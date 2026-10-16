{{-- expects $statement, $month, $monthRoute (url to reload with ?month=) --}}
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0">{{ __('Statement') }}: {{ dateFormat($statement['from']) }} - {{ dateFormat($statement['to']) }}</h5>
        <div class="d-flex gap-2">
            <form method="get" action="{{ $monthRoute }}" class="d-flex gap-2">
                <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" onchange="this.form.submit()">
            </form>
            <a href="javascript:window.print()" class="btn btn-light-secondary btn-sm"><i class="ti ti-printer"></i></a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-6"><div class="border rounded p-3"><div class="text-muted small">{{ __('Rent Collected') }}</div><h5 class="mb-0">{{ priceFormat($statement['income']) }}</h5></div></div>
            <div class="col-md-3 col-6"><div class="border rounded p-3"><div class="text-muted small">{{ __('Expenses') }}</div><h5 class="mb-0">-{{ priceFormat($statement['expenseTotal']) }}</h5></div></div>
            <div class="col-md-3 col-6"><div class="border rounded p-3"><div class="text-muted small">{{ __('Management Fee') }} ({{ (float) $statement['feePercent'] }}%)</div><h5 class="mb-0">-{{ priceFormat($statement['fee']) }}</h5></div></div>
            <div class="col-md-3 col-6"><div class="border rounded p-3 bg-light-success"><div class="text-muted small">{{ __('Net for this month') }}</div><h5 class="mb-0">{{ priceFormat($statement['net']) }}</h5></div></div>
        </div>
        <div class="d-flex flex-wrap gap-4 mb-3">
            <div>{{ __('Paid out this month') }}: <strong>{{ priceFormat($statement['paidOut']) }}</strong></div>
            <div>{{ __('Balance owed to landlord (all time)') }}: <strong class="{{ $statement['balance'] < 0 ? 'text-danger' : 'text-success' }}">{{ priceFormat($statement['balance']) }}</strong></div>
        </div>

        <h6>{{ __('Rent received') }}</h6>
        <table class="table table-sm mb-4">
            <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Property / Unit') }}</th><th>{{ __('Invoice') }}</th><th>{{ __('Method') }}</th><th class="text-end">{{ __('Amount') }}</th></tr></thead>
            <tbody>
                @forelse ($statement['payments'] as $payment)
                    <tr>
                        <td>{{ dateFormat($payment->payment_date) }}</td>
                        <td>{{ optional(\App\Models\Property::find($payment->property_id))->name }} / {{ optional(\App\Models\PropertyUnit::find($payment->unit_id))->name }}</td>
                        <td>{{ invoicePrefix() . $payment->invoice_number }}</td>
                        <td>{{ $payment->payment_type }}</td>
                        <td class="text-end">{{ priceFormat($payment->amount) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center">{{ __('No rent received in this period.') }}</td></tr>
                @endforelse
            </tbody>
        </table>

        <h6>{{ __('Expenses') }}</h6>
        <table class="table table-sm mb-4">
            <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Title') }}</th><th>{{ __('Property') }}</th><th class="text-end">{{ __('Amount') }}</th></tr></thead>
            <tbody>
                @forelse ($statement['expenses'] as $expense)
                    <tr>
                        <td>{{ dateFormat($expense->date) }}</td>
                        <td>{{ $expense->title }}</td>
                        <td>{{ optional($expense->properties)->name }}</td>
                        <td class="text-end">{{ priceFormat($expense->amount) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center">{{ __('No expenses in this period.') }}</td></tr>
                @endforelse
            </tbody>
        </table>

        <h6>{{ __('Payouts') }}</h6>
        <table class="table table-sm mb-0">
            <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Method') }}</th><th>{{ __('Reference') }}</th><th class="text-end">{{ __('Amount') }}</th>@if (!empty($canEditPayouts))<th></th>@endif</tr></thead>
            <tbody>
                @forelse ($statement['payouts'] as $payout)
                    <tr>
                        <td>{{ dateFormat($payout->payout_date) }}</td>
                        <td>{{ $payout->method ?: '-' }}</td>
                        <td>{{ $payout->reference ?: '-' }}</td>
                        <td class="text-end">{{ priceFormat($payout->amount) }}</td>
                        @if (!empty($canEditPayouts))
                            <td class="text-end">
                                {!! Form::open(['method' => 'DELETE', 'route' => ['landlord.payout.destroy', $payout->id]]) !!}
                                <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#"><i data-feather="trash-2"></i></a>
                                {!! Form::close() !!}
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center">{{ __('No payouts in this period.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
