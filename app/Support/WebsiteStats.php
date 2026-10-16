<?php

namespace App\Support;

use App\Models\Lead;
use App\Models\LeadViewing;
use App\Models\Property;
use App\Models\RentalApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Property page views on the owner's website and what they led to (enquiries, viewings, applications).
 */
class WebsiteStats
{
    // one view per visitor session and day; bots and the owner's own team are not counted
    public static function countView(Property $property)
    {
        $agent = (string) request()->userAgent();
        if ($agent === '' || preg_match('/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|preview|headless|lighthouse|curl|wget|python/i', $agent)) {
            return;
        }
        if (Auth::check() && (Auth::id() === $property->parent_id || Auth::user()->parent_id === $property->parent_id)) {
            return;
        }
        $key = 'viewed_' . $property->id . '_' . now()->toDateString();
        if (session()->has($key)) {
            return;
        }
        session()->put($key, 1);
        DB::statement('INSERT INTO property_views (parent_id, property_id, day, views) VALUES (?, ?, ?, 1) ON DUPLICATE KEY UPDATE views = views + 1',
            [$property->parent_id, $property->id, now()->toDateString()]);
    }

    public static function periods()
    {
        return [7 => __('Last 7 days'), 30 => __('Last 30 days'), 90 => __('Last 90 days'), 365 => __('Last 12 months')];
    }

    // per property: views, enquiries, viewings, applications in the last $days days
    public static function report($ownerId, $days)
    {
        $from = now()->subDays($days - 1)->startOfDay();
        $count = fn($query, $column = 'property_id') => $query->where('parent_id', $ownerId)->where('created_at', '>=', $from)
            ->selectRaw("$column as pid, count(*) as total")->groupBy($column)->pluck('total', 'pid');

        $views = DB::table('property_views')->where('parent_id', $ownerId)->where('day', '>=', $from->toDateString())
            ->selectRaw('property_id as pid, sum(views) as total')->groupBy('property_id')->pluck('total', 'pid');
        $enquiries = $count(Lead::where('source', 'website'));
        $viewings = $count(LeadViewing::query());
        $applications = $count(RentalApplication::query());

        $rows = Property::where('parent_id', $ownerId)->orderBy('name')->get(['id', 'name', 'is_active'])->map(function ($p) use ($views, $enquiries, $viewings, $applications) {
            $v = (int) ($views[$p->id] ?? 0);
            $e = (int) ($enquiries[$p->id] ?? 0);
            return [
                'property' => $p,
                'views' => $v,
                'enquiries' => $e,
                'viewings' => (int) ($viewings[$p->id] ?? 0),
                'applications' => (int) ($applications[$p->id] ?? 0),
                'rate' => $v ? round($e / $v * 100, 1) : null,
            ];
        })->sortByDesc('views')->values();

        $daily = DB::table('property_views')->where('parent_id', $ownerId)->where('day', '>=', $from->toDateString())
            ->selectRaw('day, sum(views) as total')->groupBy('day')->pluck('total', 'day');
        $chart = [];
        for ($d = $from->copy(); $d->lte(now()); $d->addDay()) {
            $chart[$d->toDateString()] = (int) ($daily[$d->toDateString()] ?? 0);
        }

        return [
            'rows' => $rows,
            'chart' => $chart,
            'totals' => [
                'views' => $rows->sum('views'),
                'enquiries' => $rows->sum('enquiries') + (int) ($enquiries[0] ?? 0), // [0] = alerts and general enquiries
                'viewings' => $rows->sum('viewings'),
                'applications' => $rows->sum('applications'),
            ],
        ];
    }
}
