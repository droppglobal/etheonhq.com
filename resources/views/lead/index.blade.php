@extends('layouts.app')

@section('page-title')
    {{ __('Leads') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Leads') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('lead.index') }}"
                            class="btn btn-sm {{ empty(request('status')) ? 'btn-secondary' : 'btn-light-secondary' }}">
                            {{ __('All') }} <span class="badge bg-white text-dark ms-1">{{ $counts->sum() }}</span></a>
                        @foreach ($statuses as $key => $label)
                            <a href="{{ route('lead.index', ['status' => $key]) }}"
                                class="btn btn-sm {{ request('status') == $key ? 'btn-' . \App\Models\Lead::statusColor($key) : 'btn-light-' . \App\Models\Lead::statusColor($key) }}">
                                {{ $label }} <span class="badge bg-white text-dark ms-1">{{ $counts[$key] ?? 0 }}</span></a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card table-card">
                <div class="card-header">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-4 col-md-6">
                            <form method="get" action="{{ route('lead.index') }}" class="d-flex gap-2">
                                <input type="hidden" name="status" value="{{ request('status') }}">
                                {!! Form::select('property', $properties, request('property'), ['class' => 'form-control', 'onchange' => 'this.form.submit()']) !!}
                            </form>
                        </div>
                        @can('create lead')
                            <div class="col-lg-auto col-md-12 ms-lg-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="{{ route('lead.create') }}" data-title="{{ __('Add Lead') }}">
                                    <i class="ti ti-circle-plus align-text-bottom"></i> {{ __('Add Lead') }}</a>
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th>{{ __('Property') }}</th>
                                    <th>{{ __('Source') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Follow-up') }}</th>
                                    <th>{{ __('Created') }}</th>
                                    <th class="text-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leads as $lead)
                                    <tr>
                                        <td><a href="{{ route('lead.show', $lead->id) }}" class="fw-semibold">{{ $lead->name }}</a></td>
                                        <td>{{ $lead->email }}<br><small class="text-muted">{{ $lead->phone }}</small></td>
                                        <td>{{ optional($lead->properties)->name ?? '-' }}
                                            @if (!empty($lead->units))
                                                <br><small class="text-muted">{{ $lead->units->name }}</small>
                                            @endif
                                        </td>
                                        <td>{{ \App\Models\Lead::sources()[$lead->source] ?? $lead->source }}</td>
                                        <td><span class="badge bg-light-{{ \App\Models\Lead::statusColor($lead->status) }}">{{ $statuses[$lead->status] ?? $lead->status }}</span></td>
                                        <td>
                                            @if (!empty($lead->follow_up_date))
                                                <span class="{{ \Carbon\Carbon::parse($lead->follow_up_date)->lte(\Carbon\Carbon::today()) && in_array($lead->status, \App\Models\Lead::openStatuses()) ? 'text-danger fw-semibold' : '' }}">
                                                    {{ dateFormat($lead->follow_up_date) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ dateFormat($lead->created_at) }}</td>
                                        <td class="text-right">
                                            <div class="cart-action">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['lead.destroy', $lead->id]]) !!}
                                                <a class="avtar avtar-xs btn-link-warning text-warning" href="{{ route('lead.show', $lead->id) }}"
                                                    data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}"><i data-feather="eye"></i></a>
                                                @can('edit lead')
                                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" href="#" data-size="lg"
                                                        data-url="{{ route('lead.edit', $lead->id) }}" data-title="{{ __('Edit Lead') }}"
                                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"><i data-feather="edit"></i></a>
                                                @endcan
                                                @can('delete lead')
                                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#"
                                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"><i data-feather="trash-2"></i></a>
                                                @endcan
                                                {!! Form::close() !!}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
