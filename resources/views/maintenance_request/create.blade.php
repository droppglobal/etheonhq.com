@php
    $user = \Auth::user();
    $tenant = $user->tenants;
@endphp

{{ Form::open(['url' => 'maintenance-request', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    @include('ai.writer', ['type' => 'maintenance_request'])

    <div class="row">
        @if ($user->type == 'tenant')
            {{ Form::hidden('property_id', !empty($tenant) ? $tenant->property : null) }}
            {{ Form::hidden('unit_id', !empty($tenant) ? $tenant->unit : null) }}
        @else
            {{-- Property --}}
            <div class="form-group col-md-6 col-lg-6">
                {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
                {{ Form::select('property_id', $property, null, [
                    'class' => 'form-control select2',
                    'id' => 'propertyId',
                    'placeholder' => __('Select Property'),
                ]) }}
            </div>

            {{-- Unit --}}
            <div class="form-group col-md-6 col-lg-6">
                {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
                <div class="unit_div">
                    <select class="form-control select2" id="unitId" name="unit_id">
                        <option value="">{{ __('Select Unit') }}</option>
                    </select>
                </div>
            </div>

            {{-- Tenant --}}
            <div class="form-group col-md-6 col-lg-6">
                {{ Form::label('tenant', __('Tenant'), ['class' => 'form-label']) }}
                <select class="form-control select2" id="tenant" name="tenant_id">
                    <option value="">{{ __('Select Tenant') }}</option>
                </select>
            </div>

            {{-- Request Date --}}
            <div class="form-group col-md-6 col-lg-6">
                {{ Form::label('request_date', __('Request Date'), ['class' => 'form-label']) }}
                {{ Form::date('request_date', null, ['class' => 'form-control']) }}
            </div>

            {{-- Maintainer --}}
            <div class="form-group col-md-6 col-lg-6">
                {{ Form::label('maintainer_id', __('Maintainer'), ['class' => 'form-label']) }}
                <select class="form-control select2" id="maintainer_id" name="maintainer_id">
                    <option value="">{{ __('Select Maintainer') }}</option>
                </select>
            </div>

            {{-- Outside vendor (optional) --}}
            <div class="form-group col-md-6 col-lg-6">
                {{ Form::label('vendor_id', __('Outside Vendor (optional)'), ['class' => 'form-label']) }}
                {{ Form::select('vendor_id', $vendors, null, ['class' => 'form-control', 'placeholder' => __('None')]) }}
            </div>
        @endif

        {{-- Issue Type --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('issue_type', __('Issue Type'), ['class' => 'form-label']) }}
            {{ Form::select('issue_type', $types, null, ['class' => 'form-control select2']) }}
        </div>

        {{-- Status --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
            {{ Form::select('status', $status, null, ['class' => 'form-control hidesearch']) }}
        </div>

        {{-- Preferred visit time (v3.5) --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('preferred_date', __('Preferred visit date'), ['class' => 'form-label']) }}
            {{ Form::date('preferred_date', null, ['class' => 'form-control', 'min' => date('Y-m-d')]) }}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('preferred_slot', __('Preferred time'), ['class' => 'form-label']) }}
            {{ Form::select('preferred_slot', \App\Models\MaintenanceRequest::slots(), 'any', ['class' => 'form-control']) }}
        </div>

        {{-- Issue Attachment --}}
        <div class="form-group col-md-12 col-lg-12">
            {{ Form::label('issue_attachment', __('Issue Attachment'), ['class' => 'form-label']) }}
            {{ Form::file('issue_attachment', ['class' => 'form-control']) }}
        </div>

        {{-- Notes --}}
        <div class="form-group col-md-12 col-lg-12">
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
    $(document).ready(function() {

        $(document).on('change', '#propertyId', function() {
            let property_id = $(this).val();

            $('#unitId').html('<option value="">{{ __('Select Unit') }}</option>').trigger('change');
            $('#tenant').html('<option value="">{{ __('Select Tenant') }}</option>').trigger('change');
            $('#maintainer_id').html('<option value="">{{ __('Select Maintainer') }}</option>')
                .trigger('change');

            if (!property_id) return;

            // Load Units
            $.ajax({
                url: '{{ route('property.unit', ':id') }}'.replace(':id', property_id),
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    $.each(data, function(key, value) {
                        $('#unitId').append('<option value="' + key + '">' +
                            value + '</option>');
                    });
                    $('#unitId').trigger('change.select2');
                }
            });

            // Load Maintainers
            $.ajax({
                url: '{{ route('property.based.maintainer') }}',
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    property_id: property_id
                },
                success: function(data) {
                    $.each(data, function(key, value) {
                        $('#maintainer_id').append('<option value="' + key + '">' +
                            value + '</option>');
                    });
                    $('#maintainer_id').trigger('change.select2');
                }
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

    });
</script>
