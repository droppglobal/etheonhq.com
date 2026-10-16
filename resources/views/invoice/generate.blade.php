@extends('layouts.app')

@section('page-title')
    {{ __('Generate Rent Invoices') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('invoice.index') }}">{{ __('Invoice') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Generate Rent') }}</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="get" action="{{ route('invoice.generate') }}" class="row g-2 align-items-end">
                <div class="col-md-3 col-6">
                    <label class="form-label">{{ __('Rent month') }}</label>
                    <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="col-md-9 col-12">
                    <p class="text-muted small mb-0">{{ __('Creates one rent invoice per tenant for the month. Tenants who already have an invoice for this month are left out, so running it twice is safe.') }}</p>
                </div>
            </form>
        </div>
    </div>

    <form method="post" action="{{ route('invoice.generate.store') }}">
        @csrf
        <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
        <div class="card table-card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0">{{ __('Tenants') }} · {{ $month->format('F Y') }}</h5>
                <div class="small text-muted"><span id="gen-count">0</span> {{ __('selected') }} · {{ __('total') }} <span id="gen-total" class="fw-semibold text-body">0</span></div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:36px"><input type="checkbox" class="form-check-input" id="gen-all"></th>
                                <th>{{ __('Tenant') }}</th>
                                <th>{{ __('Property & Unit') }}</th>
                                <th>{{ __('Rent') }}</th>
                                <th style="width:170px">{{ __('Invoice amount') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="{{ $row->existing ? 'text-muted' : '' }}">
                                    <td><input type="checkbox" class="form-check-input gen-row" name="tenants[]" value="{{ $row->tenant->id }}" {{ $row->checked ? 'checked' : '' }} {{ $row->existing ? 'disabled' : '' }}></td>
                                    <td class="fw-semibold">{{ optional($row->tenant->user)->name }}</td>
                                    <td>{{ optional($row->tenant->properties)->name }}<div class="small text-muted">{{ optional($row->unit)->name }}</div></td>
                                    <td>{{ priceFormat($row->rent) }} <span class="small text-muted">· {{ \App\Models\PropertyUnit::rentTypes()[$row->rentType] ?? $row->rentType }}</span></td>
                                    <td><input type="number" step="0.01" min="0" name="amount[{{ $row->tenant->id }}]" value="{{ $row->rent }}" class="form-control form-control-sm gen-amount" {{ $row->existing ? 'disabled' : '' }}></td>
                                    <td>
                                        @if ($row->existing)
                                            <a href="{{ route('invoice.show', \Crypt::encrypt($row->existing->id)) }}" class="badge bg-light-secondary">{{ __('Invoiced') }} {{ invoicePrefix() . $row->existing->invoice_id }}</a>
                                        @elseif ($row->rentType != 'monthly')
                                            <span class="badge bg-light-info">{{ __('Not monthly rent: check the amount') }}</span>
                                        @elseif ($row->leaseEnded)
                                            <span class="badge bg-light-warning">{{ __('Lease ended') }} {{ dateFormat($row->tenant->lease_end_date) }}</span>
                                        @else
                                            <span class="badge bg-light-success">{{ __('Ready') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No current tenants with a unit.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body row g-3 align-items-end">
                <div class="col-md-2 col-6">
                    <label class="form-label">{{ __('Due on day') }}</label>
                    <input type="number" name="due_day" min="1" max="28" value="{{ $dueDay }}" class="form-control">
                </div>
                <div class="col-md-5 col-12">
                    <label class="form-label">{{ __('Note on each invoice (optional)') }}</label>
                    <input type="text" name="notes" maxlength="500" class="form-control">
                </div>
                <div class="col-md-3 col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="notify" value="1" id="gen-notify" checked>
                        <label class="form-check-label" for="gen-notify" style="text-transform:none">{{ __('Send the "New Invoice" message with a pay link') }}</label>
                    </div>
                </div>
                <div class="col-md-2 col-12 text-md-end">
                    <button class="btn btn-secondary w-100" id="gen-submit"><i class="ti ti-file-invoice"></i> {{ __('Create invoices') }}</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('script-page')
    <script>
        (function() {
            var symbol = @json(settings()['CURRENCY_SYMBOL'] ?? '');
            function update() {
                var count = 0, total = 0;
                $('.gen-row:checked:not(:disabled)').each(function() {
                    count++;
                    total += parseFloat($(this).closest('tr').find('.gen-amount').val()) || 0;
                });
                $('#gen-count').text(count);
                $('#gen-total').text(symbol + total.toFixed(2).replace(/\.00$/, ''));
                $('#gen-submit').prop('disabled', count === 0);
            }
            $('#gen-all').on('change', function() { $('.gen-row:not(:disabled)').prop('checked', this.checked); update(); });
            $(document).on('change input', '.gen-row, .gen-amount', update);
            update();
        })();
    </script>
@endpush
