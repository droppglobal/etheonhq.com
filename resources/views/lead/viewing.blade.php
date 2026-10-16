@if (!empty($viewing))
    {{ Form::model($viewing, ['route' => ['lead.viewing.update', $viewing->id], 'method' => 'PUT']) }}
@else
    {{ Form::open(['route' => ['lead.viewing.store', $lead->id], 'method' => 'post']) }}
@endif
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('viewing_date', __('Date'), ['class' => 'form-label']) }}
            {{ Form::date('viewing_date', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('viewing_time', __('Time'), ['class' => 'form-label']) }}
            {{ Form::time('viewing_time', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
            {{ Form::select('status', $statuses, empty($viewing) ? 'scheduled' : null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
