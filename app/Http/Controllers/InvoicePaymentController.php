<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Stripe;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class InvoicePaymentController extends Controller
{
    public function paymentSettings()
    {
        $paymentSetting = invoicePaymentSettings(parentId());
        return $paymentSetting;
    }

    // Invoice the logged-in user may pay: a tenant only their own, staff any of the account.
    private function findPayableInvoice($id)
    {
        $query = Invoice::where('parent_id', parentId())->where('id', $id);
        if (\Auth::user()->type == 'tenant') {
            $query->where('tenant', optional(\Auth::user()->tenants)->id);
        } elseif (in_array(\Auth::user()->type, ['maintainer', 'landlord'])) {
            return null;
        }
        return $query->first();
    }

    private function invalidAmount($invoice, $amount)
    {
        return !is_numeric($amount) || $amount <= 0 || $amount > $invoice->getInvoiceDueAmount();
    }

    private function alreadyRecorded($transactionId)
    {
        return InvoicePayment::where('transaction_id', $transactionId)->exists();
    }

    public function stripePayment(Request $request, $ids)
    {
        $settings = $this->paymentSettings();
        $id = decrypt($ids);
        $invoice = $this->findPayableInvoice($id);
        $amount = $request->amount;
        if ($invoice) {
            if ($this->invalidAmount($invoice, $amount)) {
                return redirect()->back()->with('error', __('Invalid payment amount.'));
            }
            try {
                $transactionID = uniqid('', true);
                Stripe\Stripe::setApiKey($settings['STRIPE_SECRET']);
                $data = Stripe\Charge::create(
                    [
                        "amount" => 100 * $amount,
                        "currency" => $settings['CURRENCY'],
                        "source" => $request->stripeToken,
                        "description" => " Invoice - " . invoicePrefix() . $invoice->invoice_id,
                        "metadata" => ["order_id" => $transactionID],
                        'address' => [
                            'line1' => $request->state ?? 'NA',
                            'city' => $request->city ?? 'NA',
                            'postal_code' => $request->zipcode ?? '000000',
                            'country' => $request->country ?? 'NA',
                        ]
                    ]
                );

                if ($data['amount_refunded'] == 0 && empty($data['failure_code']) && $data['paid'] == 1 && $data['captured'] == 1) {

                    if ($data['status'] == 'succeeded') {

                        $payment['invoice_id'] = $invoice->id;
                        $payment['transaction_id'] = $transactionID;
                        $payment['payment_type'] = 'Stripe';
                        $payment['amount'] = $amount;
                        $payment['receipt'] = isset($data['receipt_url']) ? $data['receipt_url'] : '';
                        $payment['notes'] = " Invoice - " . invoicePrefix() . $invoice->invoice_id;

                        Invoice::addPayment($payment);
                        return redirect()->back()->with('success', __('Invoice payment successfully completed.'));
                    } else {
                        return redirect()->back()->with('error', __('Your payment has failed.'));
                    }
                } else {
                    return redirect()->back()->with('error', __('Transaction has been failed.'));
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', __($e->getMessage()));
            }
        } else {
            return redirect()->back()->with('error', __('Invoice is deleted.'));
        }
    }


    public function invoicePaypal(Request $request, $id)
    {
        $invoiceId = decrypt($id);
        $invoice = $this->findPayableInvoice($invoiceId);
        if (!$invoice) {
            return redirect()->back()->with('error', __('Invoice not found.'));
        }
        if ($this->invalidAmount($invoice, $request->amount)) {
            return redirect()->back()->with('error', __('Invalid payment amount.'));
        }
        $paypalSetting = $this->paymentSettings();

        if ($paypalSetting['paypal_mode'] == 'live') {
            config([
                'paypal.live.client_id' => isset($paypalSetting['paypal_client_id']) ? $paypalSetting['paypal_client_id'] : '',
                'paypal.live.client_secret' => isset($paypalSetting['paypal_secret_key']) ? $paypalSetting['paypal_secret_key'] : '',
                'paypal.mode' => isset($paypalSetting['paypal_mode']) ? $paypalSetting['paypal_mode'] : '',
                'paypal.currency' => isset($paypalSetting['CURRENCY']) ? $paypalSetting['CURRENCY'] : '',
            ]);
        } else {
            config([
                'paypal.sandbox.client_id' => isset($paypalSetting['paypal_client_id']) ? $paypalSetting['paypal_client_id'] : '',
                'paypal.sandbox.client_secret' => isset($paypalSetting['paypal_secret_key']) ? $paypalSetting['paypal_secret_key'] : '',
                'paypal.mode' => isset($paypalSetting['paypal_mode']) ? $paypalSetting['paypal_mode'] : '',
                'paypal.currency' => isset($paypalSetting['CURRENCY']) ? $paypalSetting['CURRENCY'] : '',
            ]);
        }

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));

        $paypalToken = $provider->getAccessToken();

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('invoice.paypal.status', [$invoiceId, 'success']),
                "cancel_url" => route('invoice.paypal.status', [$invoiceId, 'cancel']),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => isset($paypalSetting['CURRENCY']) ? $paypalSetting['CURRENCY'] : '',
                        "value" => $request->amount
                    ]
                ]
            ]
        ]);
        if (isset($response['id']) && $response['id'] != null) {
            // redirect to approve href
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return redirect()->away($links['href']);
                }
            }
            return redirect()
                ->back()
                ->with('error', 'Something went wrong.');
        } else {
            return redirect()
                ->back()
                ->with('error', $response['message'] ?? 'Something went wrong.');
        }
    }

    public function invoicePaypalStatus(Request $request, $invoiceId, $status)
    {
        $paypalSetting = $this->paymentSettings();

        if ($status == 'success') {
            $mode = $paypalSetting['paypal_mode'] ?? 'sandbox';
            $clientId = $paypalSetting['paypal_client_id'] ?? '';
            $secret = $paypalSetting['paypal_secret_key'] ?? '';
            $currency = $paypalSetting['CURRENCY'] ?? '';

            config([
                "paypal.{$mode}.client_id" => $clientId,
                "paypal.{$mode}.client_secret" => $secret,
                'paypal.mode' => $mode,
                'paypal.currency' => $currency,
            ]);


            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            $invoice = $this->findPayableInvoice($invoiceId);
            if (!$invoice) {
                return redirect()->route('invoice.index')->with('error', __('Invoice not found.'));
            }
            $response = $provider->capturePaymentOrder($request['token']);
            if (isset($response['status']) && $response['status'] == 'COMPLETED') {
                // record what PayPal actually captured, never an amount taken from the URL
                $capture = $response['purchase_units'][0]['payments']['captures'][0] ?? [];
                $transactionID = $capture['id'] ?? $response['id'];
                if ($this->alreadyRecorded($transactionID)) {
                    return redirect()->route('invoice.show', encrypt($invoice->id))->with('error', __('This payment has already been recorded.'));
                }
                $payment['invoice_id'] = $invoice->id;
                $payment['transaction_id'] = $transactionID;
                $payment['payment_type'] = 'Paypal';
                $payment['amount'] = $capture['amount']['value'] ?? 0;
                $payment['receipt'] = '';
                $payment['notes'] = " Invoice - " . invoicePrefix() . $invoice->invoice_id;

                Invoice::addPayment($payment);
                return redirect()->route('invoice.show', encrypt($invoice->id))->with('success', __('Invoice payment successfully completed.'));
            } else {
                return redirect()
                    ->route('invoice.index')
                    ->with('error', $response['message'] ?? __('Something went wrong.'));
            }
        } else {
            return redirect()
                ->route('invoice.index')
                ->with('error', __('Transaction has been failed.'));
        }
    }

    public function banktransferPayment(Request $request, $id)
    {
        $invoiceId = decrypt($id);
        $invoice = $this->findPayableInvoice($invoiceId);
        if (!$invoice) {
            return redirect()->back()->with('error', __('Invoice not found.'));
        }
        $validator = \Validator::make(
            $request->all(),
            [
                'receipt' => 'required|file',
                'amount' => 'required|numeric',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        if ($this->invalidAmount($invoice, $request->amount)) {
            return redirect()->back()->with('error', __('Invalid payment amount.'));
        }

        // the form field is "receipt"; the invoice page reads receipts from upload/receipt/
        if ($request->hasFile('receipt')) {
            $uploadResult = handleFileUpload($request->file('receipt'), 'upload/receipt/');

            if ($uploadResult['flag'] == 0) {
                return redirect()->back()->with('error', $uploadResult['msg']);
            }
            $payment['receipt'] = $uploadResult['filename'];
        }

        $transactionID = uniqid('', true);

        $payment['invoice_id'] = $invoice->id;
        $payment['transaction_id'] = $transactionID;
        $payment['payment_type'] = 'Bank Transfer';
        $payment['amount'] = $request->amount;
        $payment['notes'] = $request->notes;

        Invoice::addPayment($payment);
        return redirect()->back()->with('success', __('Invoice payment successfully completed.'));
    }


    public function invoiceFlutterwave(Request $request, $invoice_id, $pay_id)
    {
        $invoiceID = decrypt($invoice_id);
        $invoice = $this->findPayableInvoice($invoiceID);
        $paymentSetting = $this->paymentSettings();

        if (!$invoice) {
            return redirect()->back()->with('error', __('Invoice not found.'));
        }
        if ($this->alreadyRecorded($pay_id)) {
            return redirect()->back()->with('error', __('This payment has already been recorded.'));
        }

        if ($invoice) {
            try {
                $detail = [
                    'txref' => $pay_id,
                    'SECKEY' => $paymentSetting['flutterwave_secret_key'],
                ];
                $url = "https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/verify";
                $headersData = ['Content-Type' => 'application/json'];
                $bodyData = \Unirest\Request\Body::json($detail);
                $responseData = \Unirest\Request::post($url, $headersData, $bodyData);

                if (!empty($responseData)) {
                    $responseData = json_decode($responseData->raw_body, true);
                }

                if (isset($responseData['status']) && $responseData['status'] == 'success') {
                    $amountPaid = $responseData['data']['amount'];
                    $expectedAmount = $request->query('amount'); // Get amount from request

                    if ($amountPaid < $expectedAmount) {
                        return redirect()->back()->with('error', __('Payment amount mismatch! Expected: ') . $expectedAmount);
                    }

                    Invoice::addPayment([
                        'invoice_id' => $invoice->id,
                        'transaction_id' => $pay_id,
                        'payment_type' => 'Flutterwave',
                        'amount' => $amountPaid,
                        'notes' => $request->notes ?? 'Flutterwave Payment',
                    ]);

                    return redirect()->back()->with('success', __('Invoice payment successfully completed.'));
                } else {
                    return redirect()->back()->with('error', __('Transaction failed!'));
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
    }


    public function invoicePaystack(Request $request, $ids)
    {
        $payment_setting = $this->paymentSettings();
        $currency = $payment_setting['CURRENCY'] ?? 'USD';
        $id = Crypt::decrypt($ids);
        $invoice = $this->findPayableInvoice($id);

        if (!$invoice) {
            return response()->json([
                'flag' => 0,
                'message' => __('Invoice not found.')
            ]);
        }

        $amount = $request->amount;
        if ($this->invalidAmount($invoice, $amount)) {
            return response()->json([
                'flag' => 0,
                'message' => __('Amount must be greater than 0.')
            ]);
        }

        return response()->json([
            'flag' => 1,
            'email' => auth()->user()->email,
            'total_price' => $amount,
            'currency' => $currency,
        ]);
    }

    public function invoicePaystackStatus(Request $request, $pay_id, $invoice_id_encrypted)
    {
        try {
            $invoice = $this->findPayableInvoice(Crypt::decrypt($invoice_id_encrypted));
            if (!$invoice) {
                return redirect()->back()->with('error', __('Invoice not found.'));
            }
            if ($this->alreadyRecorded($pay_id)) {
                return redirect()->back()->with('error', __('This payment has already been recorded.'));
            }

            $secretKey = $this->paymentSettings()['paystack_secret_key'] ?? '';
            $verifyUrl = "https://api.paystack.co/transaction/verify/$pay_id";

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $verifyUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $secretKey],
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
            $result = $response ? json_decode($response, true) : [];

            if (!($result['status'] ?? false) || ($result['data']['status'] !== 'success')) {
                return redirect()->back()->with('error', __('Transaction failed or cancelled.'));
            }

            $payment = [
                'invoice_id' => $invoice->id,
                'transaction_id' => $pay_id,
                'payment_type' => 'Paystack',
                'amount' => $result['data']['amount'] / 100,
                'receipt' => '',
                'notes' => 'Paystack Payment',
            ];

            Invoice::addPayment($payment);

            return redirect()->back()->with('success', __('Invoice payment successfully completed.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Something went wrong while verifying the payment.'));
        }
    }

    public function invoicePayWithRazorpay(Request $request, $ids)
    {
        $payment_setting = $this->paymentSettings();
        $currency = $payment_setting['CURRENCY'] ?? 'INR';

        $id = Crypt::decrypt($ids);
        $invoice = $this->findPayableInvoice($id);

        if (!$invoice) {
            return response()->json([
                'flag' => 0,
                'message' => __('Invoice not found.')
            ]);
        }

        $amount = $request->amount;

        if ($this->invalidAmount($invoice, $amount)) {
            return response()->json([
                'flag' => 0,
                'message' => __('Amount must be greater than 0.')
            ]);
        }

        return response()->json([
            'flag' => 1,
            'email' => auth()->user()->email,
            'total_price' => $amount,
            'currency' => $currency,
        ]);
    }

    public function invoiceRazorpayStatus(Request $request, $pay_id, $invoice_id_encrypted)
    {
        try {
            $invoice = $this->findPayableInvoice(Crypt::decrypt($invoice_id_encrypted));

            if (!$invoice) {
                return redirect()->back()->with('error', __('Invoice not found.'));
            }
            if ($this->alreadyRecorded($pay_id)) {
                return redirect()->back()->with('error', __('This payment has already been recorded.'));
            }

            $paymentSetting = $this->paymentSettings();

            $ch = curl_init("https://api.razorpay.com/v1/payments/$pay_id");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $paymentSetting['razorpay_api_key'] . ':' . $paymentSetting['razorpay_secret_key']);

            $response = curl_exec($ch);
            curl_close($ch);

            $result = $response ? json_decode($response, true) : [];

            $validStatuses = ['captured', 'authorized'];

            if (empty($result['status']) || !in_array($result['status'], $validStatuses)) {
                $errorDesc = $result['error_description'] ?? 'Unknown error';
                \Log::warning('Razorpay invoice payment not valid', [
                    'pay_id' => $pay_id,
                    'status' => $result['status'] ?? 'null',
                    'error' => $errorDesc,
                ]);
                return redirect()->back()->with('error', __('Transaction failed or cancelled. ') . $errorDesc);
            }

            $payment = [
                'invoice_id' => $invoice->id,
                'transaction_id' => $result['id'],
                'payment_type' => 'Razorpay',
                'amount' => $result['amount'] / 100,
                'receipt' => '',
                'notes' => 'Razorpay Payment',
            ];

            if (!empty($result['method'])) {
                $payment['payment_method_details'] = json_encode([
                    'method' => $result['method'],
                    'last4' => $result['card']['last4'] ?? null,
                ]);
            }

            Invoice::addPayment($payment);

            return redirect()->back()->with('success', __('Invoice payment successfully completed.'));

        } catch (\Exception $e) {
            \Log::error($e);
            return redirect()->back()->with('error', __('Something went wrong while verifying the payment.'));
        }
    }
}
