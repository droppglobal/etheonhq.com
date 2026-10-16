<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\MaintenanceRequestController;
use App\Models\Comments;
use App\Models\MaintenanceRequest;
use App\Support\DashboardData;
use Illuminate\Http\Request;

/**
 * Mobile API for maintainers: their job queue, job details, "on my way", status updates with photos.
 */
class MaintainerApiController extends BaseController
{
    private function jobs()
    {
        return MaintenanceRequest::with(['types', 'properties', 'units', 'tenetData.user'])->where('maintainer_id', \Auth::id());
    }

    private function summary(MaintenanceRequest $m)
    {
        return [
            'id' => $m->id,
            'title' => optional($m->types)->title ?: __('Repair request'),
            'property' => optional($m->properties)->name,
            'unit' => optional($m->units)->name,
            'status' => $m->status,
            'status_label' => MaintenanceRequest::status()[$m->status] ?? $m->status,
            'priority' => $m->ai_priority,
            'priority_label' => $m->ai_priority ? (\App\Support\MaintenanceTriage::priorities()[$m->ai_priority] ?? null) : null,
            'request_date' => $this->date($m->request_date),
            'preferred_visit' => $m->preferredVisitText(),
            'on_the_way_at' => $this->dateTime($m->on_the_way_at),
        ];
    }

    public function home()
    {
        $extra = DashboardData::maintainer(\Auth::user());
        $me = \Auth::id();
        return $this->ok([
            'counts' => [
                'urgent' => $extra['urgentOpen'],
                'pending' => MaintenanceRequest::where('maintainer_id', $me)->where('status', 'pending')->count(),
                'in_progress' => $extra['inProgress'],
                'completed_this_month' => $extra['completedThisMonth'],
                'hours_this_month' => (float) $extra['hoursThisMonth'],
                'new_today' => MaintenanceRequest::where('maintainer_id', $me)->whereDate('request_date', date('Y-m-d'))->count(),
            ],
            'rating' => ['average' => $extra['avgRating'], 'count' => $extra['ratingCount']],
            'queue' => collect($extra['queue'])->map(fn($m) => $this->summary($m))->values(),
            'preventive' => collect($extra['upcomingPreventive'])->map(fn($t) => [
                'title' => $t->title, 'date' => $this->date($t->next_date),
                'property' => optional($t->properties)->name, 'unit' => optional($t->units)->name,
            ])->values(),
            'recent_feedback' => collect($extra['recentFeedback'])->map(fn($m) => [
                'rating' => $m->tenant_rating, 'feedback' => $m->tenant_feedback, 'title' => optional($m->types)->title, 'date' => $this->date($m->updated_at),
            ])->values(),
            // visits planned for today: tenants' preferred date, or jobs reported today without one
            'today' => $this->jobs()->whereIn('status', ['pending', 'in_progress'])
                ->where(fn($q) => $q->whereDate('preferred_date', date('Y-m-d'))->orWhere(fn($q2) => $q2->whereNull('preferred_date')->whereDate('request_date', date('Y-m-d'))))
                ->orderByRaw("FIELD(preferred_slot, 'morning', 'afternoon', 'evening', 'any')")->get()->map(fn($m) => $this->summary($m))->values(),
            'earnings_this_month' => $this->money(MaintenanceRequest::where('maintainer_id', $me)->where('status', 'completed')
                ->whereMonth('fixed_date', date('m'))->whereYear('fixed_date', date('Y'))->sum('amount')),
            'profile' => $this->profileData(),
        ]);
    }

    // trade, covered properties and lifetime figures
    private function profileData()
    {
        $me = \Auth::id();
        $maintainer = \App\Models\Maintainer::where('user_id', $me)->first();
        $done = MaintenanceRequest::where('maintainer_id', $me)->where('status', 'completed');
        $rated = MaintenanceRequest::where('maintainer_id', $me)->where('tenant_rating', '>', 0);
        return [
            'trade' => $maintainer ? optional(\App\Models\Type::find($maintainer->type_id))->title : null,
            'properties' => $maintainer ? collect($maintainer->properties())->filter()->map(fn($p) => [
                'id' => $p->id, 'name' => $p->name, 'address' => $this->address($p),
            ])->values() : [],
            'jobs_completed' => (clone $done)->count(),
            'hours_total' => (float) (clone $done)->sum('labor_hours'),
            'earned_total' => $this->money((clone $done)->sum('amount')),
            'rating' => ['average' => ($c = (clone $rated)->count()) ? round((clone $rated)->avg('tenant_rating'), 1) : null, 'count' => $c],
            'member_since' => $this->date(optional(\Auth::user())->created_at),
        ];
    }

    public function profile()
    {
        return $this->ok($this->profileData());
    }

    /**
     * Upcoming work by day for the next 14 days: open jobs on the tenant's preferred date (jobs without one
     * are listed under "anytime") and preventive tasks on their due date.
     */
    public function schedule()
    {
        $from = now()->startOfDay();
        $to = now()->addDays(14)->endOfDay();
        $items = collect();
        foreach ($this->jobs()->whereIn('status', ['pending', 'in_progress'])->get() as $m) {
            $date = $m->preferred_date && $m->preferred_date >= $from->toDateString() ? $m->preferred_date : null;
            if ($date && $date > $to->toDateString()) {
                continue;
            }
            $items->push(['kind' => 'job', 'date' => $date, 'slot' => $m->preferred_slot, 'job' => $this->summary($m)]);
        }
        $tasks = \App\Models\MaintenanceSchedule::with(['properties', 'units'])->where('maintainer_id', \Auth::id())->where('is_active', 1)
            ->whereBetween('next_date', [$from->toDateString(), $to->toDateString()])->get();
        foreach ($tasks as $t) {
            $items->push(['kind' => 'task', 'date' => $t->next_date, 'slot' => null, 'task' => [
                'title' => $t->title, 'property' => optional($t->properties)->name, 'unit' => optional($t->units)->name,
            ]]);
        }
        $slotOrder = ['morning' => 0, 'afternoon' => 1, 'evening' => 2];
        $days = $items->groupBy(fn($i) => $i['date'] ?: 'anytime')
            ->sortKeys()
            ->map(fn($group, $day) => [
                'date' => $day == 'anytime' ? null : $this->date($day),
                'label' => $day == 'anytime' ? __('No set day') : ($day == date('Y-m-d') ? __('Today') : ($day == date('Y-m-d', strtotime('+1 day')) ? __('Tomorrow') : dateFormat($day))),
                'items' => $group->sortBy(fn($i) => $slotOrder[$i['slot']] ?? 3)->map(fn($i) => array_filter($i + [
                    'slot_label' => $i['slot'] && $i['slot'] != 'any' ? (MaintenanceRequest::slots()[$i['slot']] ?? null) : null,
                ], fn($v) => $v !== null))->values(),
            ]);
        // "No set day" last
        $ordered = $days->except('anytime')->values();
        if ($days->has('anytime')) {
            $ordered->push($days['anytime']);
        }
        return $this->ok($ordered);
    }

    public function index(Request $request)
    {
        $query = $this->jobs();
        if ($request->status == 'open' || !$request->status) {
            $query->whereIn('status', ['pending', 'in_progress']);
        } elseif (in_array($request->status, ['pending', 'in_progress', 'completed'])) {
            $query->where('status', $request->status);
        }
        // most urgent first, then oldest
        $rank = "CASE ai_priority WHEN 'emergency' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END";
        $query = $request->status == 'completed' ? $query->orderByDesc('fixed_date')->orderByDesc('id') : $query->orderByRaw($rank)->orderBy('request_date');
        return $this->paginated($query->paginate(20), fn($m) => $this->summary($m));
    }

    public function show($id)
    {
        $m = $this->jobs()->findOrFail($id);
        $tenantUser = optional($m->tenetData)->user;
        $comments = Comments::where('maintenance_id', $m->id)->orderBy('id')->get();
        $users = \App\Models\User::whereIn('id', $comments->pluck('user_id'))->get()->keyBy('id');
        return $this->ok($this->summary($m) + [
            'notes' => $m->notes,
            'ai_summary' => $m->ai_summary,
            'address' => $this->address($m->properties),
            'tenant' => $tenantUser ? ['name' => $tenantUser->name, 'phone' => $tenantUser->phone_number] : null,
            'photo' => $this->file($m->issue_attachment, 'upload/issue_attachment/'),
            'after_photo' => $this->file($m->after_photo, 'upload/issue_attachment/'),
            'amount' => $this->money($m->amount),
            'labor_hours' => (float) $m->labor_hours,
            'fixed_date' => $this->date($m->fixed_date),
            'rating' => $m->tenant_rating ?: null,
            'feedback' => $m->tenant_feedback,
            'can_update' => in_array($m->status, ['pending', 'in_progress']),
            'maps_query' => $this->address($m->properties),
            // earlier repairs at the same unit help to spot repeat problems
            'unit_history' => MaintenanceRequest::with('types')->where('parent_id', $m->parent_id)->where('property_id', $m->property_id)
                ->where('unit_id', $m->unit_id)->where('id', '<>', $m->id)->orderByDesc('request_date')->limit(5)->get()
                ->map(fn($h) => ['title' => optional($h->types)->title, 'date' => $this->date($h->request_date), 'status' => $h->status,
                    'status_label' => MaintenanceRequest::status()[$h->status] ?? $h->status, 'notes' => mb_substr((string) $h->notes, 0, 120)])->values(),
            'comments' => $comments->map(fn($c) => [
                'id' => $c->id, 'comment' => $c->comment, 'mine' => $c->user_id == \Auth::id(),
                'author' => optional($users->get($c->user_id))->name, 'at' => $this->dateTime($c->created_at),
            ])->values(),
        ]);
    }

    public function onTheWay($id)
    {
        $this->jobs()->findOrFail($id);
        return $this->bridge(app(MaintenanceRequestController::class)->onTheWay($id));
    }

    public function update(Request $request, $id)
    {
        $m = $this->jobs()->findOrFail($id);
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
            'amount' => 'nullable|numeric|min:0',
            'labor_hours' => 'nullable|numeric|min:0|max:9999',
            'fixed_date' => 'nullable|date',
            'after_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
            'invoice' => 'nullable|file|max:10240',
        ]);
        // the website form always sends these fields
        $request->merge([
            'amount' => $request->filled('amount') ? $request->amount : (float) $m->amount,
            'fixed_date' => $request->fixed_date ?: date('Y-m-d'),
        ]);
        return $this->bridge(app(MaintenanceRequestController::class)->actionData($request, $id), fn() => $this->summary($m->fresh()));
    }

    public function comment(Request $request, $id)
    {
        $this->jobs()->findOrFail($id);
        $request->validate(['comment' => 'required|string|max:2000']);
        return $this->bridge(app(MaintenanceRequestController::class)->comment($request, $id));
    }
}
