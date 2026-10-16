{{ Form::open(['route' => 'meter-reading.store', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
            {{ Form::select('property_id', $properties, null, ['class' => 'form-control', 'required' => 'required', 'id' => 'mr_property']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
            <select name="unit_id" id="mr_unit" class="form-control" required><option value="">{{ __('Select Unit') }}</option></select>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('utility_type', __('Utility'), ['class' => 'form-label']) }}
            {{ Form::select('utility_type', $utilityTypes, null, ['class' => 'form-control', 'id' => 'mr_utility']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('reading_date', __('Reading Date'), ['class' => 'form-label']) }}
            {{ Form::date('reading_date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('previous_reading', __('Previous'), ['class' => 'form-label']) }}
            {{ Form::number('previous_reading', 0, ['class' => 'form-control', 'step' => '0.01', 'min' => 0, 'required' => 'required', 'id' => 'mr_previous']) }}
            <small class="text-muted" id="mr_last_hint"></small>
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('current_reading', __('Current'), ['class' => 'form-label']) }}
            {{ Form::number('current_reading', null, ['class' => 'form-control', 'step' => '0.01', 'min' => 0, 'required' => 'required', 'id' => 'mr_current']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('rate', __('Rate per unit'), ['class' => 'form-label']) }}
            {{ Form::number('rate', null, ['class' => 'form-control', 'step' => '0.0001', 'min' => 0, 'required' => 'required', 'id' => 'mr_rate']) }}
        </div>
        <div class="col-md-12 mb-2"><div class="alert alert-light-secondary mb-0 py-2">{{ __('Amount') }}: <strong id="mr_amount">0</strong></div></div>
        <div class="form-group col-md-12">
            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
            {{ Form::text('notes', null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
<script>
    (function() {
        function amount() {
            var used = Math.max(0, (parseFloat($('#mr_current').val()) || 0) - (parseFloat($('#mr_previous').val()) || 0));
            $('#mr_amount').text((used * (parseFloat($('#mr_rate').val()) || 0)).toFixed(2) + ' (' + used + ' {{ __('units') }})');
        }
        function loadLast() {
            var unit = $('#mr_unit').val();
            if (!unit) return;
            $.get('{{ route('meter-reading.last') }}', { unit_id: unit, utility_type: $('#mr_utility').val() }, function(data) {
                $('#mr_previous').val(data.previous_reading);
                if (data.rate !== '') $('#mr_rate').val(data.rate);
                $('#mr_last_hint').text(data.last_date ? '{{ __('Last reading') }}: ' + data.last_date : '{{ __('First reading for this meter') }}');
                amount();
            });
        }
        $('#mr_property').on('change', function() {
            var $unit = $('#mr_unit').html('<option value="">{{ __('Select Unit') }}</option>');
            if (!$(this).val()) return;
            $.get('{{ route('property.unit', ':id') }}'.replace(':id', $(this).val()), function(data) {
                $.each(data, function(id, name) { $unit.append($('<option>').val(id).text(name)); });
            });
        });
        $('#mr_unit, #mr_utility').on('change', loadLast);
        $('#mr_current, #mr_previous, #mr_rate').on('input', amount);
    })();
</script>
