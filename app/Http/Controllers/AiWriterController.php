<?php

namespace App\Http\Controllers;

use App\Support\AiWriter;
use Illuminate\Http\Request;

class AiWriterController extends Controller
{
    public function generate(Request $request)
    {
        if (!AiWriter::available()) {
            return response()->json(['error' => __('AI writing is not available on your plan.')], 403);
        }
        $type = (string) $request->input('type');
        if (!array_key_exists($type, AiWriter::types())) {
            return response()->json(['error' => __('Unknown content type.')], 422);
        }
        if (trim((string) $request->brief) === '' && empty(array_filter((array) $request->input('context')))) {
            return response()->json(['error' => __('Tell the AI what to write about.')], 422);
        }
        $placeholders = array_values(array_filter((array) $request->input('placeholders'), function ($p) {
            return is_string($p) && preg_match('/^\{[a-z0-9_]{1,40}\}$/i', $p);
        }));
        $result = AiWriter::write($type, (array) $request->input('context'), $request->brief, $request->tone, $request->length, $request->language, array_slice($placeholders, 0, 40));
        return response()->json($result, isset($result['error']) ? 422 : 200);
    }
}
