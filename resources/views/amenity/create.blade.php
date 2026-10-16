{{ Form::open(['url' => 'amenity', 'enctype' => 'multipart/form-data']) }}

<div class="modal-body">
    @include('ai.writer', ['type' => 'amenity'])
    <div class="form-group col-md-12">
        {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
        {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Amenity Name')]) }}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('image', __('Image'), ['class' => 'form-label']) }}
        {{ Form::file('image', ['class' => 'form-control']) }}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
        {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3]) }}
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
