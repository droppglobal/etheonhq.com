<?php

namespace App\Support;

use App\Models\LeadViewing;
use Carbon\Carbon;

/**
 * Free viewing times on an owner's website, from the owner's viewing hours minus viewings already booked.
 */
class ViewingSlots
{
    // ['2026-10-07' => ['09:00', '09:30', ...], ...] for the days that still have a free time
    public static function free($ownerId, array $v = null)
    {
        $v = $v ?: WebsiteSite::visitors($ownerId);
        if (empty($v['viewings'])) {
            return [];
        }
        $step = max(15, min(120, (int) $v['viewing_slot']));
        $days = array_map('intval', (array) $v['viewing_days']);
        $ahead = max(1, min(60, (int) $v['viewing_ahead']));
        $perSlot = max(1, min(20, (int) $v['viewing_per_slot']));
        $earliest = now()->addHour(); // no bookings for the next hour

        $booked = LeadViewing::where('parent_id', $ownerId)->whereNotIn('status', ['cancelled', 'no_show'])
            ->whereBetween('viewing_date', [now()->toDateString(), now()->addDays($ahead)->toDateString()])
            ->get(['viewing_date', 'viewing_time'])
            ->groupBy(fn($b) => Carbon::parse($b->viewing_date)->toDateString() . ' ' . substr((string) $b->viewing_time, 0, 5))
            ->map->count();

        $out = [];
        for ($d = 0; $d <= $ahead; $d++) {
            $date = now()->startOfDay()->addDays($d);
            if (!in_array($date->dayOfWeekIso, $days, true)) {
                continue;
            }
            $times = [];
            $t = Carbon::parse($date->toDateString() . ' ' . $v['viewing_from']);
            $end = Carbon::parse($date->toDateString() . ' ' . $v['viewing_to']);
            for (; $t->copy()->addMinutes($step)->lte($end); $t->addMinutes($step)) {
                $key = $date->toDateString() . ' ' . $t->format('H:i');
                if ($t->gt($earliest) && ($booked[$key] ?? 0) < $perSlot) {
                    $times[] = $t->format('H:i');
                }
            }
            if ($times) {
                $out[$date->toDateString()] = $times;
            }
        }
        return $out;
    }

    public static function isFree($ownerId, $date, $time)
    {
        $free = self::free($ownerId);
        return in_array(substr((string) $time, 0, 5), $free[(string) $date] ?? [], true);
    }
}
