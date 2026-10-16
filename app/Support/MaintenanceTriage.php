<?php

namespace App\Support;

use App\Models\MaintenanceRequest;
use App\Models\User;

/**
 * Sets a priority (emergency / high / medium / low) and a short summary on new maintenance requests,
 * and emails the owner straight away for emergencies.
 */
class MaintenanceTriage
{
    public static function priorities()
    {
        return [
            'emergency' => __('Emergency'),
            'high' => __('High'),
            'medium' => __('Medium'),
            'low' => __('Low'),
        ];
    }

    public static function color($priority)
    {
        return ['emergency' => 'danger', 'high' => 'warning', 'medium' => 'info', 'low' => 'secondary'][$priority] ?? 'secondary';
    }

    // Rules used without AI, and as a safety net when AI says otherwise for obvious emergencies
    public static function keywordPriority($text)
    {
        $t = mb_strtolower((string) $text);
        if (preg_match('/\b(fire|smoke|gas leak|smell of gas|sparks?|electric shock|flood(ing)?|burst pipe|sewage|carbon monoxide|no heat)\b/', $t)) {
            return 'emergency';
        }
        if (preg_match('/\b(leak(ing)?|no water|no power|power cut|no electricity|toilet (blocked|not working)|lock(ed)? out|broken (window|lock|door)|(window|glass|door|lock)\b.{0,20}\b(broken|cracked|smashed|shattered|won.?t (close|lock)))\b/', $t)) {
            return 'high';
        }
        if (preg_match('/\b(not working|broken|crack(ed)?|blocked|clogged|ac|air ?con|fridge|heater|pest|mou?ld|damp)\b/', $t)) {
            return 'medium';
        }
        return 'low';
    }

    public static function guessIssueType($text, $issueTypes)
    {
        $t = mb_strtolower((string) $text);
        $map = [
            'plumb' => ['leak', 'tap', 'pipe', 'toilet', 'drain', 'water', 'sink', 'shower'],
            'electric' => ['power', 'light', 'switch', 'socket', 'electric', 'spark', 'fan', 'wiring'],
            'floor' => ['floor', 'door', 'cabinet', 'wood', 'tile'],
            'struct' => ['wall', 'ceiling', 'roof', 'crack', 'window'],
            'exterior' => ['garden', 'parking', 'gate', 'outside', 'balcony'],
        ];
        foreach ($issueTypes as $id => $title) {
            $title = mb_strtolower($title);
            foreach ($map as $typeKey => $words) {
                if (str_contains($title, $typeKey)) {
                    foreach ($words as $word) {
                        if (str_contains($t, $word)) {
                            return (int) $id;
                        }
                    }
                }
            }
        }
        foreach ($issueTypes as $id => $title) {
            if (str_contains(mb_strtolower($title), 'general')) {
                return (int) $id;
            }
        }
        return null;
    }

    public static function apply(MaintenanceRequest $request)
    {
        $text = trim($request->notes . ' ' . optional($request->types)->title);
        $priority = self::keywordPriority($text);
        $summary = null;

        if (AiService::enabledFor($request->parent_id) && $text !== '') {
            $data = AiService::json([
                ['role' => 'system', 'content' => 'You triage residential maintenance requests. Respond as JSON: {"priority": "emergency"|"high"|"medium"|"low", "summary": string (max 15 words, in English)}. '
                    . 'emergency = risk to safety or property right now (fire, gas, flooding, sparks); high = essential service down or security issue; medium = something broken that needs fixing soon; low = cosmetic or minor.'],
                ['role' => 'user', 'content' => mb_substr($text, 0, 1500)],
            ], 120, [
                'type' => 'object',
                'properties' => ['priority' => ['type' => 'string', 'enum' => array_keys(self::priorities())], 'summary' => ['type' => 'string']],
                'required' => ['priority', 'summary'],
                'additionalProperties' => false,
            ]);
            if (!empty($data['priority']) && array_key_exists($data['priority'], self::priorities())) {
                // never downgrade an obvious emergency
                $priority = $priority == 'emergency' ? 'emergency' : $data['priority'];
                $summary = !empty($data['summary']) ? mb_substr((string) $data['summary'], 0, 200) : null;
            }
        }

        $request->ai_priority = $priority;
        $request->ai_summary = $summary;
        $request->save();

        if ($priority == 'emergency') {
            $owner = User::find($request->parent_id);
            if ($owner) {
                commonEmailSend($owner->email, [
                    'module' => 'maintenance_emergency',
                    'subject' => __('EMERGENCY maintenance request') . ' - ' . optional($request->properties)->name,
                    'message' => '<p><strong>' . __('An emergency maintenance request was just raised.') . '</strong></p><p>'
                        . e(optional($request->properties)->name) . ' / ' . e(optional($request->units)->name) . '</p><p>' . nl2br(e($request->notes)) . '</p>',
                    'logo' => settingsById($request->parent_id)['company_logo'] ?? '',
                    'parent_id' => $request->parent_id,
                ]);
            }
        }
        return $priority;
    }
}
