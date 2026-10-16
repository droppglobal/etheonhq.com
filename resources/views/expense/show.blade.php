<div class="modal-body">
    <div class="product-card">
        <div class="row">

            <div class="col-6">
                <p class="mb-1 mt-2">
                    <b>{{ __('Expense Title') }} :</b>
                    {{ $expense->title }}
                </p>
            </div>
            <div class="col-6">
                <p class="mb-1 mt-2">
                    <b>{{ __('Expense Number') }} :</b>
                    {{ expensePrefix() . $expense->expense_id }}
                </p>
            </div>
            <div class="col-6">
                <p class="mb-1 mt-2">
                    <b>{{ __('Expense Type') }} :</b>
                    {{ !empty($expense->types) ? $expense->types->title : '-' }}
                </p>
            </div>
            <div class="col-6">
                <p class="mb-1 mt-2">
                    <b>{{ __('Property') }} :</b>
                    {{ !empty($expense->properties) ? $expense->properties->name : '-' }}
                </p>
            </div>
            <div class="col-6">
                <p class="mb-1 mt-2">
                    <b>{{ __('Unit') }} :</b>
                    {{ !empty($expense->units) ? $expense->units->name : '-' }}
                </p>
            </div>
            <div class="col-6">
                <p class="mb-1 mt-2">
                    <b>{{ __('Date') }} :</b>
                    {{ dateFormat($expense->date) }}
                </p>
            </div>
            <div class="col-6">
                <p class="mb-1 mt-2">
                    <b>{{ __('Amount') }} :</b>
                    {{ priceFormat($expense->amount) }}
                </p>
            </div>
            <div class="col-6">
                <p class="mb-1 mt-2">
                    <b>{{ __('Receipt') }} :</b>

                    @if (!empty($expense->receipt))
                        <a href="{{ !empty($expense->receipt) ? fetch_file($expense->receipt,'upload/receipt/') : '#' }}"
                            download="download"><i data-feather="download"></i></a>
                    @else
                        -
                    @endif

                </p>
            </div>
            <div class="col-12">
                <p class="mb-1 mt-2">
                    <b>{{ __('Notes') }} :</b>
                    {{ $expense->notes }}
                </p>
            </div>
        </div>
    </div>
</div>
