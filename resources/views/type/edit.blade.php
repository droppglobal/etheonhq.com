{{Form::model($type, array('route' => array('type.update', $type->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="form-group ">
        {{Form::label('title',__('Title'),array('class'=>'form-label'))}}
        {{Form::text('title',null,array('class'=>'form-control','placeholder'=>__('Enter Invoice / Expense / Maintainance Issue,Type Title')))}}
    </div>
    <div class="form-group">
        {{ Form::label('type', __('Type'),['class'=>'form-label']) }}
        {!! Form::select('type', $types, null,array('class' => 'form-control select2','required'=>'required')) !!}
    </div>
</div>
<div class="modal-footer">
    {{Form::submit(__('Update'),array('class'=>'btn btn-secondary btn-rounded'))}}
</div>
{{ Form::close() }}




