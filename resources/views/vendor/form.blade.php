@if (!empty($vendor))
    {{ Form::model($vendor, ['route' => ['vendor.update', $vendor->id], 'method' => 'PUT']) }}
@else
    {{ Form::open(['route' => 'vendor.store', 'method' => 'post']) }}
@endif
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('name', __('Contact Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('company', __('Company'), ['class' => 'form-label']) }}
            {{ Form::text('company', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('phone', __('Phone'), ['class' => 'form-label']) }}
            {{ Form::text('phone', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
            {{ Form::email('email', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('service', __('Service'), ['class' => 'form-label']) }}
            {{ Form::text('service', null, ['class' => 'form-control', 'placeholder' => __('e.g. Plumbing, Lift maintenance')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}
            {{ Form::text('address', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => __('Rates, contract details, quotes...')]) }}
        </div>
        @if (!empty($vendor))
            <div class="col-md-12"><label><input type="checkbox" name="is_active" value="1" {{ $vendor->is_active ? 'checked' : '' }}> {{ __('Active') }}</label></div>
        @endif
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(!empty($vendor) ? __('Update') : __('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
