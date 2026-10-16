{{ Form::open(['route' => 'inspection.store', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('tenant_id', __('Tenant'), ['class' => 'form-label']) }}
            {{ Form::select('tenant_id', $tenants, $selectedTenant, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Select Tenant')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
            {{ Form::select('type', $types, null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('inspection_date', __('Date'), ['class' => 'form-label']) }}
            {{ Form::date('inspection_date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="col-12"><small class="text-muted">{{ __('A room-by-room checklist is created for you. A move-out inspection reuses the rooms from the move-in inspection so they can be compared.') }}</small></div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
