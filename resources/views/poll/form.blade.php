{{ Form::open(['route' => 'poll.store', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('question', __('Question'), ['class' => 'form-label']) }}
            {{ Form::text('question', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Details (optional)'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 2]) }}
        </div>
        <div class="col-md-12"><label class="form-label">{{ __('Options') }}</label></div>
        @for ($i = 0; $i < 5; $i++)
            <div class="form-group col-md-6">
                {{ Form::text('options[]', null, array_merge(['class' => 'form-control', 'placeholder' => __('Option') . ' ' . ($i + 1)], $i < 2 ? ['required' => 'required'] : [])) }}
            </div>
        @endfor
        <div class="form-group col-md-6">
            {{ Form::label('property_id', __('Who can vote'), ['class' => 'form-label']) }}
            {{ Form::select('property_id', $properties, 0, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('closes_on', __('Closes on (optional)'), ['class' => 'form-label']) }}
            {{ Form::date('closes_on', null, ['class' => 'form-control', 'min' => date('Y-m-d')]) }}
        </div>
        <div class="col-md-12"><label><input type="checkbox" name="show_results" value="1" checked> {{ __('Show results to tenants after they vote') }}</label></div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Publish'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
