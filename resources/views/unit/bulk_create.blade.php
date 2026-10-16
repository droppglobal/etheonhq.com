{{ Form::open(['route' => 'unit.bulk.store', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
            {{ Form::select('property_id', $properties, $selected, ['class' => 'form-control', 'placeholder' => __('Select Property'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            <label class="form-label">{{ __('Numbering') }}</label>
            <div class="d-flex gap-3 pt-1">
                <div class="form-check"><input class="form-check-input bu-input" type="radio" name="style" value="floors" id="bu-floors" checked><label class="form-check-label" for="bu-floors" style="text-transform:none">{{ __('By floor (101, 102, 201…)') }}</label></div>
                <div class="form-check"><input class="form-check-input bu-input" type="radio" name="style" value="sequence" id="bu-seq"><label class="form-check-label" for="bu-seq" style="text-transform:none">{{ __('Simple sequence') }}</label></div>
            </div>
        </div>
        <div class="form-group col-md-3 col-6">
            {{ Form::label('prefix', __('Name prefix'), ['class' => 'form-label']) }}
            {{ Form::text('prefix', __('Unit'), ['class' => 'form-control bu-input', 'maxlength' => 30]) }}
        </div>
        <div class="form-group col-md-3 col-6 bu-floors">
            {{ Form::label('floor_from', __('From floor'), ['class' => 'form-label']) }}
            {{ Form::number('floor_from', 1, ['class' => 'form-control bu-input', 'min' => 0, 'max' => 200]) }}
        </div>
        <div class="form-group col-md-3 col-6 bu-floors">
            {{ Form::label('floor_to', __('To floor'), ['class' => 'form-label']) }}
            {{ Form::number('floor_to', 5, ['class' => 'form-control bu-input', 'min' => 0, 'max' => 200]) }}
        </div>
        <div class="form-group col-md-3 col-6 bu-seq d-none">
            {{ Form::label('start_number', __('Start at'), ['class' => 'form-label']) }}
            {{ Form::number('start_number', 1, ['class' => 'form-control bu-input', 'min' => 0]) }}
        </div>
        <div class="form-group col-md-3 col-6">
            <label class="form-label bu-floors-label">{{ __('Units per floor') }}</label>
            {{ Form::number('units_per_floor', 4, ['class' => 'form-control bu-input', 'min' => 1, 'max' => 200, 'required' => 'required']) }}
        </div>
        <div class="col-12 mb-3">
            <div class="border rounded p-2 small bg-light"><span class="text-muted">{{ __('Preview') }}:</span> <span id="bu-preview"></span></div>
        </div>

        <div class="form-group col-md-4 col-6">{{ Form::label('bedroom', __('Bedroom'), ['class' => 'form-label']) }}{{ Form::number('bedroom', 2, ['class' => 'form-control', 'min' => 0]) }}</div>
        <div class="form-group col-md-4 col-6">{{ Form::label('kitchen', __('Kitchen'), ['class' => 'form-label']) }}{{ Form::number('kitchen', 1, ['class' => 'form-control', 'min' => 0]) }}</div>
        <div class="form-group col-md-4 col-6">{{ Form::label('baths', __('Bath'), ['class' => 'form-label']) }}{{ Form::number('baths', 1, ['class' => 'form-control', 'min' => 0]) }}</div>
        <div class="form-group col-md-4 col-6">{{ Form::label('rent', __('Rent'), ['class' => 'form-label']) }}{{ Form::number('rent', null, ['class' => 'form-control', 'min' => 0, 'step' => '0.01', 'required' => 'required']) }}</div>
        <div class="form-group col-md-4 col-6">{{ Form::label('rent_type', __('Rent Type'), ['class' => 'form-label']) }}{{ Form::select('rent_type', ['monthly' => __('Monthly'), 'yearly' => __('Yearly')], 'monthly', ['class' => 'form-control']) }}</div>
        <div class="form-group col-md-4 col-6">{{ Form::label('rent_duration', __('Rent due day'), ['class' => 'form-label']) }}{{ Form::number('rent_duration', 1, ['class' => 'form-control', 'min' => 1, 'max' => 31]) }}</div>
        <div class="form-group col-md-4 col-6">{{ Form::label('deposit_type', __('Deposit Type'), ['class' => 'form-label']) }}{{ Form::select('deposit_type', $types, 'fixed', ['class' => 'form-control']) }}</div>
        <div class="form-group col-md-4 col-6">{{ Form::label('deposit_amount', __('Deposit Amount'), ['class' => 'form-label']) }}{{ Form::number('deposit_amount', 0, ['class' => 'form-control', 'min' => 0, 'step' => '0.01']) }}</div>
        <div class="form-group col-md-4 col-6">{{ Form::label('incident_receipt_amount', __('Incident Receipt Amount'), ['class' => 'form-label']) }}{{ Form::number('incident_receipt_amount', 0, ['class' => 'form-control', 'min' => 0, 'step' => '0.01']) }}</div>
        <div class="form-group col-md-6 col-6">{{ Form::label('late_fee_type', __('Late Fee Type'), ['class' => 'form-label']) }}{{ Form::select('late_fee_type', $types, 'fixed', ['class' => 'form-control']) }}</div>
        <div class="form-group col-md-6 col-6">{{ Form::label('late_fee_amount', __('Late Fee Amount'), ['class' => 'form-label']) }}{{ Form::number('late_fee_amount', 0, ['class' => 'form-control', 'min' => 0, 'step' => '0.01']) }}</div>
        <div class="form-group col-12 mb-0">{{ Form::label('notes', __('Notes (added to every unit)'), ['class' => 'form-label']) }}{{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2]) }}</div>
    </div>
</div>
<div class="modal-footer">
    <span class="text-muted small me-auto" id="bu-count"></span>
    <button type="submit" class="btn btn-secondary btn-rounded" id="bu-submit">{{ __('Create units') }}</button>
</div>
{{ Form::close() }}
<script>
    (function() {
        var max = {{ \App\Http\Controllers\UnitBulkController::MAX_UNITS }};
        function names() {
            var style = $('input[name=style]:checked').val(), prefix = $.trim($('[name=prefix]').val()), per = parseInt($('[name=units_per_floor]').val()) || 0, out = [];
            var join = prefix === '' || /[-\/ ]$/.test(prefix) ? '' : ' ';
            if (style === 'sequence') {
                var start = parseInt($('[name=start_number]').val()) || 0;
                for (var i = 0; i < per && out.length <= max; i++) out.push(prefix + join + (start + i));
            } else {
                var from = parseInt($('[name=floor_from]').val()) || 0, to = parseInt($('[name=floor_to]').val()) || 0;
                for (var f = from; f <= to && out.length <= max; f++) for (var n = 1; n <= per; n++) out.push(prefix + join + (f === 0 ? 'G' : f) + String(n).padStart(2, '0'));
            }
            return out;
        }
        function update() {
            var seq = $('input[name=style]:checked').val() === 'sequence';
            $('.bu-floors').toggleClass('d-none', seq); $('.bu-seq').toggleClass('d-none', !seq);
            $('.bu-floors-label').text(seq ? @json(__('How many units')) : @json(__('Units per floor')));
            var list = names();
            $('#bu-preview').text(list.length ? list.slice(0, 6).join(', ') + (list.length > 6 ? ' … ' + list[list.length - 1] : '') : '-');
            $('#bu-count').text(list.length > max ? @json(__('Too many units at once')) : list.length + ' ' + @json(__('units will be created')));
            $('#bu-submit').prop('disabled', !list.length || list.length > max);
        }
        $(document).on('input change', '.bu-input', update);
        update();
    })();
</script>
