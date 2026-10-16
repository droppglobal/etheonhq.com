@if (!empty($schedule))
    {{ Form::model($schedule, ['route' => ['maintenance-schedule.update', $schedule->id], 'method' => 'PUT']) }}
@else
    {{ Form::open(['route' => 'maintenance-schedule.store', 'method' => 'post']) }}
@endif
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('title', __('Task'), ['class' => 'form-label']) }}
            {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('e.g. Lift service, Water tank cleaning')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
            {{ Form::select('property_id', $properties, null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Select Property'), 'id' => 'sched_property']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
            <select name="unit_id" id="sched_unit" class="form-control" data-selected="{{ optional($schedule)->unit_id }}">
                <option value="">{{ __('Whole property') }}</option>
            </select>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('issue_type', __('Issue Type'), ['class' => 'form-label']) }}
            {{ Form::select('issue_type', $issueTypes, null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Select Type')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('frequency', __('Repeats'), ['class' => 'form-label']) }}
            {{ Form::select('frequency', $frequencies, null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('next_date', __('Next Due Date'), ['class' => 'form-label']) }}
            {{ Form::date('next_date', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('maintainer_id', __('Maintainer'), ['class' => 'form-label']) }}
            {{ Form::select('maintainer_id', $maintainers, null, ['class' => 'form-control', 'placeholder' => __('Not assigned')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('vendor_id', __('Outside Vendor'), ['class' => 'form-label']) }}
            {{ Form::select('vendor_id', $vendors, null, ['class' => 'form-control', 'placeholder' => __('None')]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('notes', __('Instructions'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2]) }}
        </div>
        @if (!empty($schedule))
            <div class="col-md-12"><label><input type="checkbox" name="is_active" value="1" {{ $schedule->is_active ? 'checked' : '' }}> {{ __('Active') }}</label></div>
        @endif
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(!empty($schedule) ? __('Update') : __('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
<script>
    (function() {
        function loadUnits() {
            var propertyId = $('#sched_property').val(), $unit = $('#sched_unit'), selected = String($unit.data('selected') || '');
            $unit.html('<option value="">{{ __('Whole property') }}</option>');
            if (!propertyId) return;
            $.get('{{ route('property.unit', ':id') }}'.replace(':id', propertyId), function(data) {
                $.each(data, function(id, name) { $unit.append($('<option>').val(id).text(name).prop('selected', String(id) === selected)); });
            });
        }
        $('#sched_property').on('change', function() { $('#sched_unit').data('selected', ''); loadUnits(); });
        loadUnits();
    })();
</script>
