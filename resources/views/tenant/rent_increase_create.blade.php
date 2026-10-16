{{ Form::open(['route' => ['tenant.rent-increase.store', $tenant->id], 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            <label class="form-label">{{ __('Current rent') }}</label>
            <input type="text" class="form-control" value="{{ priceFormat($unit->rent) }}" disabled>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('new_rent', __('New rent'), ['class' => 'form-label']) }}
            {{ Form::number('new_rent', null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0.01', 'required' => 'required', 'id' => 'ri-new']) }}
            <small class="text-muted" id="ri-pct"></small>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('effective_date', __('Effective from'), ['class' => 'form-label']) }}
            {{ Form::date('effective_date', now()->addMonths(2)->startOfMonth()->toDateString(), ['class' => 'form-control', 'min' => now()->addDay()->toDateString(), 'required' => 'required']) }}
        </div>
        <div class="form-group col-12">
            {{ Form::label('notes', __('Message to the tenant (optional)'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('e.g. reason for the change')]) }}
        </div>
        <div class="form-group col-12 mb-0">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="notify" value="1" id="ri-notify" checked>
                <label class="form-check-label" for="ri-notify" style="text-transform:none">{{ __('Send the notice to the tenant now (email, SMS and app)') }}</label>
            </div>
            <small class="text-muted">{{ __('On the effective date the unit rent changes and new recurring invoices use the new amount. Invoices already issued are not changed.') }}</small>
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Schedule'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
<script>
    $('#ri-new').on('input', function() {
        var old = {{ (float) $unit->rent }}, val = parseFloat(this.value);
        $('#ri-pct').text(old > 0 && val > 0 ? ((val - old) / old * 100 > 0 ? '+' : '') + ((val - old) / old * 100).toFixed(1) + '%' : '');
    });
</script>
