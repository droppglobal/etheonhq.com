{{ Form::open(['url' => 'agreement', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    @include('ai.writer', ['type' => 'agreement'])
    <div class="row">
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
            {{ Form::select('property_id', $property, null, ['class' => 'form-control select2', 'id' => 'propertyId']) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
            <div class="unit_div">
                <select class="form-control select2 unitId" id="unitId" name="unit_id">
                    <option value="">{{ __('Select Unit') }}</option>
                </select>
            </div>
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('tenant', __('Tenant'), ['class' => 'form-label']) }}
            <select class="form-control select2" id="tenant" name="tenant_id">
                <option value="">{{ __('Select Tenant') }}</option>
            </select>
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('agreement_start_date', __('Agreement Start Date'), ['class' => 'form-label']) }}
            {{ Form::date('agreement_start_date', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('agreement_end_date', __('Agreement End Date'), ['class' => 'form-label']) }}
            {{ Form::date('agreement_end_date', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
            {{ Form::select('status', $status, null, ['class' => 'form-control hidesearch']) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('terms_condition', __('Terms & Condition'), ['class' => 'form-label']) }}
            {{ Form::textarea('terms_condition', $setting['terms_condition'], ['class' => 'form-control classic-editor']) }}
        </div>

        <div class="form-group  col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', $setting['agreement_description'], ['class' => 'form-control classic-editor']) }}
        </div>

        {{-- <div class="form-group">
            {{ Form::label('profile', __('Profile'), ['class' => 'form-label']) }}
            {{ Form::file('profile', ['class' => 'form-control']) }}
        </div> --}}
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
 


<script>
    $('#propertyId').on('change', function() {
        "use strict";
        var property_id = $(this).val();
        var url = '{{ route('property.unit', ':id') }}';
        url = url.replace(':id', property_id);
        $.ajax({
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                property_id: property_id,
            },
            contentType: false,
            processData: false,
            type: 'GET',
            success: function(data) {
                $('.unitId').empty();
                var unit =
                    `<select class="form-control select2 unitId" id="unitId" name="unit_id"></select>`;
                $('.unit_div').html(unit);

                $.each(data, function(key, value) {
                    $('.unitId').append('<option value="' + key + '">' + value +
                        '</option>');
                });
                $("#unitId").select2();
            },

        });
    });

    $(document).on('change', '#unitId', function() {
        let unit_id = $(this).val();
        let property_id = $('#propertyId').val();

        $('#tenant').html('<option value="">{{ __('Select Tenant') }}</option>').trigger('change');

        if (!unit_id || !property_id) return;

        $.ajax({
            url: '{{ route('unit.by.tenant', ':id') }}'.replace(':id', unit_id),
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                $.each(data, function(key, value) {
                    $('#tenant').append('<option value="' + key + '">' + value +
                        '</option>');
                });
                $('#tenant').trigger('change.select2');
            }
        });
    });
</script>
