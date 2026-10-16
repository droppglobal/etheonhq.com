{{ Form::open(['route' => ['tenant.deposit.store', $tenant->id], 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12 mb-3">
            <div class="alert alert-light-secondary mb-0">
                {{ __('Deposit held') }}: <strong>{{ priceFormat($balance) }}</strong>
                @if ($unitDeposit > 0)
                    <span class="ms-2 text-muted">({{ __('Unit deposit') }}: {{ priceFormat($unitDeposit) }})</span>
                @endif
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
            {{ Form::select('type', $types, $balance > 0 ? 'deduction' : 'received', ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
            {{ Form::date('date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
            {{ Form::number('amount', $balance > 0 ? null : ($unitDeposit > 0 ? $unitDeposit : null), ['class' => 'form-control', 'step' => '0.01', 'min' => '0.01', 'required' => 'required', 'placeholder' => __('Enter amount')]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => __('e.g. Wall repainting, cleaning')]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Add'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
