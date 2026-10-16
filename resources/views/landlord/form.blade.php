@if (!empty($landlord))
    {{ Form::model($landlord, ['route' => ['landlord.update', $landlord->id], 'method' => 'PUT']) }}
@else
    {{ Form::open(['route' => 'landlord.store', 'method' => 'post']) }}
@endif
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('first_name', __('First Name'), ['class' => 'form-label']) }}
            {{ Form::text('first_name', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('last_name', __('Last Name'), ['class' => 'form-label']) }}
            {{ Form::text('last_name', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('email', __('Email (login)'), ['class' => 'form-label']) }}
            {{ Form::email('email', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('phone_number', __('Phone'), ['class' => 'form-label']) }}
            {{ Form::text('phone_number', null, ['class' => 'form-control']) }}
        </div>
        @if (empty($landlord))
            <div class="form-group col-md-6">
                {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
                {{ Form::password('password', ['class' => 'form-control', 'required' => 'required', 'minlength' => 6]) }}
            </div>
        @endif
        <div class="form-group col-md-6">
            {{ Form::label('management_fee_percent', __('Management Fee (%)'), ['class' => 'form-label']) }}
            {{ Form::number('management_fee_percent', optional($profile)->management_fee_percent ?? 0, ['class' => 'form-control', 'min' => 0, 'max' => 100, 'step' => '0.01']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('property_ids', __('Properties'), ['class' => 'form-label']) }}
            {{ Form::select('property_ids[]', $properties, $assigned, ['class' => 'form-control select2', 'id' => 'property', 'multiple']) }}
            <small class="text-muted">{{ __('A property belongs to one landlord.') }}</small>
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('bank_details', __('Payout Bank Details'), ['class' => 'form-label']) }}
            {{ Form::textarea('bank_details', optional($profile)->bank_details, ['class' => 'form-control', 'rows' => 2]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', optional($profile)->notes, ['class' => 'form-control', 'rows' => 2]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(!empty($landlord) ? __('Update') : __('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
