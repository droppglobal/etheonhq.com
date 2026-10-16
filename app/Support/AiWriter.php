<?php

namespace App\Support;

/**
 * "Write with AI" for any form: each type says what to write, which form fields it fills and
 * which form fields it reads as context. Replaces the old AI Content Generator modal, where the
 * result had to be copied into the form by hand.
 */
class AiWriter
{
    /*
     * outputs: form field name => [label, format plain|html, max characters, instruction]
     * context: form field name => label (read from the form and sent to the AI)
     */
    public static function types()
    {
        return array_merge(\App\Support\WebsiteSections::aiTypes(), [
            'custom_page' => [
                'purpose' => 'a page for a property management company website',
                'outputs' => ['title' => ['Page title', 'plain', 80, 'short page title'], 'content' => ['Page content', 'html', 6000, 'well structured page body']],
                'context' => ['title' => 'Current title'],
            ],
            'unit' => [
                'purpose' => 'internal notes for a rental unit (layout, condition, what is included, rules)',
                'outputs' => ['notes' => ['Notes', 'plain', 1000, 'short practical notes']],
                'context' => ['unitname' => 'Unit name', 'name' => 'Unit name', 'bedroom' => 'Bedrooms', 'kitchen' => 'Kitchens', 'baths' => 'Bathrooms', 'rent' => 'Rent', 'rent_type' => 'Rent period', 'property_id' => 'Property'],
            ],
            'contact' => [
                'purpose' => 'a note in the contact diary of a property manager',
                'outputs' => ['subject' => ['Subject', 'plain', 120, 'short subject line'], 'message' => ['Message', 'plain', 2000, 'clear message']],
                'context' => ['name' => 'Contact name'],
            ],
            'noticeboard' => [
                'purpose' => 'a notice for the residents notice board of a building',
                'outputs' => ['title' => ['Title', 'plain', 120, 'short notice title'], 'description' => ['Notice', 'plain', 2000, 'clear notice text for residents']],
                'context' => ['title' => 'Current title'],
            ],
            'property_advantage' => [
                'purpose' => 'a selling point (advantage) of a rental property, shown on listings',
                'outputs' => ['name' => ['Name', 'plain', 60, 'two to four words'], 'description' => ['Description', 'plain', 300, 'one or two sentences']],
                'context' => ['name' => 'Current name'],
            ],
            'amenity' => [
                'purpose' => 'an amenity of a rental property (for example swimming pool, gym), shown on listings',
                'outputs' => ['name' => ['Name', 'plain', 60, 'one to three words'], 'description' => ['Description', 'plain', 300, 'one or two sentences']],
                'context' => ['name' => 'Current name'],
            ],
            'subscription' => [
                'purpose' => 'the name of a subscription plan for property management software',
                'outputs' => ['title' => ['Plan name', 'plain', 40, 'one or two catchy words']],
                'context' => ['package_amount' => 'Price', 'interval' => 'Billing interval', 'property_limit' => 'Property limit', 'tenant_limit' => 'Tenant limit', 'user_limit' => 'User limit'],
            ],
            'coupon' => [
                'purpose' => 'a discount coupon for subscription plans',
                'outputs' => ['name' => ['Coupon name', 'plain', 60, 'short campaign name'], 'code' => ['Code', 'code', 20, 'UPPERCASE letters and digits only, no spaces, 6-12 characters']],
                'context' => ['rate' => 'Discount', 'type' => 'Discount type', 'valid_for' => 'Valid until'],
            ],
            'agreement' => [
                'purpose' => 'a residential rental agreement',
                'outputs' => ['terms_condition' => ['Terms & conditions', 'html', 8000, 'numbered list of clear tenancy terms'], 'description' => ['Description', 'html', 3000, 'one or two paragraphs describing the agreement']],
                'context' => ['property_id' => 'Property', 'unit_id' => 'Unit', 'agreement_start_date' => 'Start date', 'agreement_end_date' => 'End date'],
            ],
            'maintenance_request' => [
                'purpose' => 'the description of a maintenance request in a rented home',
                'outputs' => ['notes' => ['Notes', 'plain', 1000, 'clear description of the problem: what, where, since when']],
                'context' => ['issue_type' => 'Issue type', 'property_id' => 'Property', 'unit_id' => 'Unit'],
            ],
            'notification' => [
                'purpose' => 'an automatic notification sent by property management software',
                'outputs' => [
                    'subject' => ['Email subject', 'plain', 120, 'short subject'],
                    'message' => ['Email message', 'html', 4000, 'friendly, clear email body'],
                    'sms_message' => ['SMS / WhatsApp text', 'plain', 160, 'under 160 characters including placeholders'],
                ],
                'context' => ['name' => 'Notification'],
            ],
            'blog' => [
                'purpose' => 'a blog post for a property management / real estate website',
                'outputs' => ['title' => ['Title', 'plain', 120, 'catchy title'], 'content' => ['Post', 'html', 10000, 'blog post with short paragraphs and subheadings']],
                'context' => ['title' => 'Current title'],
            ],
            'faq' => [
                'purpose' => 'a frequently asked question on a property management website',
                'outputs' => ['question' => ['Question', 'plain', 200, 'the question'], 'description' => ['Answer', 'plain', 1500, 'clear helpful answer']],
                'context' => ['question' => 'Current question'],
            ],
        ]);
    }

    public static function tones()
    {
        return ['professional' => __('Professional'), 'friendly' => __('Friendly'), 'formal' => __('Formal'), 'casual' => __('Casual'), 'persuasive' => __('Persuasive')];
    }

    public static function lengths()
    {
        return ['short' => __('Short'), 'medium' => __('Medium'), 'long' => __('Long')];
    }

    // shown for owners whose plan includes AI, and always for the super admin (when a key is set)
    public static function available()
    {
        if (\Auth::user()->type == 'super admin') {
            return (bool) AiService::chain();
        }
        return AiService::enabledFor(parentId());
    }

    /**
     * Returns ['fields' => [form field => value]] or ['error' => message].
     * $context: [form field => value] read from the form; $placeholders: allowed {codes} (notifications).
     */
    public static function write($type, array $context, $brief, $tone, $length, $language, array $placeholders = [])
    {
        $def = self::types()[$type] ?? null;
        if (!$def) {
            return ['error' => __('Unknown content type.')];
        }
        $tone = array_key_exists($tone, self::tones()) ? $tone : 'professional';
        $length = array_key_exists($length, self::lengths()) ? $length : 'medium';
        $language = mb_substr(trim((string) $language), 0, 30) ?: 'English';

        $facts = [];
        foreach ($def['context'] as $field => $label) {
            $value = trim(strip_tags((string) ($context[$field] ?? '')));
            if ($value !== '' && !preg_match('/^select\b/i', $value)) {
                $facts[$label] = mb_substr($value, 0, 300);
            }
        }

        $spec = [];
        foreach ($def['outputs'] as $field => [$label, $format, $max, $instruction]) {
            $spec[$field] = $instruction . ($format == 'html' ? ' (plain text: use blank lines between paragraphs, lines starting with "- " for bullets, lines starting with "## " for subheadings)' : '');
        }

        $system = 'You write ' . $def['purpose'] . ".\n"
            . "Tone: {$tone}. Length: {$length}. Language: {$language}.\n"
            . "Use the provided facts and the user's brief. Do not invent specific facts such as prices, dates, names, addresses or legal claims that were not given.\n"
            . ($placeholders ? 'You may insert these placeholders exactly as written, where they fit: ' . implode(' ', $placeholders) . ". Never invent other placeholders.\n" : '')
            . 'Respond as JSON with exactly these keys: ' . json_encode($spec, JSON_UNESCAPED_UNICODE) . '.';
        $user = json_encode(['brief' => mb_substr(trim((string) $brief), 0, 1000), 'facts' => $facts], JSON_UNESCAPED_UNICODE);

        $maxTokens = ['short' => 500, 'medium' => 1000, 'long' => 1800][$length];
        $schema = ['type' => 'object', 'properties' => array_map(function () {
            return ['type' => 'string'];
        }, $def['outputs']), 'required' => array_keys($def['outputs']), 'additionalProperties' => false];
        $data = AiService::json([['role' => 'system', 'content' => $system], ['role' => 'user', 'content' => $user]], $maxTokens, $schema);
        if (!$data) {
            return ['error' => __('The AI could not write this right now. Please try again.')];
        }

        $fields = [];
        foreach ($def['outputs'] as $field => [$label, $format, $max, $instruction]) {
            $value = $data[$field] ?? '';
            if (is_array($value)) {
                $value = implode("\n", array_filter($value, 'is_string'));
            }
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }
            if ($format == 'code') {
                $fields[$field] = mb_substr(preg_replace('/[^A-Z0-9]/', '', mb_strtoupper($value)), 0, $max);
            } elseif ($format == 'html') {
                $fields[$field] = self::toHtml(mb_substr($value, 0, $max));
            } else {
                $fields[$field] = mb_substr(trim(strip_tags($value)), 0, $max);
            }
        }
        return $fields ? ['fields' => $fields] : ['error' => __('The AI could not write this right now. Please try again.')];
    }

    // light markdown from the AI -> escaped HTML (the AI never supplies tags itself)
    public static function toHtml($text)
    {
        $text = strip_tags(str_replace(["\r\n", "\r"], "\n", $text));
        $html = '';
        foreach (preg_split("/\n\s*\n/", $text) as $block) {
            $lines = array_values(array_filter(array_map('trim', explode("\n", trim($block))), 'strlen'));
            if (!$lines) {
                continue;
            }
            if (preg_match('/^#{1,6}\s+(.+)$/', $lines[0], $m) && count($lines) == 1) {
                $html .= '<h4>' . e($m[1]) . '</h4>';
            } elseif (count(array_filter($lines, function ($l) { return preg_match('/^([-*•]|\d+[.)])\s+/', $l); })) == count($lines)) {
                $ordered = preg_match('/^\d+[.)]\s+/', $lines[0]);
                $items = array_map(function ($l) { return '<li>' . e(preg_replace('/^([-*•]|\d+[.)])\s+/', '', $l)) . '</li>'; }, $lines);
                $html .= ($ordered ? '<ol>' : '<ul>') . implode('', $items) . ($ordered ? '</ol>' : '</ul>');
            } else {
                $html .= '<p>' . implode('<br>', array_map('e', $lines)) . '</p>';
            }
        }
        return $html;
    }
}
