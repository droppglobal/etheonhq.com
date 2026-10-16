<?php

namespace App\Support;

use App\Models\Invoice;
use Illuminate\Support\Str;

/**
 * Short public link that lets a tenant pay an invoice in one tap from WhatsApp, SMS or email.
 * The random token is the only secret; it is created on first use and stays valid until the invoice is paid.
 */
class PayLink
{
    public static function token(Invoice $invoice)
    {
        if (empty($invoice->pay_token)) {
            do {
                $token = Str::random(24);
            } while (Invoice::where('pay_token', $token)->exists());
            $invoice->pay_token = $token;
            $invoice->saveQuietly();
        }
        return $invoice->pay_token;
    }

    public static function url(Invoice $invoice)
    {
        return route('pay.show', self::token($invoice));
    }

    // whether the owner has a gateway the public page can use
    public static function canPayOnline($parentId)
    {
        return StripeAutopay::available($parentId) || Razorpay::available($parentId);
    }

    // Adds the link to a reminder text unless the template already placed it with {payment_link}
    public static function appendTo($text, Invoice $invoice)
    {
        if (!self::canPayOnline($invoice->parent_id)) {
            return str_replace('{payment_link}', '', (string) $text);
        }
        $url = self::url($invoice);
        if (str_contains((string) $text, '{payment_link}')) {
            return str_replace('{payment_link}', $url, $text);
        }
        return rtrim((string) $text) . "\n" . __('Pay now') . ': ' . $url;
    }
}
