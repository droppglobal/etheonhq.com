<?php

namespace App\Support;

use App\Models\Invoice;
use App\Models\Tenant;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\SetupIntent;
use Stripe\Stripe;

/**
 * Saved-card autopay through the owner's own Stripe account.
 */
class StripeAutopay
{
    // Stripe amounts are in the smallest unit, except for these currencies
    const ZERO_DECIMAL = ['bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf'];

    public static function settings($parentId)
    {
        return invoicePaymentSettings($parentId);
    }

    // Autopay is offered only when the owner has Stripe switched on with keys
    public static function available($parentId)
    {
        $settings = self::settings($parentId);
        return ($settings['STRIPE_PAYMENT'] ?? '') == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']);
    }

    private static function boot($parentId)
    {
        Stripe::setApiKey(self::settings($parentId)['STRIPE_SECRET']);
    }

    public static function toStripeAmount($amount, $currency)
    {
        return in_array(strtolower($currency), self::ZERO_DECIMAL) ? (int) round($amount) : (int) round($amount * 100);
    }

    public static function fromStripeAmount($amount, $currency)
    {
        return in_array(strtolower($currency), self::ZERO_DECIMAL) ? (float) $amount : $amount / 100;
    }

    // Tenants store country names ("United Kingdom"); Stripe wants ISO codes ("GB")
    public static function countryCode($country)
    {
        $country = trim((string) $country);
        if (preg_match('/^[A-Za-z]{2}$/', $country)) {
            return strtoupper($country);
        }
        if ($country === '' || !class_exists('ResourceBundle')) {
            return null;
        }
        try {
            $bundle = \ResourceBundle::create('en', 'ICUDATA-region');
            foreach ($bundle->get('Countries') as $code => $name) {
                if (strlen($code) == 2 && strcasecmp($name, $country) === 0) {
                    return $code;
                }
            }
        } catch (\Throwable $e) {
            // intl data not available
        }
        return null;
    }

    // Name and address are required by Stripe for some accounts (e.g. Indian accounts charging in other currencies)
    private static function billingDetails(Tenant $tenant)
    {
        $address = array_filter([
            'line1' => $tenant->address ?: null,
            'city' => $tenant->city ?: null,
            'state' => $tenant->state ?: null,
            'postal_code' => $tenant->zip_code ?: null,
            'country' => self::countryCode($tenant->country),
        ]);
        return ['name' => optional($tenant->user)->name ?: 'Tenant ' . $tenant->id, 'address' => $address];
    }

    public static function customerFor(Tenant $tenant)
    {
        self::boot($tenant->parent_id);
        if (!empty($tenant->stripe_customer_id)) {
            return $tenant->stripe_customer_id;
        }
        $user = $tenant->user;
        $billing = self::billingDetails($tenant);
        $customer = Customer::create(array_filter([
            'email' => optional($user)->email,
            'name' => $billing['name'],
            'address' => !empty($billing['address']['line1']) ? $billing['address'] : null,
            'metadata' => ['tenant_id' => $tenant->id, 'parent_id' => $tenant->parent_id],
        ]));
        $tenant->stripe_customer_id = $customer->id;
        $tenant->save();
        return $customer->id;
    }

    public static function createSetupIntent(Tenant $tenant)
    {
        $customerId = self::customerFor($tenant);
        return SetupIntent::create([
            'customer' => $customerId,
            'payment_method_types' => ['card'],
            'usage' => 'off_session',
            'metadata' => ['tenant_id' => $tenant->id],
        ]);
    }

    // Saves the card from a confirmed SetupIntent (checked against this tenant's customer)
    public static function saveFromSetupIntent(Tenant $tenant, $setupIntentId)
    {
        self::boot($tenant->parent_id);
        $intent = SetupIntent::retrieve($setupIntentId);
        if ($intent->status != 'succeeded' || $intent->customer != $tenant->stripe_customer_id || empty($intent->payment_method)) {
            throw new \Exception(__('The card could not be confirmed. Please try again.'));
        }
        self::savePaymentMethod($tenant, $intent->payment_method);
    }

    public static function savePaymentMethod(Tenant $tenant, $paymentMethodId)
    {
        self::boot($tenant->parent_id);
        $method = PaymentMethod::retrieve($paymentMethodId);
        if (empty($method->customer)) {
            $method->attach(['customer' => self::customerFor($tenant)]);
        } elseif ($method->customer != $tenant->stripe_customer_id) {
            throw new \Exception(__('This card belongs to a different customer.'));
        }
        $tenant->stripe_payment_method_id = $method->id;
        $tenant->card_brand = optional($method->card)->brand;
        $tenant->card_last4 = optional($method->card)->last4;
        $tenant->autopay_enabled = 1;
        $tenant->autopay_gateway = 'stripe';
        $tenant->save();
    }

    public static function disable(Tenant $tenant)
    {
        if (!empty($tenant->stripe_payment_method_id)) {
            try {
                self::boot($tenant->parent_id);
                PaymentMethod::retrieve($tenant->stripe_payment_method_id)->detach();
            } catch (\Exception $e) {
                // already removed on Stripe's side
            }
        }
        $tenant->autopay_enabled = 0;
        $tenant->autopay_gateway = null;
        $tenant->stripe_payment_method_id = null;
        $tenant->card_brand = null;
        $tenant->card_last4 = null;
        $tenant->save();
    }

    /**
     * Charges what is still owed on the invoice without the tenant present.
     * Returns ['status' => 'paid'|'failed'|'skipped', 'message' => ...].
     */
    public static function chargeInvoice(Invoice $invoice, Tenant $tenant)
    {
        $due = round($invoice->getInvoiceDueAmount(), 2);
        if ($due <= 0) {
            return ['status' => 'skipped', 'message' => 'nothing due'];
        }
        $settings = self::settings($invoice->parent_id);
        $currency = strtolower($settings['CURRENCY'] ?? 'usd');

        $billing = self::billingDetails($tenant);
        try {
            self::boot($invoice->parent_id);
            $intent = PaymentIntent::create(array_filter([
                'shipping' => !empty($billing['address']['line1']) ? $billing : null,
                'amount' => self::toStripeAmount($due, $currency),
                'currency' => $currency,
                'customer' => $tenant->stripe_customer_id,
                'payment_method' => $tenant->stripe_payment_method_id,
                'off_session' => true,
                'confirm' => true,
                'description' => 'Invoice ' . invoicePrefix() . $invoice->invoice_id,
                'metadata' => ['invoice_id' => $invoice->id, 'tenant_id' => $tenant->id],
            ]), [
                // the same charge is never sent twice (cron run twice); a changed amount or card is a new request
                'idempotency_key' => 'autopay-' . $invoice->id . '-' . date('Ymd') . '-' . self::toStripeAmount($due, $currency) . '-' . $tenant->stripe_payment_method_id,
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            return ['status' => 'failed', 'message' => $e->getError()->message ?? $e->getMessage()];
        } catch (\Exception $e) {
            return ['status' => 'failed', 'message' => $e->getMessage()];
        }

        if ($intent->status != 'succeeded') {
            return ['status' => 'failed', 'message' => __('The bank asked for confirmation. Please pay this invoice online.')];
        }
        if (\App\Models\InvoicePayment::where('transaction_id', $intent->id)->exists()) {
            return ['status' => 'skipped', 'message' => 'already recorded'];
        }

        Invoice::addPayment([
            'invoice_id' => $invoice->id,
            'transaction_id' => $intent->id,
            'payment_type' => 'Stripe Autopay',
            'amount' => $due,
            'receipt' => '',
            'notes' => __('Automatic card payment') . ' ' . ucfirst((string) $tenant->card_brand) . ' **** ' . $tenant->card_last4,
        ]);
        return ['status' => 'paid', 'message' => $intent->id];
    }
}
