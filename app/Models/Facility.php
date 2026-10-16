<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = ['property_id', 'name', 'description', 'open_time', 'close_time', 'slot_minutes', 'max_bookings_per_week', 'requires_approval', 'is_active', 'parent_id'];

    public function properties()
    {
        return $this->hasOne(Property::class, 'id', 'property_id');
    }

    public function bookings()
    {
        return $this->hasMany(FacilityBooking::class, 'facility_id', 'id');
    }

    // All start times for a day, e.g. 06:00, 07:00 ... last slot ends by close time
    public function slots()
    {
        $slots = [];
        $minutes = max(15, (int) $this->slot_minutes);
        $time = Carbon::createFromFormat('H:i', substr($this->open_time, 0, 5));
        $close = Carbon::createFromFormat('H:i', substr($this->close_time, 0, 5));
        while ($time->copy()->addMinutes($minutes)->lte($close)) {
            $slots[] = [$time->format('H:i'), $time->copy()->addMinutes($minutes)->format('H:i')];
            $time->addMinutes($minutes);
        }
        return $slots;
    }

    // Bookings that block a slot (pending ones too, so two people can't request the same slot)
    public function takenSlots($date)
    {
        return FacilityBooking::where('facility_id', $this->id)->where('booking_date', $date)
            ->whereIn('status', ['pending', 'approved'])->pluck('start_time')->map(function ($t) {
                return substr($t, 0, 5);
            })->all();
    }
}
