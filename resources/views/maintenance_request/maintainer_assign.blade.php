{{ Form::open(['route' => 'maintenance.assign.store', 'method' => 'post']) }}

<div class="modal-body">

    {{ Form::hidden('request_id', $maintenanceRequest->id) }}

    <div class="form-group">
        {{ Form::label('maintainer_id', __('Maintainer'), ['class' => 'form-label']) }}
        {{ Form::select('maintainer_id', $maintainers, null, ['class' => 'form-control select2', 'required']) }}
    </div>
    <div class="form-group">
        {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
        {{ Form::select('status', $status, null, ['class' => 'form-control select2', 'required']) }}
    </div>

</div>

<div class="modal-footer">
    <button type="submit" class="btn btn-secondary">
        {{ __('Assign') }}
    </button>
</div>

{{ Form::close() }}
