<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Property;
use Illuminate\Http\Request;

class PollController extends Controller
{
    private function tenant()
    {
        return \Auth::user()->type == 'tenant' ? \Auth::user()->tenants : null;
    }

    // a tenant sees polls for every property or their own; closed ones stay visible with their results
    private function tenantPolls($tenant)
    {
        return Poll::where('parent_id', parentId())->whereIn('property_id', [0, $tenant->property]);
    }

    public function index()
    {
        if ($tenant = $this->tenant()) {
            if (!\Auth::user()->can('vote poll')) {
                return redirect()->back()->with('error', __('Permission Denied!'));
            }
            $polls = $this->tenantPolls($tenant)->orderBy('id', 'desc')->get();
            $myVotes = PollVote::where('tenant_id', $tenant->id)->whereIn('poll_id', $polls->pluck('id'))->pluck('option_id', 'poll_id');
            return view('poll.tenant', compact('polls', 'myVotes'));
        }
        if (!\Auth::user()->can('manage poll')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $polls = Poll::where('parent_id', parentId())->orderBy('id', 'desc')->get();
        return view('poll.index', compact('polls'));
    }

    public function create()
    {
        if (!\Auth::user()->can('manage poll')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $properties = Property::where('parent_id', parentId())->pluck('name', 'id')->prepend(__('All properties'), 0);
        return view('poll.form', compact('properties'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('manage poll')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $options = array_values(array_filter(array_map('trim', (array) $request->options), 'strlen'));
        $validator = \Validator::make($request->all() + ['option_count' => count($options)], [
            'question' => 'required|max:191',
            'closes_on' => 'nullable|date|after_or_equal:today',
            'option_count' => 'integer|min:2|max:10',
        ], ['option_count.min' => __('Add at least two options.')]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        if (!empty($request->property_id) && !Property::where('parent_id', parentId())->where('id', $request->property_id)->exists()) {
            return redirect()->back()->with('error', __('Property not found.'));
        }
        $poll = Poll::create([
            'question' => $request->question,
            'description' => $request->description,
            'property_id' => (int) $request->property_id,
            'closes_on' => $request->closes_on ?: null,
            'show_results' => $request->has('show_results') ? 1 : 0,
            'is_active' => 1,
            'parent_id' => parentId(),
        ]);
        foreach ($options as $i => $label) {
            PollOption::create(['poll_id' => $poll->id, 'label' => mb_substr($label, 0, 191), 'sort_order' => $i]);
        }
        return redirect()->back()->with('success', __('Poll published.'));
    }

    public function show($id)
    {
        $poll = Poll::where('parent_id', parentId())->find($id);
        if ($this->tenant() || !\Auth::user()->can('manage poll') || !$poll) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $results = $poll->results();
        return view('poll.show', compact('poll', 'results'));
    }

    public function close($id)
    {
        $poll = Poll::where('parent_id', parentId())->find($id);
        if ($this->tenant() || !\Auth::user()->can('manage poll') || !$poll) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $poll->is_active = $poll->is_active ? 0 : 1;
        $poll->save();
        return redirect()->back()->with('success', $poll->is_active ? __('Poll reopened.') : __('Poll closed.'));
    }

    public function destroy($id)
    {
        $poll = Poll::where('parent_id', parentId())->find($id);
        if ($this->tenant() || !\Auth::user()->can('manage poll') || !$poll) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        PollVote::where('poll_id', $poll->id)->delete();
        PollOption::where('poll_id', $poll->id)->delete();
        $poll->delete();
        return redirect()->back()->with('success', __('Poll deleted.'));
    }

    public function vote(Request $request, $id)
    {
        $tenant = $this->tenant();
        $poll = $tenant ? $this->tenantPolls($tenant)->find($id) : null;
        if (!$poll || !\Auth::user()->can('vote poll')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if (!$poll->isOpen()) {
            return redirect()->back()->with('error', __('This poll is closed.'));
        }
        $option = PollOption::where('poll_id', $poll->id)->find($request->option_id);
        if (!$option) {
            return redirect()->back()->with('error', __('Please choose an option.'));
        }
        if (PollVote::where('poll_id', $poll->id)->where('tenant_id', $tenant->id)->exists()) {
            return redirect()->back()->with('error', __('You have already voted.'));
        }
        try {
            PollVote::create(['poll_id' => $poll->id, 'option_id' => $option->id, 'tenant_id' => $tenant->id]);
        } catch (\Illuminate\Database\QueryException $e) {
            // unique (poll_id, tenant_id): a double-click from the same tenant
            return redirect()->back()->with('error', __('You have already voted.'));
        }
        return redirect()->back()->with('success', __('Thanks for voting!'));
    }
}
