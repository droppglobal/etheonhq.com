{{ Form::open(['route' => ['invoice.credit.store', $invoice->id], 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-12">
            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="type" id="cn-credit" value="credit" {{ $invoice->getInvoiceDueAmount() > 0 ? 'checked' : '' }} {{ $invoice->getInvoiceDueAmount() <= 0 ? 'disabled' : '' }}>
                <label class="btn btn-outline-secondary" for="cn-credit">{{ __('Credit note') }}</label>
                <input type="radio" class="btn-check" name="type" id="cn-refund" value="refund" {{ $invoice->getInvoiceDueAmount() <= 0 ? 'checked' : '' }} {{ $invoice->getPaidTotal() <= 0 ? 'disabled' : '' }}>
                <label class="btn btn-outline-secondary" for="cn-refund">{{ __('Refund') }}</label>
            </div>
            <small class="text-muted d-block mt-2 cn-help" data-type="credit">{{ __('Lowers what the tenant owes on this invoice, e.g. a discount or a charge you are waiving. Still due: :amount', ['amount' => priceFormat($invoice->getInvoiceDueAmount())]) }}</small>
            <small class="text-muted d-block mt-2 cn-help d-none" data-type="refund">{{ __('Records money you paid back to the tenant. It is taken off income and the invoice balance goes up by the same amount. Paid so far: :amount', ['amount' => priceFormat($invoice->getPaidTotal())]) }}</small>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
            {{ Form::number('amount', null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0.01', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
            {{ Form::date('date', date('Y-m-d'), ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-12 cn-method d-none">
            {{ Form::label('method', __('Refunded by'), ['class' => 'form-label']) }}
            {{ Form::text('method', null, ['class' => 'form-control', 'placeholder' => __('e.g. Bank transfer, Cash, Stripe refund')]) }}
        </div>
        <div class="form-group col-12">
            {{ Form::label('reason', __('Reason'), ['class' => 'form-label']) }}
            {{ Form::textarea('reason', null, ['class' => 'form-control', 'rows' => 2, 'required' => 'required']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
<script>
    (function() {
        function sync() {
            var t = $('input[name=type]:checked').val();
            $('.cn-help').addClass('d-none').filter('[data-type=' + t + ']').removeClass('d-none');
            $('.cn-method').toggleClass('d-none', t !== 'refund');
        }
        $('input[name=type]').on('change', sync);
        sync();
    })();
</script>
