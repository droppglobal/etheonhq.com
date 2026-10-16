{{ Form::open(['url' => 'expense', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group  col-md-12 col-lg-12">
            {{ Form::label('title', __('Expense Title'), ['class' => 'form-label']) }}
            {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Expense Title')]) }}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('expense_id', __('Expense Number'), ['class' => 'form-label']) }}
            <div class="input-group">
                <span class="input-group-text ">
                    {{ expensePrefix() }}
                </span>
                {{ Form::text('expense_id', $billNumber, ['class' => 'form-control', 'placeholder' => __('Enter Expense Number')]) }}
            </div>
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('expense_type', __('Expense Type'), ['class' => 'form-label']) }}
            {{ Form::select('expense_type', $types, null, ['class' => 'form-control select2']) }}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('vendor_id', __('Vendor'), ['class' => 'form-label']) }}
            {{ Form::select('vendor_id', $vendors, null, ['class' => 'form-control', 'placeholder' => __('None')]) }}
        </div>
        <div class="form-group col-12 mb-2">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="split" value="1" id="expense-split">
                <label class="form-check-label" for="expense-split" style="text-transform:none">{{ __('Split this bill across several properties') }}</label>
            </div>
        </div>
        <div class="form-group col-12 split-fields d-none">
            {{ Form::label('split_properties', __('Properties'), ['class' => 'form-label']) }}
            {{ Form::select('split_properties[]', collect($property)->filter(fn($name, $id) => $id !== ''), null, ['class' => 'form-control select2', 'id' => 'split_properties', 'multiple']) }}
            <div class="d-flex gap-3 mt-2">
                <div class="form-check"><input class="form-check-input" type="radio" name="split_by" value="equal" id="split-equal" checked><label class="form-check-label" for="split-equal" style="text-transform:none">{{ __('Equal shares') }}</label></div>
                <div class="form-check"><input class="form-check-input" type="radio" name="split_by" value="units" id="split-units"><label class="form-check-label" for="split-units" style="text-transform:none">{{ __('By number of units') }}</label></div>
            </div>
            <small class="text-muted">{{ __('One expense is saved for each property with its share of the amount.') }}</small>
        </div>
        <div class="form-group col-md-6 col-lg-6 single-fields">
            {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
            {{ Form::select('property_id', $property, null, ['class' => 'form-control select2', 'id' => 'property_id']) }}
        </div>
        <div class="form-group col-lg-6 col-md-6 single-fields">
            {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
            <div class="unit_div">
                <select class="form-control select2 unit" id="unit_id" name="unit_id">
                    <option value="">{{ __('Select Unit') }}</option>
                </select>
            </div>
        </div>
        <div class="form-group  col-md-6 col-lg-6">
            {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
            {{ Form::date('date', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group  col-md-6 col-lg-6">
            {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
            {{ Form::number('amount', null, ['class' => 'form-control', 'placeholder' => __('Enter Expense Amount'), 'step' => 'any']) }}
        </div>
        <div class="form-group  col-md-12 col-lg-12">
            {{ Form::label('receipt', __('Receipt'), ['class' => 'form-label']) }}
            {{ Form::file('receipt', ['class' => 'form-control']) }}
        </div>
        <div class="form-group  col-md-12 col-lg-12">
            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 3]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}

<script>
$('#expense-split').on('change', function () {
    $('.split-fields').toggleClass('d-none', !this.checked);
    $('.single-fields').toggleClass('d-none', this.checked);
});
$('#property_id').on('change', function () {

    var property_id = $(this).val();
    var url = '{{ route('property.unit', ':id') }}'.replace(':id', property_id);

    $.ajax({
        url: url,
        type: 'GET',
        success: function (data) {

            let $unit = $('#unit_id');

            $unit.empty();

            $unit.append('<option value="">Select Unit</option>');

            $.each(data, function (key, value) {
                $unit.append('<option value="' + key + '">' + value + '</option>');
            });
            $unit.trigger('change');

        }
    });
});
</script>
