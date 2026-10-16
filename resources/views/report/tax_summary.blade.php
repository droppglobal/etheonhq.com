@extends('layouts.app')

@section('page-title')
    {{ __('Tax Summary') }} {{ $year }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Tax Summary') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0">{{ $settings['company_name'] ?? '' }} &middot; {{ __('Year') }} {{ $year }}</h5>
                        @if (!empty($settings['tax_number']))
                            <small class="text-muted">{{ $settings['tax_title'] ?: __('Tax Number') }}: {{ $settings['tax_number'] }}</small>
                        @endif
                    </div>
                    <form method="get" class="d-flex gap-2">
                        {!! Form::select('year', $years, $year, ['class' => 'form-control', 'onchange' => 'this.form.submit()']) !!}
                        <button type="submit" name="export" value="csv" class="btn btn-light-secondary"><i class="ti ti-download"></i> CSV</button>
                            <button type="submit" name="export" value="pdf" class="btn btn-light-secondary"><i class="ti ti-file-download"></i> PDF</button>
                        <a href="javascript:window.print()" class="btn btn-light-secondary"><i class="ti ti-printer"></i></a>
                    </form>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4"><div class="border rounded p-3"><div class="text-muted small">{{ __('Income Received') }}</div><h4 class="mb-0 text-success">{{ priceFormat($totals['income']) }}</h4></div></div>
                        <div class="col-md-4"><div class="border rounded p-3"><div class="text-muted small">{{ __('Expenses Paid') }}</div><h4 class="mb-0 text-danger">{{ priceFormat($totals['expense']) }}</h4></div></div>
                        <div class="col-md-4"><div class="border rounded p-3 bg-light-secondary"><div class="text-muted small">{{ __('Net Income') }}</div><h4 class="mb-0">{{ priceFormat($totals['net']) }}</h4></div></div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <h6>{{ __('By month (cash received / paid)') }}</h6>
                            <table class="table table-sm">
                                <thead><tr><th>{{ __('Month') }}</th><th class="text-end">{{ __('Income') }}</th><th class="text-end">{{ __('Expenses') }}</th><th class="text-end">{{ __('Net') }}</th></tr></thead>
                                <tbody>
                                    @foreach ($months as $row)
                                        <tr><td>{{ $row['month'] }}</td><td class="text-end">{{ priceFormat($row['income']) }}</td><td class="text-end">{{ priceFormat($row['expense']) }}</td><td class="text-end fw-semibold">{{ priceFormat($row['net']) }}</td></tr>
                                    @endforeach
                                </tbody>
                                <tfoot><tr class="fw-bold"><td>{{ __('Total') }}</td><td class="text-end">{{ priceFormat($totals['income']) }}</td><td class="text-end">{{ priceFormat($totals['expense']) }}</td><td class="text-end">{{ priceFormat($totals['net']) }}</td></tr></tfoot>
                            </table>
                        </div>
                        <div class="col-lg-6">
                            <h6>{{ __('Income invoiced by type') }}</h6>
                            <table class="table table-sm mb-4">
                                <tbody>
                                    @forelse ($incomeByType as $row)
                                        <tr><td>{{ $row->title }}</td><td class="text-end">{{ priceFormat($row->total) }}</td></tr>
                                    @empty
                                        <tr><td class="text-muted">{{ __('No invoices this year.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <h6>{{ __('Expenses by type') }}</h6>
                            <table class="table table-sm">
                                <tbody>
                                    @forelse ($expenseByType as $row)
                                        <tr><td>{{ $row->title }}</td><td class="text-end">{{ priceFormat($row->total) }}</td></tr>
                                    @empty
                                        <tr><td class="text-muted">{{ __('No expenses this year.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <small class="text-muted">{{ __('Monthly figures are cash received and paid. "Income invoiced by type" counts invoices dated in this year, paid or not.') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
