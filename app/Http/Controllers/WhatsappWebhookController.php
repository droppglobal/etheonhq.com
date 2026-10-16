<?php

namespace App\Http\Controllers;

use App\Models\AssistantMessage;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantAssistant;
use Illuminate\Http\Request;
use Twilio\Security\RequestValidator;

/**
 * Two-way WhatsApp: Twilio posts each incoming tenant message here and we reply with TwiML.
 * One URL per owner account: /webhook/whatsapp/{owner code}.
 */
class WhatsappWebhookController extends Controller
{
    private function twiml($message)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><Response>'
            . ($message !== null ? '<Message>' . htmlspecialchars(mb_substr($message, 0, 1500), ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</Message>' : '')
            . '</Response>';
        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    private static function digits($phone)
    {
        return preg_replace('/\D+/', '', (string) $phone);
    }

    // Match the sender against this owner's tenants by the last 10 digits of the phone number
    private function findTenant($ownerId, $from)
    {
        $sender = substr(self::digits(str_replace('whatsapp:', '', $from)), -10);
        if (strlen($sender) < 7) {
            return null;
        }
        $users = User::where('parent_id', $ownerId)->where('type', 'tenant')->whereNotNull('phone_number')->get();
        foreach ($users as $user) {
            if (substr(self::digits($user->phone_number), -10) === $sender) {
                $tenant = Tenant::where('parent_id', $ownerId)->where('user_id', $user->id)->whereNull('exit_date')->first();
                if ($tenant) {
                    return $tenant;
                }
            }
        }
        return null;
    }

    public function handle(Request $request, $code)
    {
        $owner = User::where('code', $code)->where('type', 'owner')->first();
        if (!$owner) {
            abort(404);
        }

        // only Twilio, signing with this owner's auth token, may post here
        $token = settingsById($owner->id)['twilio_token'] ?? '';
        $signature = (string) $request->header('X-Twilio-Signature');
        if ($token === '' || $signature === '' || !(new RequestValidator($token))->validate($signature, $request->fullUrl(), $request->post())) {
            abort(403);
        }

        $text = trim((string) $request->input('Body'));
        $tenant = $this->findTenant($owner->id, $request->input('From'));
        if (!$tenant) {
            return $this->twiml(__('Sorry, we could not find a tenancy for this number. Please contact the office.'));
        }
        if ($text === '') {
            return $this->twiml(__('Please send your question as text.'));
        }

        if ($description = TenantAssistant::repairCommand($text)) {
            $maintenance = TenantAssistant::createRequest($tenant, $description, 'whatsapp');
            $reply = __('Thanks, your maintenance request has been raised (priority: :priority). We will be in touch.', [
                'priority' => \App\Support\MaintenanceTriage::priorities()[$maintenance->ai_priority] ?? $maintenance->ai_priority,
            ]);
            AssistantMessage::log($tenant, 'user', $text, 'whatsapp');
            AssistantMessage::log($tenant, 'assistant', $reply, 'whatsapp');
            return $this->twiml($reply);
        }

        $history = AssistantMessage::where('tenant_id', $tenant->id)->orderByDesc('id')->limit(8)->get()->reverse()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])->all();
        $result = TenantAssistant::reply($tenant, $text, $history);
        $reply = $result['reply'];
        if (!empty($result['suggest_request'])) {
            $reply .= "\n\n" . __('To raise a maintenance request, reply: REPAIR followed by the problem.');
        }
        AssistantMessage::log($tenant, 'user', $text, 'whatsapp', ['needs_reply' => $result['needs_human'] ? 1 : 0]);
        AssistantMessage::log($tenant, 'assistant', $reply, 'whatsapp');
        if ($result['needs_human']) {
            AssistantController::alertStaff($tenant, $text);
        }
        return $this->twiml($reply);
    }
}
