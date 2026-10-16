<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Shared JSON shapes for the mobile API. Money and dates are sent both raw (for logic) and formatted
 * with the owner's own currency and date settings (for display), so the app never formats them itself.
 */
class BaseController extends Controller
{
    protected function ok($data = null, $message = null, $status = 200)
    {
        return response()->json(array_filter(['data' => $data, 'message' => $message], fn($v) => $v !== null), $status);
    }

    protected function fail($message, $status = 422, array $errors = [])
    {
        return response()->json(array_filter(['message' => $message, 'errors' => $errors ?: null]), $status);
    }

    protected function money($amount)
    {
        $amount = round((float) $amount, 2);
        return ['amount' => $amount, 'formatted' => $amount < 0 ? '-' . priceFormat(abs($amount)) : priceFormat($amount)];
    }

    protected function date($value)
    {
        if (empty($value)) {
            return null;
        }
        $time = strtotime($value);
        return ['iso' => date('Y-m-d', $time), 'formatted' => dateFormat($value)];
    }

    protected function dateTime($value)
    {
        if (empty($value)) {
            return null;
        }
        $time = strtotime($value);
        return ['iso' => date('c', $time), 'formatted' => dateFormat($value) . ' ' . date('H:i', $time)];
    }

    // street address plus the city, unless the address already contains it
    protected function address($property)
    {
        if (!$property) {
            return '';
        }
        $address = trim((string) $property->address);
        foreach ([$property->city] as $part) {
            $part = trim((string) $part);
            if ($part !== '' && stripos($address, $part) === false) {
                $address = trim($address . ', ' . $part, ', ');
            }
        }
        return $address;
    }

    protected function file($name, $folder)
    {
        return !empty($name) ? fetch_file($name, $folder) : null;
    }

    protected function paginated(LengthAwarePaginator $page, callable $map)
    {
        return response()->json([
            'data' => collect($page->items())->map($map)->values(),
            'meta' => ['page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total(), 'last_page' => $page->lastPage()],
        ]);
    }

    /**
     * Runs a website controller action and turns its result into an API response, so the app and the
     * website share one set of rules. Redirects carry their outcome in the "error"/"success" flash;
     * JSON responses are passed on; file downloads (PDFs) are returned as they are.
     */
    protected function bridge($response, $data = null)
    {
        $clean = fn($text) => trim(preg_replace('/\s+/', ' ', strip_tags(str_replace(['</br>', '<br>', '<br/>'], ' ', (string) $text))));
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $payload = $response->getData(true);
            $status = $response->getStatusCode();
            $error = $payload['error'] ?? ((($payload['status'] ?? null) === 'false' || ($payload['flag'] ?? null) === 0) ? ($payload['message'] ?? $payload['msg'] ?? null) : null);
            if ($error || $status >= 400) {
                return $this->fail($clean($error ?: ($payload['message'] ?? __('Something went wrong.'))), $status >= 400 ? $status : 422);
            }
            return $this->ok(is_callable($data) ? $data() : ($data ?? $payload), isset($payload['message']) ? $clean($payload['message']) : null);
        }
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            $error = session()->pull('error');
            $success = session()->pull('success');
            if ($error) {
                return $this->fail($clean($error), 422);
            }
            return $this->ok(is_callable($data) ? $data() : $data, $success ? $clean($success) : null);
        }
        return $response; // e.g. a PDF download
    }
}
