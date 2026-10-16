@extends('layouts.app')
@section('page-title')
    {{ __('Invoice') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script>
        $(document).ready(function() {

            // PROPERTY → UNIT
            $('#property_id').on('change', function() {

                var property_id = $(this).val();
                if (!property_id) return;

                var url = '{{ route('property.unit', ':id') }}'.replace(':id', property_id);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(data) {

                        // Reset Unit
                        let unitHtml = `
                    <select class="form-control select2" id="unit" name="unit_id">
                        <option value="">{{ __('Select Unit') }}</option>
                    </select>
                `;
                        $('.unit_div').html(unitHtml);

                        // Reset Tenant
                        let tenantHtml = `
                    <select class="form-control select2" id="tenant" name="tenant">
                        <option value="">{{ __('Select Tenant') }}</option>
                    </select>
                `;
                        $('.tenant_div').html(tenantHtml);

                        let firstKey = null;

                        // Append Units
                        $.each(data, function(key, value) {
                            if (!firstKey) firstKey = key;
                            $('#unit').append('<option value="' + key + '">' + value +
                                '</option>');
                        });

                        // Re-init select2 (ONLY this)
                        $('#unit').select2();
                        $('#tenant').select2();

                        // Auto select first
                        if (firstKey) {
                            $('#unit').val(firstKey).trigger('change');
                        }
                    }
                });
            });


            // UNIT → TENANT
            $(document).on('change', '#unit', function() {

                var unit_id = $(this).val();
                if (!unit_id) return;

                var url = '{{ route('unit.by.tenant', ':id') }}'.replace(':id', unit_id);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(data) {

                        let tenantHtml = `
                    <select class="form-control select2" id="tenant" name="tenant">
                        <option value="">{{ __('Select Tenant') }}</option>
                    </select>
                `;
                        $('.tenant_div').html(tenantHtml);

                        $.each(data, function(key, value) {
                            $('#tenant').append('<option value="' + key + '">' + value +
                                '</option>');
                        });

                        $('#tenant').select2();
                    }
                });
            });

        });
    </script>


    <script>
        var selector = "body";

        if ($(selector + " .repeater").length) {

            var $dragAndDrop = $("body .repeater tbody").sortable({
                handle: '.sort-handler'
            });

            var $repeater = $(selector + ' .repeater').repeater({
                initEmpty: false,
                defaultValues: {
                    'status': 1
                },

                show: function() {
                    $(this).slideDown();

                    // ✅ Re-init select2 ONLY for new row
                    $(this).find('.select2').select2();
                },

                hide: function(deleteElement) {
                    if (confirm('Are you sure you want to delete this element?')) {
                        $(this).slideUp(deleteElement);
                        $(this).remove();
                    }
                },

                ready: function(setIndexes) {
                    $dragAndDrop.on('drop', setIndexes);
                },

                isFirstItemUndeletable: true
            });

            // Load existing data (if any)
            var value = $(selector + " .repeater").attr('data-value');
            if (typeof value != 'undefined' && value.length != 0) {
                value = JSON.parse(value);
                $repeater.setList(value);
            }
        }

        $(document).ready(function() {
            $('#is_recurring').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#recurring_date_wrapper').removeClass('d-none');
                } else {
                    $('#recurring_date_wrapper').addClass('d-none');
                    $('#recurring_date').val('');
                }
            });
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"><a href="{{ route('invoice.index') }}"> {{ __('Invoice') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Create') }}</li>
@endsection

@section('content')
    {{ Form::open(['url' => 'invoice', 'method' => 'post', 'id' => 'invoice_form']) }}
    <div class="row mt-4">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="info-group">
                        <div class="row">
                            <div class="form-group col-md-6 col-lg-4">
                                {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
                                {{ Form::select('property_id', $property, null, ['class' => 'form-control select2', 'id' => 'property_id']) }}
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
                                <div class="unit_div">
                                    <select class="form-control select2 unit" id="unit" name="unit_id">
                                        <option value="">{{ __('Select Unit') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-6 col-lg-4">
                                {{ Form::label('tenant', __('Tenant'), ['class' => 'form-label']) }}
                                <div class="tenant_div">
                                    <select class="form-control select2 tenant" id="tenant" name="tenant">
                                        <option value="">{{ __('Select Tenant') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-6 col-lg-4">
                                <div class="form-group">
                                    {{ Form::label('invoice_id', __('Invoice Number'), ['class' => 'form-label']) }}
                                    <div class="input-group">
                                        <span class="input-group-text ">
                                            {{ invoicePrefix() }}
                                        </span>
                                        {{ Form::text('invoice_id', $invoiceNumber, ['class' => 'form-control', 'placeholder' => __('Enter Invoice Number')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                {{ Form::label('invoice_month', __('Invoice Month'), ['class' => 'form-label']) }}
                                {{ Form::month('invoice_month', null, ['class' => 'form-control']) }}
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                {{ Form::label('end_date', __('Invoice End Date'), ['class' => 'form-label']) }}
                                {{ Form::date('end_date', null, ['class' => 'form-control']) }}
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
                                {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => __('Enter Notes')]) }}
                            </div>
                            <div class="form-group col-md-2 col-lg-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_recurring" name="is_recurring"
                                        value="1">
                                    <label class="form-check-label" for="is_recurring">
                                        {{ __('Recurring Invoice') }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-2 col-lg-2 d-none" id="recurring_date_wrapper">
                                <label for="recurring_date">{{ __('Recurring Date') }}</label>
                                <select name="recurring_date" id="recurring_date" class="form-control hidesearch">
                                    <option value="">{{ __('Select Day') }}</option>
                                    @for ($i = 1; $i <= 31; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card repeater">
                <div class="card-header">

                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">{{ __('Invoice Type') }}</h5>

                        <a class="btn btn-secondary d-flex align-items-center gap-2" href="#" data-repeater-create="">
                            <i class="ti ti-circle-plus align-text-bottom"></i>{{ __('Add Type') }}</a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="display dataTable cell-border" data-repeater-list="types">
                        <thead>
                            <tr>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody data-repeater-item>
                            <tr>
                                <td width="30%">
                                    {{ Form::select('invoice_type', $types, null, ['class' => 'form-control select2']) }}
                                </td>
                                <td>
                                    {{ Form::number('amount', null, ['class' => 'form-control', 'required']) }}
                                </td>
                                <td>
                                    {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 1]) }}
                                </td>
                                <td>
                                    <a class="text-danger" data-repeater-delete data-bs-toggle="tooltip"
                                        data-bs-original-title="{{ __('Detete') }}" href="#"> <i
                                            data-feather="trash-2"></i></a>
                                </td>
                            </tr>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="group-button text-end">
                {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded', 'id' => 'invoice-submit']) }}
            </div>
        </div>
    </div>
    {{ Form::close() }}
@endsection
