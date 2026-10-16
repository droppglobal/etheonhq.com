<?php

namespace App\Support;

use App\Models\Invoice;
use App\Models\Tenant;

/**
 * Automatic rent payment through the owner's Razorpay account, using Razorpay recurring payments:
 * the tenant authorises a card or UPI mandate once (a small payment that is refunded), Razorpay returns
 * a token, and later invoices are charged with that token. Card and UPI recurring debits can be processed
 * asynchronously (Razorpay sends the pre-debit notice), so a charge may stay "pending" until the next cron run.
 */
class RazorpayAutopay
{
    const AUTH_AMOUNT = 1; // in the main currency unit; refunded straight after the mandate is set up

    public static function available($parentId)
    {
        return Razorpay::available($parentId) && Razorpay::currency($parentId) == 'INR';
    }

    // Highest amount the mandate allows per debit: a margin above the rent, at least 5,000
    public static function maxAmount(Tenant $tenant)
    {
        $rent = (float) optional(\App\Models\PropertyUnit::find($tenant->unit))->rent;
        return max(5000, (int) ceil($rent * 1.5 / 100) * 100);
    }

    public static function customerFor(Tenant $tenant)
    {
        if (!empty($tenant->razorpay_customer_id)) {
            return $tenant->razorpay_customer_id;
        }
        $user = $tenant->user;
        $customer = Razorpay::api($tenant->parent_id, 'post', 'customers', array_filter([
            'name' => mb_substr(optional($user)->name ?: 'Tenant ' . $tenant->id, 0, 50),
            'email' => optional($user)->email,
            'contact' => preg_replace('/[^\d+]/', '', (string) optional($user)->phone_number) ?: null,
            'fail_existing' => '0', // return the existing customer for this email/contact instead of failing
            'notes' => ['tenant_id' => (string) $tenant->id],
        ]));
        $tenant->razorpay_customer_id = $customer['id'];
        $tenant->save();
        return $customer['id'];
    }

    /**
     * Order for the one-time mandate authorisation. $method is 'card' or 'upi'.
     * Returns what Razorpay Checkout needs.
     */
    public static function authorisationOrder(Tenant $tenant, $method)
    {
        $method = in_array($method, ['card', 'upi']) ? $method : 'card';
        $customerId = self::customerFor($tenant);
        $order = Razorpay::createOrder($tenant->parent_id, self::AUTH_AMOUNT, 'INR', [
            'purpose' => 'autopay_mandate',
            'tenant_id' => (string) $tenant->id,
            'receipt' => 'mandate-' . $tenant->id . '-' . time(),
        ], [
            'customer_id' => $customerId,
            'method' => $method,
            'token' => [
                'max_amount' => Razorpay::toSubunits(self::maxAmount($tenant), 'INR'),
                'expire_at' => now()->addYears(10)->timestamp,
                'frequency' => 'as_presented',
            ],
        ]);
        return [
            'key' => Razorpay::keyId($tenant->parent_id),
            'order_id' => $order['id'],
            'customer_id' => $customerId,
            'amount' => $order['amount'],
            'method' => $method,
        ];
    }

    // Verifies the Checkout response, stores the token and refunds the authorisation payment
    public static function saveMandate(Tenant $tenant, $orderId, $paymentId, $signature)
    {
        if (!Razorpay::validSignature($tenant->parent_id, $orderId, $paymentId, $signature)) {
            throw new \Exception(__('The payment could not be verified. Please try again.'));
        }
        $payment = Razorpay::payment($tenant->parent_id, $paymentId);
        if (($payment['order_id'] ?? '') !== $orderId || empty($payment['token_id']) || ($payment['customer_id'] ?? '') !== $tenant->razorpay_customer_id) {
            throw new \Exception(__('Razorpay did not return a reusable mandate. Please try a different card or UPI app.'));
        }

        $tenant->autopay_gateway = 'razorpay';
        $tenant->razorpay_token_id = $payment['token_id'];
        $tenant->razorpay_method = $payment['method'] ?? null;
        if (($payment['method'] ?? '') == 'card' && !empty($payment['card'])) {
            $tenant->card_brand = $payment['card']['network'] ?? 'card';
            $tenant->card_last4 = $payment['card']['last4'] ?? '';
        } else {
            $tenant->card_brand = 'UPI';
            $tenant->card_last4 = $payment['vpa'] ?? '';
        }
        $tenant->autopay_enabled = 1;
        $tenant->save();

        try {
            if (($payment['status'] ?? '') == 'captured') {
                Razorpay::api($tenant->parent_id, 'post', 'payments/' . rawurlencode($paymentId) . '/refund', ['notes' => ['reason' => 'autopay mandate authorisation']]);
            }
        } catch (\Exception $e) {
            \Log::info('Razorpay mandate refund failed: ' . $e->getMessage());
        }
    }

    public static function disable(Tenant $tenant)
    {
        if (!empty($tenant->razorpay_token_id) && !empty($tenant->razorpay_customer_id)) {
            try {
                Razorpay::api($tenant->parent_id, 'delete', 'customers/' . rawurlencode($tenant->razorpay_customer_id) . '/tokens/' . rawurlencode($tenant->razorpay_token_id));
            } catch (\Exception $e) {
                // already cancelled on Razorpay's side
            }
        }
        $tenant->autopay_enabled = 0;
        $tenant->autopay_gateway = null;
        $tenant->razorpay_token_id = null;
        $tenant->razorpay_method = null;
        $tenant->card_brand = null;
        $tenant->card_last4 = null;
        $tenant->save();
    }

    /**
     * Charges (or follows up on) the invoice. Returns ['status' => 'paid'|'pending'|'failed'|'skipped', 'message' => ...].
     */
    public static function chargeInvoice(Invoice $invoice, Tenant $tenant)
    {
        if (!empty($invoice->autopay_pending_id)) {
            return self::checkPending($invoice, $tenant);
        }
        $due = round($invoice->getInvoiceDueAmount(), 2);
        if ($due <= 0) {
            return ['status' => 'skipped', 'message' => 'nothing due'];
        }
        if ($due > self::maxAmount($tenant)) {
            return ['status' => 'failed', 'message' => __('The amount is above your autopay limit. Please pay this invoice online.')];
        }

        try {
            $order = Razorpay::createOrder($invoice->parent_id, $due, 'INR', [
                'invoice_id' => (string) $invoice->id,
                'tenant_id' => (string) $tenant->id,
                'receipt' => 'inv-' . $invoice->id . '-' . date('Ymd'),
            ]);
            $user = $tenant->user;
            $result = Razorpay::api($invoice->parent_id, 'post', 'payments/create/recurring', array_filter([
                'email' => optional($user)->email,
                'contact' => preg_replace('/[^\d+]/', '', (string) optional($user)->phone_number) ?: null,
                'amount' => $order['amount'],
                'currency' => 'INR',
                'order_id' => $order['id'],
                'customer_id' => $tenant->razorpay_customer_id,
                'token' => $tenant->razorpay_token_id,
                'recurring' => '1',
                'description' => 'Invoice ' . invoicePrefix() . $invoice->invoice_id,
                'notes' => ['invoice_id' => (string) $invoice->id],
            ]));
        } catch (\Exception $e) {
            return ['status' => 'failed', 'message' => $e->getMessage()];
        }

        $paymentId = $result['razorpay_payment_id'] ?? null;
        if (!$paymentId) {
            return ['status' => 'failed', 'message' => __('Razorpay did not accept the charge.')];
        }
        $invoice->autopay_pending_id = $paymentId;
        $invoice->save();
        return self::checkPending($invoice, $tenant);
    }

    private static function checkPending(Invoice $invoice, Tenant $tenant)
    {
        $paymentId = $invoice->autopay_pending_id;
        try {
            $payment = Razorpay::payment($invoice->parent_id, $paymentId);
        } catch (\Exception $e) {
            return ['status' => 'pending', 'message' => $e->getMessage()];
        }
        $status = $payment['status'] ?? '';
        // only ever book a payment that Razorpay says was made for this invoice
        if (isset($payment['notes']['invoice_id']) && (string) $payment['notes']['invoice_id'] !== (string) $invoice->id) {
            $invoice->autopay_pending_id = null;
            $invoice->save();
            return ['status' => 'failed', 'message' => __('The automatic payment did not match this invoice.')];
        }
        if ($status == 'captured') {
            $invoice->autopay_pending_id = null;
            $invoice->save();
            if (\App\Models\InvoicePayment::where('transaction_id', $paymentId)->exists()) {
                return ['status' => 'skipped', 'message' => 'already recorded'];
            }
            Invoice::addPayment([
                'invoice_id' => $invoice->id,
                'transaction_id' => $paymentId,
                'payment_type' => 'Razorpay Autopay',
                'amount' => Razorpay::fromSubunits($payment['amount'], 'INR'),
                'receipt' => '',
                'notes' => __('Automatic payment') . ' ' . $tenant->card_brand . ' ' . $tenant->card_last4,
            ]);
            return ['status' => 'paid', 'message' => $paymentId];
        }
        if ($status == 'failed') {
            $invoice->autopay_pending_id = null;
            $invoice->save();
            return ['status' => 'failed', 'message' => $payment['error_description'] ?? __('The automatic payment was declined.')];
        }
        return ['status' => 'pending', 'message' => $status ?: 'created'];
    }
}
