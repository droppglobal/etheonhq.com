{{Form::open(array('url'=>'type','method'=>'post'))}}
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
    {{Form::submit(__('Create'),array('class'=>'btn btn-secondary btn-rounded'))}}
</div>
{{ Form::close() }}


