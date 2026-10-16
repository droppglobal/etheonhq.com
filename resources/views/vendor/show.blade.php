@extends('layouts.app')

@section('page-title')
    {{ __('Vendor') }}: {{ $vendor->name }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('vendor.index') }}">{{ __('Vendors') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ $vendor->name }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ $vendor->name }}</h5>
                    @if ($vendor->company)<small class="text-muted">{{ $vendor->company }}</small>@endif</div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="ti ti-tool me-1"></i> {{ $vendor->service ?: '-' }}</li>
                        <li class="mb-2"><i class="ti ti-phone me-1"></i> {{ $vendor->phone ?: '-' }}</li>
                        <li class="mb-2"><i class="ti ti-mail me-1"></i> {{ $vendor->email ?: '-' }}</li>
                        <li class="mb-2"><i class="ti ti-map-pin me-1"></i> {{ $vendor->address ?: '-' }}</li>
                    </ul>
                    @if ($vendor->notes)<hr>{!! nl2br(e($vendor->notes)) !!}@endif
                    <hr>
                    <div class="d-flex justify-content-between"><span class="text-muted">{{ __('Total Spent') }}</span><strong>{{ priceFormat($expenses->sum('amount')) }}</strong></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">{{ __('Jobs') }}</span><strong>{{ $jobs->count() }}</strong></div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('Expenses / Invoices') }}</h5></div>
                <div class="card-body pt-0">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Title') }}</th><th>{{ __('Property') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Receipt') }}</th></tr></thead>
                        <tbody>
                            @forelse ($expenses as $expense)
                                <tr>
                                    <td>{{ dateFormat($expense->date) }}</td>
                                    <td>{{ $expense->title }}</td>
                                    <td>{{ optional($expense->properties)->name ?? '-' }}</td>
                                    <td>{{ priceFormat($expense->amount) }}</td>
                                    <td>@if ($expense->receipt)<a href="{{ fetch_file($expense->receipt, 'upload/receipt/') }}" target="_blank"><i class="ti ti-download"></i></a>@else - @endif</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">{{ __('No expenses linked to this vendor yet. Pick the vendor when adding an expense.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('Maintenance Jobs') }}</h5></div>
                <div class="card-body pt-0">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Property') }}</th><th>{{ __('Issue') }}</th><th>{{ __('Status') }}</th><th>{{ __('Cost') }}</th></tr></thead>
                        <tbody>
                            @forelse ($jobs as $job)
                                <tr>
                                    <td>{{ dateFormat($job->request_date) }}</td>
                                    <td>{{ optional($job->properties)->name }} / {{ optional($job->units)->name }}</td>
                                    <td><a href="{{ route('maintenance-request.show', \Crypt::encrypt($job->id)) }}">{{ optional($job->types)->title ?? '-' }}</a></td>
                                    <td>{{ \App\Models\MaintenanceRequest::status()[$job->status] ?? $job->status }}</td>
                                    <td>{{ priceFormat($job->amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">{{ __('No jobs yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
