<?php

namespace App\Http\Controllers;

use App\Models\AssistantMessage;
use App\Models\Tenant;
use Illuminate\Http\Request;

/**
 * Office view of tenant conversations with the assistant (web chat and WhatsApp):
 * read them, answer when the assistant handed over, and mark them done.
 */
class AssistantChatController extends Controller
{
    private function allowed()
    {
        return \Auth::user()->can('manage assistant chats');
    }

    private function tenantFor($id)
    {
        return Tenant::where('parent_id', parentId())->where('id', (int) $id)->first();
    }

    public function index(Request $request)
    {
        if (!$this->allowed()) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $latest = AssistantMessage::where('parent_id', parentId())
            ->selectRaw('tenant_id, MAX(id) as last_id, COUNT(*) as total, SUM(needs_reply) as waiting')
            ->groupBy('tenant_id');
        if ($request->query('filter') == 'waiting') {
            $latest->havingRaw('SUM(needs_reply) > 0');
        }
        $threads = $latest->orderByDesc('waiting')->orderByDesc('last_id')->paginate(25)->withQueryString();
        $lastMessages = AssistantMessage::whereIn('id', $threads->pluck('last_id'))->get()->keyBy('tenant_id');
        $tenants = Tenant::with(['user', 'properties', 'units'])->whereIn('id', $threads->pluck('tenant_id'))->get()->keyBy('id');
        return view('assistant_chat.index', compact('threads', 'lastMessages', 'tenants'));
    }

    public function show($id)
    {
        $tenant = $this->tenantFor($id);
        if (!$this->allowed() || !$tenant) {
            return redirect()->route('assistant-chats.index')->with('error', __('Permission Denied!'));
        }
        $messages = AssistantMessage::with('staff')->where('tenant_id', $tenant->id)->orderByDesc('id')->limit(200)->get()->reverse();
        $lastChannel = optional($messages->where('role', 'user')->last())->channel ?: 'web';
        $whatsapp = !empty(optional($tenant->user)->phone_number) && !empty(settings()['twilio_sid']);
        return view('assistant_chat.show', compact('tenant', 'messages', 'lastChannel', 'whatsapp'));
    }

    public function reply(Request $request, $id)
    {
        $tenant = $this->tenantFor($id);
        if (!$this->allowed() || !$tenant) {
            return redirect()->route('assistant-chats.index')->with('error', __('Permission Denied!'));
        }
        $text = trim((string) $request->input('message'));
        if ($text === '' || mb_strlen($text) > 2000) {
            return redirect()->back()->with('error', __('Please type a reply (up to 2000 characters).'));
        }
        $channel = $request->input('channel') == 'whatsapp' ? 'whatsapp' : 'web';

        $note = '';
        if ($channel == 'whatsapp') {
            $sent = sendTwilioWhatsappMsg(['to' => optional($tenant->user)->phone_number, 'body' => $text, 'parent_id' => $tenant->parent_id]);
            if (!$sent) {
                $channel = 'web';
                $note = ' ' . __('WhatsApp could not be sent, so the reply was posted in the app chat only.');
            }
        }
        AssistantMessage::log($tenant, 'staff', $text, $channel, ['staff_id' => \Auth::id()]);
        AssistantMessage::where('tenant_id', $tenant->id)->where('needs_reply', 1)->update(['needs_reply' => 0]);

        \App\Support\Notify::push($tenant->user_id, __('Reply from the office'), $text, route('assistant.index'));
        return redirect()->route('assistant-chats.show', $tenant->id)->with($note ? 'error' : 'success', ($note ? '' : __('Reply sent.')) . $note);
    }

    public function resolve($id)
    {
        $tenant = $this->tenantFor($id);
        if (!$this->allowed() || !$tenant) {
            return redirect()->route('assistant-chats.index')->with('error', __('Permission Denied!'));
        }
        AssistantMessage::where('tenant_id', $tenant->id)->where('needs_reply', 1)->update(['needs_reply' => 0]);
        return redirect()->back()->with('success', __('Marked as handled.'));
    }
}
