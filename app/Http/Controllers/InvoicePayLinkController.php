<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Support\PayLink;
use App\Support\Razorpay;
use App\Support\StripeAutopay;
use Illuminate\Http\Request;

/**
 * Public "pay this invoice" page reached from the link in reminders (no login needed),
 * plus the staff action that sends the link to the tenant.
 */
class InvoicePayLinkController extends Controller
{
    private function invoiceFor($token)
    {
        if (!preg_match('/^[A-Za-z0-9]{24}$/', (string) $token)) {
            abort(404);
        }
        $invoice = Invoice::where('pay_token', $token)->first();
        if (!$invoice) {
            abort(404);
        }
        if (!\Auth::check()) {
            useAccountSettings($invoice->parent_id); // currency, date format and invoice prefix of this owner
        }
        return $invoice;
    }

    private function due(Invoice $invoice)
    {
        return round(max(0, $invoice->getInvoiceDueAmount()), 2);
    }

    public function show($token)
    {
        $invoice = $this->invoiceFor($token);
        $parentId = $invoice->parent_id;
        $company = settingsById($parentId);
        $payment = invoicePaymentSettings($parentId);
        $due = $this->due($invoice);
        $stripe = StripeAutopay::available($parentId);
        $razorpay = Razorpay::available($parentId);
        $tenantName = optional(optional($invoice->tenants)->user)->name;
        return view('pay_link.show', compact('invoice', 'company', 'payment', 'due', 'stripe', 'razorpay', 'token', 'tenantName'));
    }

    // ------------------------------------------------------------------ Stripe Checkout (hosted page)

    public function stripe($token)
    {
        $invoice = $this->invoiceFor($token);
        $due = $this->due($invoice);
        if ($due <= 0 || !StripeAutopay::available($invoice->parent_id)) {
            return redirect()->route('pay.show', $token);
        }
        $currency = strtolower(StripeAutopay::settings($invoice->parent_id)['CURRENCY'] ?? 'usd');
        try {
            \Stripe\Stripe::setApiKey(StripeAutopay::settings($invoice->parent_id)['STRIPE_SECRET']);
            $session = \Stripe\Checkout\Session::create([
                'mode' => 'payment',
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => StripeAutopay::toStripeAmount($due, $currency),
                        'product_data' => ['name' => __('Invoice') . ' ' . invoicePrefix() . $invoice->invoice_id],
                    ],
                ]],
                'customer_email' => optional(optional($invoice->tenants)->user)->email,
                'client_reference_id' => (string) $invoice->id,
                'metadata' => ['invoice_id' => $invoice->id, 'source' => 'pay_link'],
                'payment_intent_data' => ['metadata' => ['invoice_id' => $invoice->id, 'source' => 'pay_link']],
                'success_url' => route('pay.stripe.return', $token) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('pay.show', $token),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('pay.show', $token)->with('error', $e->getMessage());
        }
        return redirect()->away($session->url);
    }

    public function stripeReturn(Request $request, $token)
    {
        $invoice = $this->invoiceFor($token);
        try {
            \Stripe\Stripe::setApiKey(StripeAutopay::settings($invoice->parent_id)['STRIPE_SECRET']);
            $session = \Stripe\Checkout\Session::retrieve((string) $request->query('session_id'));
        } catch (\Exception $e) {
            return redirect()->route('pay.show', $token)->with('error', __('The payment could not be verified.'));
        }
        if ($session->payment_status != 'paid' || (string) $session->client_reference_id !== (string) $invoice->id) {
            return redirect()->route('pay.show', $token)->with('error', __('The payment was not completed.'));
        }
        $transactionId = $session->payment_intent ?: $session->id;
        if (!InvoicePayment::where('transaction_id', $transactionId)->exists()) {
            Invoice::addPayment([
                'invoice_id' => $invoice->id,
                'transaction_id' => $transactionId,
                'payment_type' => 'Stripe',
                'amount' => StripeAutopay::fromStripeAmount($session->amount_total, $session->currency),
                'receipt' => '',
                'notes' => __('Paid with the payment link'),
            ]);
        }
        return redirect()->route('pay.show', $token)->with('success', __('Thank you! Your payment was received.'));
    }

    // ------------------------------------------------------------------ Razorpay Checkout

    public function razorpayOrder($token)
    {
        $invoice = $this->invoiceFor($token);
        $due = $this->due($invoice);
        if ($due <= 0 || !Razorpay::available($invoice->parent_id)) {
            return response()->json(['error' => __('This invoice cannot be paid online.')], 422);
        }
        $currency = Razorpay::currency($invoice->parent_id);
        try {
            $order = Razorpay::createOrder($invoice->parent_id, $due, $currency, [
                'invoice_id' => (string) $invoice->id,
                'source' => 'pay_link',
                'receipt' => 'inv-' . $invoice->id . '-' . time(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
        $user = optional($invoice->tenants)->user;
        return response()->json([
            'key' => Razorpay::keyId($invoice->parent_id),
            'order_id' => $order['id'],
            'amount' => $order['amount'],
            'currency' => $currency,
            'name' => settingsById($invoice->parent_id)['company_name'] ?? '',
            'description' => __('Invoice') . ' ' . invoicePrefix() . $invoice->invoice_id,
            'prefill' => ['name' => optional($user)->name, 'email' => optional($user)->email, 'contact' => optional($user)->phone_number],
        ]);
    }

    public function razorpayVerify(Request $request, $token)
    {
        $invoice = $this->invoiceFor($token);
        $orderId = (string) $request->input('razorpay_order_id');
        $paymentId = (string) $request->input('razorpay_payment_id');
        if (!Razorpay::validSignature($invoice->parent_id, $orderId, $paymentId, $request->input('razorpay_signature'))) {
            return redirect()->route('pay.show', $token)->with('error', __('The payment could not be verified.'));
        }
        try {
            $payment = Razorpay::payment($invoice->parent_id, $paymentId);
            $order = Razorpay::api($invoice->parent_id, 'get', 'orders/' . rawurlencode($orderId));
        } catch (\Exception $e) {
            return redirect()->route('pay.show', $token)->with('error', $e->getMessage());
        }
        // the order must have been made for this invoice, and the payment must belong to it
        if (($order['notes']['invoice_id'] ?? '') !== (string) $invoice->id || ($payment['order_id'] ?? '') !== $orderId
            || !in_array($payment['status'] ?? '', ['captured', 'authorized'])) {
            return redirect()->route('pay.show', $token)->with('error', __('The payment was not completed.'));
        }
        if (!InvoicePayment::where('transaction_id', $paymentId)->exists()) {
            Invoice::addPayment([
                'invoice_id' => $invoice->id,
                'transaction_id' => $paymentId,
                'payment_type' => 'Razorpay',
                'amount' => Razorpay::fromSubunits($payment['amount'], $payment['currency'] ?? 'INR'),
                'receipt' => '',
                'notes' => __('Paid with the payment link'),
            ]);
        }
        return redirect()->route('pay.show', $token)->with('success', __('Thank you! Your payment was received.'));
    }

    // ------------------------------------------------------------------ staff: send / copy the link

    public function send(Request $request, $id)
    {
        if (!\Auth::user()->can('edit invoice') && !\Auth::user()->can('create invoice')) {
            return response()->json(['error' => __('Permission Denied!')], 403);
        }
        $invoice = Invoice::where('parent_id', parentId())->where('id', (int) $id)->first();
        if (!$invoice || $this->due($invoice) <= 0) {
            return response()->json(['error' => __('This invoice has nothing left to pay.')], 422);
        }
        if (!PayLink::canPayOnline($invoice->parent_id)) {
            return response()->json(['error' => __('Switch on Stripe or Razorpay in Settings > Payment to use pay links.')], 422);
        }
        $url = PayLink::url($invoice);
        $user = optional($invoice->tenants)->user;
        $text = __('Hi :name, invoice :number for :amount is ready. Pay securely here: :url', [
            'name' => optional($user)->name ?: __('there'),
            'number' => invoicePrefix() . $invoice->invoice_id,
            'amount' => priceFormat($this->due($invoice)),
            'url' => $url,
        ]);

        $channel = $request->input('channel');
        $sent = false;
        if ($channel == 'whatsapp' && $user && $user->phone_number) {
            $sent = sendTwilioWhatsappMsg(['to' => $user->phone_number, 'body' => $text, 'parent_id' => $invoice->parent_id]);
        } elseif ($channel == 'email' && $user && $user->email) {
            $response = commonEmailSend($user->email, [
                'module' => 'payment_link',
                'subject' => __('Pay invoice') . ' ' . invoicePrefix() . $invoice->invoice_id,
                'message' => '<p>' . e($text) . '</p><p><a href="' . $url . '">' . __('Pay now') . '</a></p>',
                'logo' => settingsById($invoice->parent_id)['company_logo'] ?? '',
                'parent_id' => $invoice->parent_id,
            ]);
            $sent = ($response['status'] ?? '') != 'error';
        }
        return response()->json(['url' => $url, 'text' => $text, 'sent' => $sent, 'channel' => $channel]);
    }
}
