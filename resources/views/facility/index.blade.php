@extends('layouts.app')

@section('page-title')
    {{ __('Facilities') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Facilities') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            @if ($pending->count())
                <div class="card border-warning">
                    <div class="card-header"><h5 class="mb-0">{{ __('Waiting for approval') }} ({{ $pending->count() }})</h5></div>
                    <div class="card-body pt-0">
                        <table class="table mb-0">
                            <tbody>
                                @foreach ($pending as $booking)
                                    <tr>
                                        <td class="fw-semibold">{{ optional($booking->facility)->name }}</td>
                                        <td>{{ dateFormat($booking->booking_date) }} {{ $booking->start_time }}-{{ $booking->end_time }}</td>
                                        <td>{{ optional(optional($booking->tenants)->user)->name }} <small class="text-muted">{{ optional(optional($booking->tenants)->units)->name }}</small></td>
                                        <td class="text-muted">{{ $booking->notes }}</td>
                                        <td class="text-end">
                                            {{ Form::open(['route' => ['facility.booking.decide', $booking->id], 'method' => 'post', 'class' => 'd-inline']) }}
                                            <button name="decision" value="approved" class="btn btn-sm btn-success">{{ __('Approve') }}</button>
                                            <button name="decision" value="rejected" class="btn btn-sm btn-light-danger">{{ __('Reject') }}</button>
                                            {{ Form::close() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="card table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Facilities') }}</h5>
                    <a href="#" class="btn btn-secondary customModal" data-size="lg" data-url="{{ route('facility.create') }}" data-title="{{ __('Add Facility') }}">
                        <i class="ti ti-circle-plus align-text-bottom"></i> {{ __('Add Facility') }}</a>
                </div>
                <div class="card-body pt-0">
                    <table class="table table-hover">
                        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Property') }}</th><th>{{ __('Hours') }}</th><th>{{ __('Slot') }}</th><th>{{ __('Weekly limit') }}</th><th>{{ __('Approval') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Action') }}</th></tr></thead>
                        <tbody>
                            @forelse ($facilities as $facility)
                                <tr>
                                    <td class="fw-semibold">{{ $facility->name }}</td>
                                    <td>{{ optional($facility->properties)->name ?? __('All properties') }}</td>
                                    <td>{{ $facility->open_time }} - {{ $facility->close_time }}</td>
                                    <td>{{ $facility->slot_minutes }} {{ __('min') }}</td>
                                    <td>{{ $facility->max_bookings_per_week ?: __('No limit') }}</td>
                                    <td>{{ $facility->requires_approval ? __('Required') : __('Instant') }}</td>
                                    <td><span class="badge bg-light-{{ $facility->is_active ? 'success' : 'secondary' }}">{{ $facility->is_active ? __('Open') : __('Closed') }}</span></td>
                                    <td class="text-end">
                                        <div class="cart-action">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['facility.destroy', $facility->id]]) !!}
                                            <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" href="#" data-size="lg" data-url="{{ route('facility.edit', $facility->id) }}" data-title="{{ __('Edit Facility') }}"><i data-feather="edit"></i></a>
                                            <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#"><i data-feather="trash-2"></i></a>
                                            {!! Form::close() !!}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">{{ __('No facilities yet. Add the gym, party hall, guest room or any shared space tenants can book.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('Upcoming bookings') }}</h5></div>
                <div class="card-body pt-0">
                    <table class="table mb-0">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Time') }}</th><th>{{ __('Facility') }}</th><th>{{ __('Tenant') }}</th><th class="text-end"></th></tr></thead>
                        <tbody>
                            @forelse ($upcoming as $booking)
                                <tr>
                                    <td>{{ dateFormat($booking->booking_date) }}</td>
                                    <td>{{ $booking->start_time }} - {{ $booking->end_time }}</td>
                                    <td>{{ optional($booking->facility)->name }}</td>
                                    <td>{{ optional(optional($booking->tenants)->user)->name }} <small class="text-muted">{{ optional(optional($booking->tenants)->units)->name }}</small></td>
                                    <td class="text-end">
                                        {{ Form::open(['route' => ['facility.booking.cancel', $booking->id], 'method' => 'post', 'onsubmit' => "return confirm('" . __('Cancel this booking?') . "')"]) }}
                                        <button class="btn btn-sm btn-light-danger">{{ __('Cancel') }}</button>
                                        {{ Form::close() }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">{{ __('No upcoming bookings.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
