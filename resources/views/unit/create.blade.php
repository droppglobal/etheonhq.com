{{ Form::open(['route' => ['unit.store', $property_id], 'method' => 'post']) }}
<div class="modal-body">

    <div class="row">
        <div class="form-group  col-md-12">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter unit name')]) }}
        </div>
        <div class="form-group  col-md-4">
            {{ Form::label('bedroom', __('Bedroom'), ['class' => 'form-label']) }}
            {{ Form::number('bedroom', null, ['class' => 'form-control', 'placeholder' => __('Enter number of bedroom')]) }}
        </div>
        <div class="form-group  col-md-4">
            {{ Form::label('kitchen', __('Kitchen'), ['class' => 'form-label']) }}
            {{ Form::number('kitchen', null, ['class' => 'form-control', 'placeholder' => __('Enter number of kitchen')]) }}
        </div>
        <div class="form-group  col-md-4">
            {{ Form::label('baths', __('Bath'), ['class' => 'form-label']) }}
            {{ Form::number('baths', null, ['class' => 'form-control', 'placeholder' => __('Enter number of bath')]) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('rent', __('Rent'), ['class' => 'form-label']) }}
            {{ Form::number('rent', null, ['class' => 'form-control', 'placeholder' => __('Enter unit rent')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('rent_type', __('Rent Type'), ['class' => 'form-label']) }}
            {{ Form::select('rent_type', $rentTypes, null, ['class' => 'form-control hidesearch', 'id' => 'rent_type']) }}
        </div>
        <div class="form-group  col-md-12 rent_type monthly ">
            {{ Form::label('rent_duration', __('Rent Duration'), ['class' => 'form-label']) }}
            {{ Form::number('rent_duration', null, ['class' => 'form-control', 'placeholder' => __('Enter day of month between 1 to 30')]) }}
        </div>
        <div class="form-group  col-md-12 rent_type yearly d-none">
            {{ Form::label('rent_duration', __('Rent Duration'), ['class' => 'form-label']) }}
            {{ Form::number('rent_duration', null, ['class' => 'form-control', 'placeholder' => __('Enter month of year between 1 to 12'), 'disabled']) }}
        </div>
        <div class="form-group  col-md-4 rent_type custom d-none">
            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
            {{ Form::date('start_date', null, ['class' => 'form-control', 'disabled']) }}
        </div>
        <div class="form-group  col-md-4 rent_type custom d-none">
            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
            {{ Form::date('end_date', null, ['class' => 'form-control', 'disabled']) }}
        </div>
        <div class="form-group  col-md-4 rent_type custom d-none">
            {{ Form::label('payment_due_date', __('Payment Due Date'), ['class' => 'form-label']) }}
            {{ Form::date('payment_due_date', null, ['class' => 'form-control', 'disabled']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('deposit_type', __('Deposit Type'), ['class' => 'form-label']) }}
            {{ Form::select('deposit_type', $types, null, ['class' => 'form-control hidesearch']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('deposit_amount', __('Deposit Amount'), ['class' => 'form-label']) }}
            {{ Form::number('deposit_amount', null, ['class' => 'form-control', 'placeholder' => __('Enter deposit amount')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('late_fee_type', __('Late Fee Type'), ['class' => 'form-label']) }}
            {{ Form::select('late_fee_type', $types, null, ['class' => 'form-control hidesearch']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('late_fee_amount', __('Late Fee Amount'), ['class' => 'form-label']) }}
            {{ Form::number('late_fee_amount', null, ['class' => 'form-control', 'placeholder' => __('Enter late fee amount')]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('incident_receipt_amount', __('Incident Receipt Amount'), ['class' => 'form-label']) }}
            {{ Form::number('incident_receipt_amount', null, ['class' => 'form-control', 'placeholder' => __('Enter incident receipt amount')]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => __('Enter notes')]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
<script>
    $('#rent_type').on('change', function() {
        "use strict";
        var type = this.value;
        $('.rent_type').addClass('d-none')
        $('.' + type).removeClass('d-none')

        var input1 = $('.rent_type').find('input');
        input1.prop('disabled', true);
        var input2 = $('.' + type).find('input');
        input2.prop('disabled', false);
    });
</script>
