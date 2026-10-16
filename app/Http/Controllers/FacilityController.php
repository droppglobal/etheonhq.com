<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Bookable facilities. Owners set them up and approve bookings; tenants book slots in their own property.
 */
class FacilityController extends Controller
{
    private function tenant()
    {
        return \Auth::user()->type == 'tenant' ? \Auth::user()->tenants : null;
    }

    private function findOwn($id)
    {
        return Facility::where('parent_id', parentId())->where('id', $id)->first();
    }

    // a tenant can book active facilities of their own property (or ones open to every property)
    private function findBookable($id, $tenant)
    {
        return Facility::where('parent_id', parentId())->where('id', $id)->where('is_active', 1)
            ->whereIn('property_id', [0, $tenant->property])->first();
    }

    private function rules()
    {
        return [
            'name' => 'required|max:191',
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i|after:open_time',
            'slot_minutes' => 'required|integer|min:15|max:1440',
            'max_bookings_per_week' => 'nullable|integer|min:0|max:100',
        ];
    }

    public function index()
    {
        if ($tenant = $this->tenant()) {
            if (!\Auth::user()->can('book facility')) {
                return redirect()->back()->with('error', __('Permission Denied!'));
            }
            $facilities = Facility::where('parent_id', parentId())->where('is_active', 1)->whereIn('property_id', [0, $tenant->property])->get();
            $myBookings = FacilityBooking::where('parent_id', parentId())->where('tenant_id', $tenant->id)
                ->whereDate('booking_date', '>=', Carbon::today()->subDays(30))->orderBy('booking_date', 'desc')->orderBy('start_time')->get();
            return view('facility.tenant', compact('facilities', 'myBookings'));
        }
        if (!\Auth::user()->can('manage facility')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $facilities = Facility::where('parent_id', parentId())->orderBy('name')->get();
        $pending = FacilityBooking::where('parent_id', parentId())->where('status', 'pending')->whereDate('booking_date', '>=', Carbon::today())->orderBy('booking_date')->get();
        $upcoming = FacilityBooking::where('parent_id', parentId())->where('status', 'approved')->whereDate('booking_date', '>=', Carbon::today())->orderBy('booking_date')->orderBy('start_time')->limit(50)->get();
        return view('facility.index', compact('facilities', 'pending', 'upcoming'));
    }

    public function create()
    {
        if (!\Auth::user()->can('manage facility')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $facility = null;
        $properties = Property::where('parent_id', parentId())->pluck('name', 'id')->prepend(__('All properties'), 0);
        return view('facility.form', compact('facility', 'properties'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('manage facility')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        if (!empty($request->property_id) && !Property::where('parent_id', parentId())->where('id', $request->property_id)->exists()) {
            return redirect()->back()->with('error', __('Property not found.'));
        }
        Facility::create($request->only('name', 'description', 'open_time', 'close_time', 'slot_minutes') + [
            'property_id' => (int) $request->property_id,
            'max_bookings_per_week' => (int) $request->max_bookings_per_week,
            'requires_approval' => $request->has('requires_approval') ? 1 : 0,
            'is_active' => 1,
            'parent_id' => parentId(),
        ]);
        return redirect()->back()->with('success', __('Facility successfully created.'));
    }

    public function edit($id)
    {
        $facility = $this->findOwn($id);
        if (!\Auth::user()->can('manage facility') || !$facility) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $properties = Property::where('parent_id', parentId())->pluck('name', 'id')->prepend(__('All properties'), 0);
        return view('facility.form', compact('facility', 'properties'));
    }

    public function update(Request $request, $id)
    {
        $facility = $this->findOwn($id);
        if (!\Auth::user()->can('manage facility') || !$facility) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        if (!empty($request->property_id) && !Property::where('parent_id', parentId())->where('id', $request->property_id)->exists()) {
            return redirect()->back()->with('error', __('Property not found.'));
        }
        $facility->fill($request->only('name', 'description', 'open_time', 'close_time', 'slot_minutes') + [
            'property_id' => (int) $request->property_id,
            'max_bookings_per_week' => (int) $request->max_bookings_per_week,
            'requires_approval' => $request->has('requires_approval') ? 1 : 0,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ])->save();
        return redirect()->back()->with('success', __('Facility successfully updated.'));
    }

    public function destroy($id)
    {
        $facility = $this->findOwn($id);
        if (!\Auth::user()->can('manage facility') || !$facility) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        FacilityBooking::where('facility_id', $facility->id)->delete();
        $facility->delete();
        return redirect()->back()->with('success', __('Facility successfully deleted.'));
    }

    // ---- tenant booking ----

    // free / taken slots for a date (JSON for the booking form)
    public function slots(Request $request, $id)
    {
        $tenant = $this->tenant();
        $facility = $tenant ? $this->findBookable($id, $tenant) : null;
        if (!$facility || !\Auth::user()->can('book facility')) {
            return response()->json(['error' => __('Permission Denied!')], 403);
        }
        $date = $request->date && strtotime($request->date) ? date('Y-m-d', strtotime($request->date)) : Carbon::today()->toDateString();
        $taken = $facility->takenSlots($date);
        $now = Carbon::now();
        $slots = array_map(function ($slot) use ($taken, $date, $now) {
            $past = Carbon::parse($date . ' ' . $slot[0])->lt($now);
            return ['start' => $slot[0], 'end' => $slot[1], 'available' => !$past && !in_array($slot[0], $taken)];
        }, $facility->slots());
        return response()->json(['date' => $date, 'slots' => $slots]);
    }

    public function book(Request $request, $id)
    {
        $tenant = $this->tenant();
        $facility = $tenant ? $this->findBookable($id, $tenant) : null;
        if (!$facility || !\Auth::user()->can('book facility')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if (!empty($tenant->exit_date)) {
            return redirect()->back()->with('error', __('Only current tenants can book facilities.'));
        }
        $validator = \Validator::make($request->all(), [
            'booking_date' => 'required|date|after_or_equal:today|before_or_equal:' . Carbon::today()->addDays(60)->toDateString(),
            'start_time' => 'required|date_format:H:i',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $slot = collect($facility->slots())->first(function ($s) use ($request) {
            return $s[0] === $request->start_time;
        });
        if (!$slot || Carbon::parse($request->booking_date . ' ' . $slot[0])->lt(Carbon::now())) {
            return redirect()->back()->with('error', __('Please pick one of the available time slots.'));
        }

        if ($facility->max_bookings_per_week > 0) {
            $date = Carbon::parse($request->booking_date);
            $count = FacilityBooking::where('facility_id', $facility->id)->where('tenant_id', $tenant->id)
                ->whereIn('status', ['pending', 'approved'])
                ->whereBetween('booking_date', [$date->copy()->startOfWeek()->toDateString(), $date->copy()->endOfWeek()->toDateString()])->count();
            if ($count >= $facility->max_bookings_per_week) {
                return redirect()->back()->with('error', __('You can book this facility :n time(s) per week.', ['n' => $facility->max_bookings_per_week]));
            }
        }

        // lock the facility row so two tenants clicking the same slot at once can't both get it
        $booked = \DB::transaction(function () use ($facility, $request, $slot, $tenant) {
            Facility::where('id', $facility->id)->lockForUpdate()->first();
            if (in_array($slot[0], $facility->takenSlots($request->booking_date))) {
                return false;
            }
            FacilityBooking::create([
                'facility_id' => $facility->id,
                'tenant_id' => $tenant->id,
                'booking_date' => $request->booking_date,
                'start_time' => $slot[0],
                'end_time' => $slot[1],
                'status' => $facility->requires_approval ? 'pending' : 'approved',
                'notes' => mb_substr((string) $request->notes, 0, 500),
                'parent_id' => parentId(),
            ]);
            return true;
        });
        if (!$booked) {
            return redirect()->back()->with('error', __('Sorry, that slot was just taken. Please pick another.'));
        }

        return redirect()->back()->with('success', $facility->requires_approval
            ? __('Booking requested. You will see it as approved once the office confirms.')
            : __('Booked! :facility on :date at :time.', ['facility' => $facility->name, 'date' => dateFormat($request->booking_date), 'time' => $slot[0]]));
    }

    public function cancel($bookingId)
    {
        $tenant = $this->tenant();
        $query = FacilityBooking::where('parent_id', parentId())->where('id', $bookingId);
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        } elseif (!\Auth::user()->can('manage facility')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $booking = $query->first();
        if (!$booking || !in_array($booking->status, ['pending', 'approved'])) {
            return redirect()->back()->with('error', __('This booking cannot be cancelled.'));
        }
        $booking->status = 'cancelled';
        $booking->save();
        return redirect()->back()->with('success', __('Booking cancelled.'));
    }

    public function decide(Request $request, $bookingId)
    {
        if (!\Auth::user()->can('manage facility')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $booking = FacilityBooking::where('parent_id', parentId())->where('id', $bookingId)->where('status', 'pending')->first();
        if (!$booking || !in_array($request->decision, ['approved', 'rejected'])) {
            return redirect()->back()->with('error', __('This booking cannot be updated.'));
        }
        $booking->status = $request->decision;
        $booking->save();
        return redirect()->back()->with('success', $request->decision == 'approved' ? __('Booking approved.') : __('Booking rejected.'));
    }
}
