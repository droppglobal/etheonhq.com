{{ Form::open(['url' => 'FAQ', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        @include('ai.writer', ['type' => 'faq'])
        <div class="form-group  col-md-12">
            {{ Form::label('question', __('Question'), ['class' => 'form-label']) }}
            {{ Form::text('question', null, ['class' => 'form-control', 'placeholder' => __('Enter Question')]) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 5]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('enabled', __('Enabled FAQ'), ['class' => 'form-label']) }}
            {{ Form::hidden('enabled', 0, ['class' => 'form-check-input']) }}
            <div class="form-check form-switch">
                {{ Form::checkbox('enabled', 1, true, ['class' => 'form-check-input', 'role' => 'switch', 'id' => 'flexSwitchCheckChecked']) }}
                {{ Form::label('', '', ['class' => 'form-check-label']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">

    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
