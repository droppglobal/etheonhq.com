{{ Form::model($contact, ['route' => ['contact.update', $contact->id], 'method' => 'PUT']) }}
<div class="modal-body">
    @include('ai.writer', ['type' => 'contact'])
    <div class="row">
        <div class="form-group  col-md-12">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter contact name')]) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
            {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => __('Enter contact email')]) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('contact_number', __('Contact Number'), ['class' => 'form-label']) }}
            {{ Form::number('contact_number', null, ['class' => 'form-control', 'placeholder' => __('Enter contact number')]) }}
            <small class="form-text text-muted">
                {{ __('Please enter the number with country code. e.g., +91XXXXXXXXXX') }}
            </small>
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('subject', __('Subject'), ['class' => 'form-label']) }}
            {{ Form::text('subject', null, ['class' => 'form-control', 'placeholder' => __('Enter contact subject')]) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('message', __('Message'), ['class' => 'form-label']) }}
            {{ Form::textarea('message', null, ['class' => 'form-control', 'rows' => 5]) }}
        </div>
    </div>
</div>
<div class="modal-footer">

    {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
