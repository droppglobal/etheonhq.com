@extends('layouts.app')

@section('page-title')
    {{ __('Lead') }}: {{ $lead->name }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lead.index') }}">{{ __('Leads') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ $lead->name }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $lead->name }}</h5>
                    <span class="badge bg-light-{{ \App\Models\Lead::statusColor($lead->status) }}">{{ \App\Models\Lead::statuses()[$lead->status] ?? $lead->status }}</span>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="ti ti-mail me-1"></i> {{ $lead->email ?: '-' }}</li>
                        <li class="mb-2"><i class="ti ti-phone me-1"></i> {{ $lead->phone ?: '-' }}</li>
                        <li class="mb-2"><i class="ti ti-building me-1"></i> {{ optional($lead->properties)->name ?? '-' }}
                            {{ !empty($lead->units) ? '/ ' . $lead->units->name : '' }}</li>
                        <li class="mb-2"><i class="ti ti-world me-1"></i> {{ \App\Models\Lead::sources()[$lead->source] ?? $lead->source }}</li>
                        <li class="mb-2"><i class="ti ti-calendar me-1"></i> {{ __('Follow-up') }}: {{ $lead->follow_up_date ? dateFormat($lead->follow_up_date) : '-' }}</li>
                        <li class="mb-2"><i class="ti ti-clock me-1"></i> {{ __('Created') }}: {{ dateFormat($lead->created_at) }}</li>
                        @if (!empty($lead->tenant_id))
                            <li class="mb-2"><i class="ti ti-user-check me-1"></i>
                                <a href="{{ route('tenant.show', \Crypt::encrypt($lead->tenant_id)) }}">{{ __('View tenant') }}</a></li>
                        @endif
                    </ul>
                    @if (!empty($lead->message))
                        <hr>
                        <h6>{{ __('Enquiry') }}</h6>
                        <p class="mb-0">{!! nl2br(e($lead->message)) !!}</p>
                    @endif
                    @if (!empty($lead->notes))
                        <hr>
                        <h6>{{ __('Internal Notes') }}</h6>
                        <p class="mb-0">{!! nl2br(e($lead->notes)) !!}</p>
                    @endif
                    @can('edit lead')
                        <hr>
                        <a href="#" class="btn btn-secondary btn-sm customModal" data-size="lg"
                            data-url="{{ route('lead.edit', $lead->id) }}" data-title="{{ __('Edit Lead') }}">
                            <i class="ti ti-edit"></i> {{ __('Edit / Change Status') }}</a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Viewings') }}</h5>
                    @can('edit lead')
                        <a href="#" class="btn btn-secondary btn-sm customModal" data-size="md"
                            data-url="{{ route('lead.viewing.create', $lead->id) }}" data-title="{{ __('Schedule Viewing') }}">
                            <i class="ti ti-calendar-plus"></i> {{ __('Schedule Viewing') }}</a>
                    @endcan
                </div>
                <div class="card-body pt-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Time') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Notes') }}</th>
                                <th class="text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lead->viewings as $viewing)
                                <tr>
                                    <td>{{ dateFormat($viewing->viewing_date) }}</td>
                                    <td>{{ $viewing->viewing_time ?: '-' }}</td>
                                    <td><span class="badge bg-light-{{ $viewing->status == 'completed' ? 'success' : ($viewing->status == 'requested' ? 'warning' : (in_array($viewing->status, ['cancelled', 'no_show']) ? 'danger' : 'info')) }}">
                                            {{ $viewingStatuses[$viewing->status] ?? $viewing->status }}</span></td>
                                    <td>{{ $viewing->notes }}</td>
                                    <td class="text-right">
                                        @can('edit lead')
                                            <div class="cart-action">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['lead.viewing.destroy', $viewing->id]]) !!}
                                                <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" href="#" data-size="md"
                                                    data-url="{{ route('lead.viewing.edit', $viewing->id) }}" data-title="{{ __('Update Viewing') }}">
                                                    <i data-feather="edit"></i></a>
                                                <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#"><i data-feather="trash-2"></i></a>
                                                {!! Form::close() !!}
                                            </div>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">{{ __('No viewings yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('Rental Applications') }}</h5></div>
                <div class="card-body pt-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Submitted') }}</th>
                                <th>{{ __('Unit') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lead->applications as $application)
                                <tr>
                                    <td>{{ dateFormat($application->created_at) }}</td>
                                    <td>{{ optional($application->units)->name ?? '-' }}</td>
                                    <td><span class="badge bg-light-{{ $application->status == 'approved' ? 'success' : ($application->status == 'rejected' ? 'danger' : 'warning') }}">
                                            {{ \App\Models\RentalApplication::statuses()[$application->status] ?? $application->status }}</span></td>
                                    <td class="text-right">
                                        @can('manage rental application')
                                            <a class="avtar avtar-xs btn-link-warning text-warning" href="{{ route('rental-application.show', $application->id) }}"><i data-feather="eye"></i></a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">{{ __('No applications yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
