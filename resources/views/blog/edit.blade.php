{{Form::model($blog, array('route' => array('blog.update', $blog->id), 'method' => 'PUT', 'enctype' => 'multipart/form-data')) }}
<div class="modal-body">
    @include('ai.writer', ['type' => 'blog'])
    <div class="row">
        <div class="form-group col-md-6">
            {{Form::label('title',__('Title'),array('class'=>'form-label'))}}
            {{Form::text('title',null,array('class'=>'form-control','placeholder'=>__('Enter Title')))}}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('image', __('Image'), ['class' => 'form-label']) }}
            {{ Form::file('image', ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('enabled', __('Enabled Page'), ['class' => 'form-label']) }}
            {{ Form::hidden('enabled', 0, ['class' => 'form-check-input']) }}
            <div class="form-check form-switch">
                {{ Form::checkbox('enabled', 1, (int) $blog->enabled === 1, ['class' => 'form-check-input', 'role' => 'switch', 'id' => 'flexSwitchCheckChecked']) }}
                {{ Form::label('', '', ['class' => 'form-check-label']) }}
            </div>
        </div>
        <div class="form-group col-md-12">
            {{Form::label('content',__('Content'),array('class'=>'form-label'))}}
            {!! Form::textarea('content', null, ['class' => 'form-control classic-editor']) !!}
        </div>
    </div>
</div>
<div class="modal-footer">

    {{Form::submit(__('Update'),array('class'=>'btn btn-secondary btn-rounded'))}}
</div>
{{ Form::close() }}

