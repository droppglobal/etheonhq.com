@extends('layouts.app')

@section('page-title')
    {{ __('Rental Applications') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Rental Applications') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('rental-application.index') }}" class="btn btn-sm {{ empty(request('status')) ? 'btn-secondary' : 'btn-light-secondary' }}">{{ __('All') }}</a>
                        @foreach ($statuses as $key => $label)
                            <a href="{{ route('rental-application.index', ['status' => $key]) }}"
                                class="btn btn-sm {{ request('status') == $key ? 'btn-secondary' : 'btn-light-secondary' }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Applicant') }}</th>
                                    <th>{{ __('Property') }}</th>
                                    <th>{{ __('Unit') }}</th>
                                    <th>{{ __('Monthly Income') }}</th>
                                    <th>{{ __('Move-in') }}</th>
                                    <th>{{ __('Submitted') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($applications as $application)
                                    <tr>
                                        <td><a href="{{ route('rental-application.show', $application->id) }}" class="fw-semibold">{{ $application->full_name }}</a>
                                            <br><small class="text-muted">{{ $application->email }}</small></td>
                                        <td>{{ optional($application->properties)->name ?? '-' }}</td>
                                        <td>{{ optional($application->units)->name ?? '-' }}</td>
                                        <td>{{ priceFormat($application->monthly_income) }}</td>
                                        <td>{{ $application->move_in_date ? dateFormat($application->move_in_date) : '-' }}</td>
                                        <td>{{ dateFormat($application->created_at) }}</td>
                                        <td><span class="badge bg-light-{{ $application->status == 'approved' ? 'success' : ($application->status == 'rejected' ? 'danger' : 'warning') }}">
                                                {{ $statuses[$application->status] ?? $application->status }}</span></td>
                                        <td class="text-right">
                                            <a class="avtar avtar-xs btn-link-warning text-warning" href="{{ route('rental-application.show', $application->id) }}"
                                                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Review') }}"><i data-feather="eye"></i></a>
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
