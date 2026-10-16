@extends('layouts.app')

@section('page-title')
    {{ __('Preventive Maintenance') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Preventive Maintenance') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ __('Preventive Maintenance') }}</h5>
                        <small class="text-muted">{{ __('On the due date a maintenance request is created automatically and assigned.') }}</small>
                    </div>
                    @can('create maintenance schedule')
                        <a href="#" class="btn btn-secondary customModal" data-size="lg" data-url="{{ route('maintenance-schedule.create') }}"
                            data-title="{{ __('Add Schedule') }}"><i class="ti ti-circle-plus align-text-bottom"></i> {{ __('Add Schedule') }}</a>
                    @endcan
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Task') }}</th>
                                    <th>{{ __('Property') }}</th>
                                    <th>{{ __('Repeats') }}</th>
                                    <th>{{ __('Next Due') }}</th>
                                    <th>{{ __('Last Created') }}</th>
                                    <th>{{ __('Assigned To') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($schedules as $schedule)
                                    <tr>
                                        <td class="fw-semibold">{{ $schedule->title }}<br><small class="text-muted">{{ optional($schedule->types)->title }}</small></td>
                                        <td>{{ optional($schedule->properties)->name }}{{ !empty($schedule->units) ? ' / ' . $schedule->units->name : '' }}</td>
                                        <td>{{ \App\Models\MaintenanceSchedule::frequencies()[$schedule->frequency] ?? $schedule->frequency }}</td>
                                        <td>{{ dateFormat($schedule->next_date) }}</td>
                                        <td>{{ $schedule->last_run_date ? dateFormat($schedule->last_run_date) : '-' }}</td>
                                        <td>{{ optional($schedule->maintainer)->name ?? '-' }}{{ !empty($schedule->vendor) ? ' / ' . $schedule->vendor->name : '' }}</td>
                                        <td><span class="badge bg-light-{{ $schedule->is_active ? 'success' : 'secondary' }}">{{ $schedule->is_active ? __('Active') : __('Paused') }}</span></td>
                                        <td class="text-right">
                                            <div class="cart-action">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['maintenance-schedule.destroy', $schedule->id]]) !!}
                                                @can('edit maintenance schedule')
                                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" href="#" data-size="lg"
                                                        data-url="{{ route('maintenance-schedule.edit', $schedule->id) }}" data-title="{{ __('Edit Schedule') }}"><i data-feather="edit"></i></a>
                                                @endcan
                                                @can('delete maintenance schedule')
                                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#"><i data-feather="trash-2"></i></a>
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
