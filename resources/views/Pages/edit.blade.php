{{ Form::model($page, ['route' => ['pages.update', $page->id], 'method' => 'PUT']) }}
<div class="modal-body">
    @include('ai.writer', ['type' => 'custom_page'])
    <div class="row">
        <div class="form-group  col-md-12">
            {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
            {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Title')]) }}
        </div>
         <div class="form-group col-md-6">
            {{ Form::label('enabled', __('Enabled Page'), ['class' => 'form-label']) }}
            <input type="hidden" name="enabled" value="0">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckChecked" name="enabled"
                    value="1" {{ $page->enabled == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="flexSwitchCheckChecked"></label>
            </div>
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('content', __('Content'), ['class' => 'form-label']) }}
            {!! Form::textarea('content', null, ['class' => 'form-control classic-editor']) !!}
        </div>
    </div>
</div>
<div class="modal-footer">

    {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
