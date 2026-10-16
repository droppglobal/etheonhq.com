<?php

namespace App\Http\Controllers;

use App\Models\AssistantMessage;
use App\Support\AiService;
use App\Support\Notify;
use App\Support\TenantAssistant;
use Illuminate\Http\Request;

/**
 * Tenant chat assistant.
 */
class AssistantController extends Controller
{
    private function tenant()
    {
        return \Auth::user()->type == 'tenant' ? \Auth::user()->tenants : null;
    }

    public function index()
    {
        $tenant = $this->tenant();
        if (!$tenant) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $history = $this->history($tenant, 50);
        $lastId = (int) optional($history->last())->id;
        $aiEnabled = AiService::enabledFor($tenant->parent_id);
        return view('assistant.index', compact('history', 'aiEnabled', 'lastId'));
    }

    public function message(Request $request)
    {
        $tenant = $this->tenant();
        if (!$tenant) {
            return response()->json(['error' => __('Permission Denied!')], 403);
        }
        $text = trim((string) $request->input('message'));
        if ($text === '' || mb_strlen($text) > 1000) {
            return response()->json(['error' => __('Please type a message (up to 1000 characters).')], 422);
        }

        $history = $this->history($tenant, 20)->map(fn($m) => ['role' => $m->role, 'content' => $m->content])->all();
        $result = TenantAssistant::reply($tenant, $text, $history);

        $question = AssistantMessage::log($tenant, 'user', $text, 'web', ['needs_reply' => $result['needs_human'] ? 1 : 0]);
        $answer = AssistantMessage::log($tenant, 'assistant', $result['reply'], 'web');
        if ($result['needs_human']) {
            self::alertStaff($tenant, $text);
        }

        return response()->json($result + ['last_id' => $answer->id]);
    }

    public function createRequest(Request $request)
    {
        $tenant = $this->tenant();
        if (!$tenant || !\Auth::user()->can('create maintenance request')) {
            return response()->json(['error' => __('Permission Denied!')], 403);
        }
        $description = trim((string) $request->input('description'));
        if (mb_strlen($description) < 3) {
            return response()->json(['error' => __('Please describe the problem.')], 422);
        }
        $maintenance = TenantAssistant::createRequest($tenant, $description, 'assistant');
        return response()->json([
            'reply' => __('Done! I raised a maintenance request (priority: :priority). You can follow it under Maintenance Requests.', ['priority' => \App\Support\MaintenanceTriage::priorities()[$maintenance->ai_priority] ?? $maintenance->ai_priority]),
        ]);
    }

    // "New chat" only starts a fresh screen for the tenant; the office keeps the full record
    public function clear()
    {
        $tenant = $this->tenant();
        if ($tenant) {
            session(['assistant_cleared_after' => (int) AssistantMessage::where('tenant_id', $tenant->id)->max('id')]);
        }
        return redirect()->route('assistant.index');
    }

    // new replies from the office since the given message id (the chat page polls this)
    public function updates(Request $request)
    {
        $tenant = $this->tenant();
        if (!$tenant) {
            return response()->json(['messages' => []], 403);
        }
        $messages = AssistantMessage::where('tenant_id', $tenant->id)->where('id', '>', (int) $request->query('after'))
            ->where('role', 'staff')->orderBy('id')->get(['id', 'content']);
        return response()->json(['messages' => $messages]);
    }

    private function history($tenant, $limit)
    {
        return AssistantMessage::where('tenant_id', $tenant->id)
            ->where('id', '>', (int) session('assistant_cleared_after', 0))
            ->orderByDesc('id')->limit($limit)->get()->reverse()->values();
    }

    public static function alertStaff($tenant, $text)
    {
        Notify::push(
            Notify::staffOf($tenant->parent_id, 'manage assistant chats'),
            __('Tenant needs a reply') . ': ' . optional($tenant->user)->name,
            $text,
            route('assistant-chats.show', $tenant->id)
        );
    }
}
