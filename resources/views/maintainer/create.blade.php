{{ Form::open(['url' => 'maintainer', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('first_name', __('First Name'), ['class' => 'form-label']) }}
            {{ Form::text('first_name', null, ['class' => 'form-control', 'placeholder' => __('Enter First Name')]) }}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('last_name', __('Last Name'), ['class' => 'form-label']) }}
            {{ Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('Enter Last Name')]) }}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
            {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => __('Enter Email')]) }}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
            {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('Enter Password')]) }}
        </div>
        <div class="form-group">
            {{ Form::label('phone_number', __('Phone Number'), ['class' => 'form-label']) }}
            {{ Form::text('phone_number', null, ['class' => 'form-control', 'placeholder' => __('Enter Phone Number')]) }}
            <small class="form-text text-muted">
                {{ __('Please enter the number with country code. e.g., +91XXXXXXXXXX') }}
            </small>
        </div>
        <div class="form-group">
            {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
            {{ Form::select('property_id[]', $property, null, ['class' => 'form-control select2', 'id' => 'property', 'multiple']) }}
        </div>

        <div class="form-group">
            {{ Form::label('type_id', __('Type'), ['class' => 'form-label']) }}
            {{ Form::select('type_id', $types, null, ['class' => 'form-control select2']) }}
        </div>
        <div class="form-group">
            {{ Form::label('profile', __('Profile'), ['class' => 'form-label']) }}
            {{ Form::file('profile', ['class' => 'form-control']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
