<?php

namespace App\Support;

use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Tenant;
use App\Models\Type;

/**
 * Answers a tenant's questions about their own tenancy, in the web chat and over WhatsApp.
 * Common questions are answered from the database directly; anything else goes to the AI (if enabled),
 * which only ever sees this tenant's own facts.
 */
class TenantAssistant
{
    public static function facts(Tenant $tenant)
    {
        $settings = settingsById($tenant->parent_id);
        $open = Invoice::where('parent_id', $tenant->parent_id)->where('tenant', $tenant->id)
            ->whereIn('status', ['unpaid', 'partial_paid'])->orderBy('end_date')->get();
        $requests = MaintenanceRequest::where('parent_id', $tenant->parent_id)->where('tenant_id', $tenant->id)
            ->whereIn('status', ['pending', 'in_progress'])->orderBy('id', 'desc')->get();

        return [
            'tenant_name' => optional($tenant->user)->name,
            'property' => optional($tenant->properties)->name,
            'unit' => optional($tenant->units)->name,
            'rent' => !empty($tenant->units) ? priceFormatFor($tenant->units->rent, $settings) . ' / ' . $tenant->units->rent_type : null,
            'lease_start' => $tenant->lease_start_date,
            'lease_end' => $tenant->lease_end_date,
            'amount_due' => priceFormatFor($open->sum(function ($invoice) {
                return max(0, $invoice->getInvoiceDueAmount());
            }), $settings),
            'open_invoices' => $open->map(function ($invoice) use ($settings) {
                return invoicePrefix() . $invoice->invoice_id . ' - ' . priceFormatFor($invoice->getInvoiceDueAmount(), $settings) . ' ' . __('due') . ' ' . $invoice->end_date;
            })->values()->all(),
            'open_maintenance_requests' => $requests->map(function ($request) {
                return (optional($request->types)->title ?: __('Request')) . ' - ' . $request->status . ' (' . __('raised') . ' ' . $request->request_date . ')';
            })->values()->all(),
            'autopay' => $tenant->autopay_enabled ? __('on') : __('off'),
            'landlord_company' => $settings['company_name'] ?? '',
            'landlord_email' => $settings['company_email'] ?? '',
            'landlord_phone' => $settings['company_phone'] ?? '',
        ];
    }

    // Keyword answers that need no AI. Returns null when the question is something else.
    public static function quickAnswer(Tenant $tenant, $text)
    {
        $t = mb_strtolower(trim($text));
        $f = self::facts($tenant);

        if (preg_match('/^(help|menu|hi|hello|hey|start)\b/', $t)) {
            return __('Hi :name! You can ask me about your rent, balance, lease or repairs. Quick commands: BALANCE, LEASE, REPAIRS, CONTACT, or REPAIR followed by the problem (for example "REPAIR kitchen tap leaking").', ['name' => $f['tenant_name']]);
        }
        if (preg_match('/\b(balance|owe|due|outstanding|pay(ment)?s?|invoice)\b/', $t)) {
            if (empty($f['open_invoices'])) {
                return __('You have nothing to pay right now. Thank you!');
            }
            return __('You owe :amount.', ['amount' => $f['amount_due']]) . "\n" . implode("\n", $f['open_invoices']) . "\n" . __('Autopay is :state.', ['state' => $f['autopay']]);
        }
        if (preg_match('/\b(lease|contract|move.?out|end date|renew)\b/', $t)) {
            return __('Your lease for :unit at :property runs from :start to :end.', ['unit' => $f['unit'], 'property' => $f['property'], 'start' => $f['lease_start'], 'end' => $f['lease_end']]);
        }
        if (preg_match('/^repairs?$|\b(my|open) (requests?|repairs?)\b|\bstatus\b/', $t)) {
            return empty($f['open_maintenance_requests'])
                ? __('You have no open maintenance requests.')
                : __('Your open requests:') . "\n" . implode("\n", $f['open_maintenance_requests']);
        }
        if (preg_match('/\b(contact|phone|email|landlord|manager|office)\b/', $t)) {
            return __('You can reach :company at :phone or :email.', ['company' => $f['landlord_company'], 'phone' => $f['landlord_phone'], 'email' => $f['landlord_email']]);
        }
        if (preg_match('/\brent\b/', $t)) {
            return __('Your rent is :rent.', ['rent' => $f['rent']]);
        }
        return null;
    }

    // "REPAIR <description>" command
    public static function repairCommand($text)
    {
        if (preg_match('/^\s*repair\s*[:\-]?\s*(.{3,})$/is', $text, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    /**
     * Returns ['reply' => string, 'suggest_request' => string|null, 'needs_human' => bool].
     * suggest_request is a description the tenant can confirm to raise a maintenance request;
     * needs_human means the assistant could not help and a staff member should answer.
     */
    public static function reply(Tenant $tenant, $text, array $history = [])
    {
        if ($answer = self::quickAnswer($tenant, $text)) {
            return ['reply' => $answer, 'suggest_request' => null, 'needs_human' => false];
        }

        if (!AiService::enabledFor($tenant->parent_id)) {
            if (self::looksLikeRepair($text)) {
                return ['reply' => __('Sorry to hear that. That sounds like something that needs fixing.'), 'suggest_request' => $text, 'needs_human' => false];
            }
            return [
                'reply' => __('I can help with your balance, lease, repairs and contact details. Try "BALANCE", "LEASE", "REPAIRS" or "REPAIR" followed by the problem.'),
                'suggest_request' => null,
                'needs_human' => false,
            ];
        }

        $system = "You are a helpful assistant for a residential tenant, working for their property manager.\n"
            . "Answer ONLY using the facts below about this tenant. If the answer is not in the facts, say you don't know and suggest contacting the property manager.\n"
            . "Never invent amounts, dates or policies. Keep replies short and friendly. Reply in the tenant's language.\n"
            . "If the tenant describes something broken or a problem in the home, set suggest_request to a one-sentence description of the problem; otherwise an empty string.\n"
            . "Set needs_human to true when the facts do not contain the answer, when the tenant asks for a person, or for complaints, disputes or anything urgent or unsafe; then tell the tenant the office has been asked to reply.\n"
            . "Respond as JSON: {\"reply\": string, \"suggest_request\": string, \"needs_human\": boolean}.\n"
            . 'Facts: ' . json_encode(self::facts($tenant), JSON_UNESCAPED_UNICODE);

        $messages = [['role' => 'system', 'content' => $system]];
        foreach (array_slice($history, -8) as $turn) {
            if (in_array($turn['role'] ?? '', ['user', 'assistant']) && isset($turn['content'])) {
                $messages[] = ['role' => $turn['role'], 'content' => mb_substr($turn['content'], 0, 1000)];
            } elseif (($turn['role'] ?? '') == 'staff' && isset($turn['content'])) {
                // replies typed by the office are part of the conversation the assistant continues
                $messages[] = ['role' => 'assistant', 'content' => '(Property manager) ' . mb_substr($turn['content'], 0, 1000)];
            }
        }
        $messages[] = ['role' => 'user', 'content' => mb_substr($text, 0, 1000)];

        $data = AiService::json($messages, 400, [
            'type' => 'object',
            'properties' => ['reply' => ['type' => 'string'], 'suggest_request' => ['type' => 'string'], 'needs_human' => ['type' => 'boolean']],
            'required' => ['reply', 'suggest_request', 'needs_human'],
            'additionalProperties' => false,
        ]);
        if (empty($data['reply'])) {
            return ['reply' => __('Sorry, I could not answer that right now. I have asked the office to reply to you here.'), 'suggest_request' => null, 'needs_human' => true];
        }
        return [
            'reply' => (string) $data['reply'],
            'suggest_request' => !empty($data['suggest_request']) ? mb_substr((string) $data['suggest_request'], 0, 500) : null,
            'needs_human' => !empty($data['needs_human']),
        ];
    }

    public static function looksLikeRepair($text)
    {
        return (bool) preg_match('/\b(broken|leak|leaking|not working|repair|fix|blocked|clogged|no water|no power|damaged|crack|mould|mold|pest)\b/i', $text);
    }

    // Raises a maintenance request for the tenant's own unit; triage runs on it.
    public static function createRequest(Tenant $tenant, $description, $source = 'web')
    {
        $issueTypes = Type::where('parent_id', $tenant->parent_id)->where('type', 'issue')->pluck('title', 'id');
        $issueType = MaintenanceTriage::guessIssueType($description, $issueTypes);

        $request = new MaintenanceRequest();
        $request->property_id = $tenant->property;
        $request->unit_id = $tenant->unit;
        $request->tenant_id = $tenant->id;
        $request->issue_type = $issueType ?: (int) $issueTypes->keys()->first();
        $request->status = 'pending';
        $request->request_date = now()->toDateString();
        $request->notes = mb_substr($description, 0, 2000);
        $request->source = $source;
        $request->parent_id = $tenant->parent_id;
        $request->save();

        MaintenanceTriage::apply($request);
        return $request;
    }
}
