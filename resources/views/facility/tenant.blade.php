@extends('layouts.app')

@section('page-title')
    {{ __('Book a Facility') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Book a Facility') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-7">
            @forelse ($facilities as $facility)
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1">{{ $facility->name }}</h5>
                                <div class="text-muted small">{{ $facility->open_time }} - {{ $facility->close_time }} &middot; {{ $facility->slot_minutes }} {{ __('min slots') }}
                                    @if ($facility->max_bookings_per_week) &middot; {{ __('max :n per week', ['n' => $facility->max_bookings_per_week]) }} @endif
                                    @if ($facility->requires_approval) &middot; {{ __('needs approval') }} @endif
                                </div>
                                @if ($facility->description)<p class="small mt-2 mb-0">{!! nl2br(e($facility->description)) !!}</p>@endif
                            </div>
                        </div>
                        {{ Form::open(['route' => ['facility.book', $facility->id], 'method' => 'post', 'class' => 'mt-3 facility-book', 'data-slots' => route('facility.slots', $facility->id)]) }}
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small mb-0">{{ __('Date') }}</label>
                                <input type="date" name="booking_date" class="form-control form-control-sm fb-date" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d', strtotime('+60 days')) }}" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small mb-0">{{ __('Time') }}</label>
                                <div class="fb-slots d-flex flex-wrap gap-1"><span class="text-muted small">{{ __('Loading...') }}</span></div>
                                <input type="hidden" name="start_time" class="fb-start" required>
                            </div>
                            <div class="col-md-9"><input type="text" name="notes" class="form-control form-control-sm" maxlength="500" placeholder="{{ __('Note (optional)') }}"></div>
                            <div class="col-md-3"><button type="submit" class="btn btn-secondary btn-sm w-100 fb-submit" disabled>{{ __('Book') }}</button></div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            @empty
                <div class="card"><div class="card-body text-muted">{{ __('There are no facilities to book at your property.') }}</div></div>
            @endforelse
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('My bookings') }}</h5></div>
                <div class="card-body pt-0">
                    <table class="table mb-0">
                        <tbody>
                            @forelse ($myBookings as $booking)
                                <tr>
                                    <td><div class="fw-semibold">{{ optional($booking->facility)->name }}</div>
                                        <small>{{ dateFormat($booking->booking_date) }} {{ $booking->start_time }}-{{ $booking->end_time }}</small></td>
                                    <td><span class="badge bg-light-{{ \App\Models\FacilityBooking::statusColor($booking->status) }}">{{ \App\Models\FacilityBooking::statuses()[$booking->status] ?? $booking->status }}</span></td>
                                    <td class="text-end">
                                        @if (in_array($booking->status, ['pending', 'approved']) && $booking->booking_date >= date('Y-m-d'))
                                            {{ Form::open(['route' => ['facility.booking.cancel', $booking->id], 'method' => 'post']) }}
                                            <button class="btn btn-sm btn-light-danger">{{ __('Cancel') }}</button>
                                            {{ Form::close() }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="text-muted">{{ __('No bookings yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $('.facility-book').each(function() {
            var $form = $(this), $slots = $form.find('.fb-slots'), $start = $form.find('.fb-start'), $submit = $form.find('.fb-submit');
            function load() {
                $start.val(''); $submit.prop('disabled', true);
                $.get($form.data('slots'), { date: $form.find('.fb-date').val() }, function(res) {
                    $slots.empty();
                    var any = false;
                    $.each(res.slots, function(_, s) {
                        var $b = $('<button type="button" class="btn btn-sm">').text(s.start)
                            .addClass(s.available ? 'btn-light-secondary' : 'btn-light disabled text-decoration-line-through').prop('disabled', !s.available);
                        if (s.available) { any = true; $b.on('click', function() { $slots.find('.btn-secondary').removeClass('btn-secondary').addClass('btn-light-secondary'); $b.removeClass('btn-light-secondary').addClass('btn-secondary'); $start.val(s.start); $submit.prop('disabled', false); }); }
                        $slots.append($b);
                    });
                    if (!any) $slots.append('<span class="text-muted small">{{ __('No free slots on this day.') }}</span>');
                });
            }
            $form.find('.fb-date').on('change', load);
            load();
        });
    </script>
@endpush
