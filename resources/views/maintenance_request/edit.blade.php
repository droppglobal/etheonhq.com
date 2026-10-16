@php
    $user = \Auth::user();
    $tenant = $user->tenants;
@endphp

{{ Form::model($maintenanceRequest, ['route' => ['maintenance-request.update', $maintenanceRequest->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
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
                {{ Form::select('property_id', $property, $maintenanceRequest->property_id, [
                    'class' => 'form-control select2',
                    'id' => 'property_id',
                    'placeholder' => __('Select Property'),
                ]) }}
            </div>

            {{-- Unit --}}
            <div class="form-group col-md-6 col-lg-6">
                {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
                <div class="unit_div">
                    <select class="form-control select2" id="unit_id" name="unit_id">
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
                {{ Form::date('request_date', $maintenanceRequest->request_date, ['class' => 'form-control']) }}
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
                {{ Form::select('vendor_id', $vendors, $maintenanceRequest->vendor_id ?: null, ['class' => 'form-control', 'placeholder' => __('None')]) }}
            </div>
        @endif

        {{-- Issue Type --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('issue_type', __('Issue Type'), ['class' => 'form-label']) }}
            {{ Form::select('issue_type', $types, $maintenanceRequest->issue_type, ['class' => 'form-control select2']) }}
        </div>

        {{-- Status --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
            {{ Form::select('status', $status, $maintenanceRequest->status, ['class' => 'form-control hidesearch']) }}
        </div>

        {{-- Issue Attachment --}}
        <div class="form-group col-md-12 col-lg-12">
            {{ Form::label('issue_attachment', __('Issue Attachment'), ['class' => 'form-label']) }}
            {{ Form::file('issue_attachment', ['class' => 'form-control']) }}
            @if (!empty($maintenanceRequest->issue_attachment))
                <div class="mt-1">
                    <a href="{{ asset(Storage::url($maintenanceRequest->issue_attachment)) }}" target="_blank">
                        {{ __('View Current Attachment') }}
                    </a>
                </div>
            @endif
        </div>

        {{-- Notes --}}
        <div class="form-group col-md-12 col-lg-12">
            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', $maintenanceRequest->notes, ['class' => 'form-control', 'rows' => 3]) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {

        var selected_unit = "{{ $maintenanceRequest->unit_id ?? '' }}";
        var selected_tenant = "{{ $maintenanceRequest->tenant_id ?? '' }}";
        var selected_maintainer = "{{ $maintenanceRequest->maintainer_id ?? '' }}";

        // ✅ Remove previous listeners before attaching new ones
        $(document).off('change', '#property_id').on('change', '#property_id', function() {
            let property_id = $(this).val();

            $('#unit_id').html('<option value="">{{ __('Select Unit') }}</option>').trigger('change');
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
                        let selected = (key == selected_unit) ? 'selected' : '';
                        $('#unit_id').append('<option value="' + key + '" ' +
                            selected + '>' + value + '</option>');
                    });
                    $('#unit_id').trigger('change.select2');

                    if (selected_unit) {
                        $('#unit_id').trigger('change');
                    }
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
                        let selected = (key == selected_maintainer) ? 'selected' :
                            '';
                        $('#maintainer_id').append('<option value="' + key + '" ' +
                            selected + '>' + value + '</option>');
                    });
                    $('#maintainer_id').trigger('change.select2');
                }
            });
        });

        $(document).off('change', '#unit_id').on('change', '#unit_id', function() {
            let unit_id = $(this).val();
            let property_id = $('#property_id').val();

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
                        let selected = (key == selected_tenant) ? 'selected' : '';
                        $('#tenant').append('<option value="' + key + '" ' +
                            selected + '>' + value + '</option>');
                    });
                    $('#tenant').trigger('change.select2');
                }
            });
        });

        // Auto-trigger on load
        if ($('#property_id').val()) {
            $('#property_id').trigger('change');
        }

    });
</script>
