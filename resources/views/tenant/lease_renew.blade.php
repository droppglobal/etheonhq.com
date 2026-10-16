{{ Form::open(['route' => 'lease.renew.store', 'method' => 'post']) }}
<div class="modal-body">
    <div class="card-body">
        {!! Form::hidden('tenant_id', $tenant->id) !!}
        <div class="row">
            <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('property', __('Property'), ['class' => 'form-label']) }}
                {{ Form::select('property', $property, $tenant->property, ['class' => 'form-control select2', 'id' => 'property']) }}
            </div>
            <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('unit', __('Unit'), ['class' => 'form-label']) }}
                <div class="unit_div">
                    <select class="form-control select2 unit" id="unit" name="unit">
                        <option value="">{{ __('Select Unit') }}</option>
                    </select>
                </div>
            </div>
            <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('lease_start_date', __('Lease Start Date'), ['class' => 'form-label']) }}
                {{ Form::date('lease_start_date', $tenant->lease_start_date, ['class' => 'form-control']) }}
            </div>
            <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('lease_end_date', __('Lease End Date'), ['class' => 'form-label']) }}
                {{ Form::date('lease_end_date', $tenant->lease_end_date, ['class' => 'form-control']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
<script>
    (function() {

        let selectedUnitId = "{{ $selectedUnit }}";

        $('#property').off('change').on('change', function() {

            let property_id = $(this).val();
            let url = '{{ route('tenant.unit', ':id') }}'.replace(':id', property_id);

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    property_id: property_id
                },
                success: function(data) {

                    let $unit = $('#unit');

                    $unit.empty().append('<option value="">{{ __('Select Unit') }}</option>');

                    $.each(data, function(key, value) {
                        let statusDot = value.is_occupied == 1 ? '🔴' : '🟢';
                        let selected = (key == selectedUnitId) ? 'selected' : '';

                        $unit.append(
                            '<option value="' + key + '" ' + selected + '>' +
                            value.name + ' ' + statusDot +
                            '</option>'
                        );
                    });

                    // ✅ call your global initializer
                    select2(document);

                    $unit.trigger('change');
                }
            });
        });

        if ($('#property').val()) {
            $('#property').trigger('change');
        }

    })();
</script>
