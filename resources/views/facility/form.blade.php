@if (!empty($facility))
    {{ Form::model($facility, ['route' => ['facility.update', $facility->id], 'method' => 'PUT']) }}
@else
    {{ Form::open(['route' => 'facility.store', 'method' => 'post']) }}
@endif
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('e.g. Gym, Party Hall')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
            {{ Form::select('property_id', $properties, null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('open_time', __('Opens'), ['class' => 'form-label']) }}
            {{ Form::time('open_time', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('close_time', __('Closes'), ['class' => 'form-label']) }}
            {{ Form::time('close_time', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('slot_minutes', __('Slot length (minutes)'), ['class' => 'form-label']) }}
            {{ Form::number('slot_minutes', optional($facility)->slot_minutes ?? 60, ['class' => 'form-control', 'min' => 15, 'step' => 15, 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('max_bookings_per_week', __('Max bookings per tenant per week'), ['class' => 'form-label']) }}
            {{ Form::number('max_bookings_per_week', optional($facility)->max_bookings_per_week ?? 0, ['class' => 'form-control', 'min' => 0]) }}
            <small class="text-muted">{{ __('0 = no limit') }}</small>
        </div>
        <div class="form-group col-md-6 pt-md-4">
            <label class="d-block"><input type="checkbox" name="requires_approval" value="1" {{ optional($facility)->requires_approval ? 'checked' : '' }}> {{ __('Bookings need my approval') }}</label>
            @if (!empty($facility))
                <label class="d-block"><input type="checkbox" name="is_active" value="1" {{ $facility->is_active ? 'checked' : '' }}> {{ __('Open for booking') }}</label>
            @endif
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Rules / Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 2]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(!empty($facility) ? __('Update') : __('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
