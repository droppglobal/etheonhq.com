<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

/**
 * Small client for the owner's own Razorpay account (REST API, basic auth with key id + secret).
 */
class Razorpay
{
    // tests point this at a local mock server
    public static $baseUrl = 'https://api.razorpay.com/v1';

    const ZERO_DECIMAL = ['jpy', 'krw', 'vnd', 'clp', 'pyg', 'ugx', 'xaf', 'xof'];

    public static function settings($parentId)
    {
        return invoicePaymentSettings($parentId);
    }

    public static function available($parentId)
    {
        $settings = self::settings($parentId);
        return ($settings['razorpay_payment'] ?? '') == 'on' && !empty($settings['razorpay_api_key']) && !empty($settings['razorpay_secret_key']);
    }

    public static function keyId($parentId)
    {
        return self::settings($parentId)['razorpay_api_key'] ?? '';
    }

    public static function currency($parentId)
    {
        return strtoupper(self::settings($parentId)['CURRENCY'] ?? 'INR');
    }

    public static function toSubunits($amount, $currency)
    {
        return in_array(strtolower($currency), self::ZERO_DECIMAL) ? (int) round($amount) : (int) round($amount * 100);
    }

    public static function fromSubunits($amount, $currency)
    {
        return in_array(strtolower($currency), self::ZERO_DECIMAL) ? (float) $amount : $amount / 100;
    }

    /**
     * Calls the API and returns the decoded body. Throws with Razorpay's own error description on failure.
     */
    public static function api($parentId, $method, $path, array $data = [])
    {
        $settings = self::settings($parentId);
        $request = Http::timeout(30)->acceptJson()->withBasicAuth($settings['razorpay_api_key'], $settings['razorpay_secret_key']);
        $url = rtrim(static::$baseUrl, '/') . '/' . ltrim($path, '/');
        $response = $method == 'get' ? $request->get($url, $data) : $request->{$method}($url, $data);
        if (!$response->successful()) {
            throw new \Exception($response->json('error.description') ?: 'Razorpay error ' . $response->status());
        }
        return $response->json() ?: [];
    }

    public static function createOrder($parentId, $amount, $currency, array $notes, array $extra = [])
    {
        return self::api($parentId, 'post', 'orders', array_merge([
            'amount' => self::toSubunits($amount, $currency),
            'currency' => $currency,
            'receipt' => mb_substr($notes['receipt'] ?? ('rcpt-' . uniqid()), 0, 40),
            'notes' => $notes,
            'payment_capture' => 1,
        ], $extra));
    }

    public static function payment($parentId, $paymentId)
    {
        return self::api($parentId, 'get', 'payments/' . rawurlencode($paymentId));
    }

    // Checkout returns order id, payment id and an HMAC signature made with our secret
    public static function validSignature($parentId, $orderId, $paymentId, $signature)
    {
        $secret = self::settings($parentId)['razorpay_secret_key'] ?? '';
        if ($secret === '' || !$orderId || !$paymentId || !$signature) {
            return false;
        }
        return hash_equals(hash_hmac('sha256', $orderId . '|' . $paymentId, $secret), (string) $signature);
    }
}
