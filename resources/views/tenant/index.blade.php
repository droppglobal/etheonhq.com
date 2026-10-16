@extends('layouts.app')
@section('page-title')
    {{ __('Tenant') }}
@endsection
@php
    $profile = asset(Storage::url('upload/profile/avatar.png'));
@endphp

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Tenant') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-2 col-md-6">

                            <input type="text" id="filter_search" class="form-control"
                                placeholder="{{ __('Name or phone number...') }}">
                        </div>

                        <div class="col-lg-2 col-md-6">

                            {{ Form::select('property', $property, request('property'), [
                                'class' => 'form-control select2 property',
                                'id' => 'property_id',
                            ]) }}
                        </div>

                        <div class="col-lg-2 col-md-6">

                            {{ Form::select('unit', [], request('unit'), [
                                'class' => 'form-control select2 unit',
                                'id' => 'unit_id',
                                'placeholder' => __('Select Unit'),
                            ]) }}
                        </div>

                        <div class="col-lg-2 col-md-6">

                            {{ Form::text('date', request('date'), [
                                'class' => 'form-control date',
                                'placeholder' => __('Select lease end date'),
                                'id' => 'demo',
                                'autocomplete' => 'off',
                            ]) }}
                        </div>

                        <div class="col-lg-1 col-md-6">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary" id="btn_search" data-bs-toggle="tooltip"
                                    title="{{ __('Search') }}">
                                    <i class="ti ti-search"></i>
                                </button>
                                <button type="button" class="btn btn-primary" id="btn_reset" data-bs-toggle="tooltip"
                                    onclick="window.location.reload();" title="{{ __('Reset') }}">
                                    <i class="ti ti-refresh"></i>
                                </button>
                            </div>
                        </div>

                        @can('create tenant')
                            <div class="col-lg-auto col-md-12 ms-lg-auto">
                                <a class="btn btn-secondary w-100 w-lg-auto" href="{{ route('tenant.create') }}">
                                    <i class="ti ti-circle-plus me-1"></i> {{ __('Create Tenant') }}
                                </a>
                            </div>
                        @endcan
                    </div>
                </div>

                <div class="card-body" id="tenant-list-wrapper">
                    @include('tenant.tenant_list', ['tenants' => $tenants])
                </div>

            </div>
        </div>
    </div>
@endsection
@push('script-page')
    <script>
        $('#demo').daterangepicker({
            autoApply: true,
            autoUpdateInput: false,
            locale: {
                format: 'MM/DD/YYYY'
            }
        }, function(start, end) {
            $('#demo').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
        });

        var baseUrl = "{{ route('tenant.index') }}";

        function loadTenants(page) {
            page = page || 1;

            var search = $('#filter_search').val();
            var property = $('#property_id').val();
            var unit = $('#unit_id').val();
            var date = $('#demo').val();

            $.ajax({
                url: baseUrl,
                type: 'GET',
                data: {
                    page: page,
                    search: search,
                    property: property,
                    unit: unit,
                    date: date
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(data) {
                    $('#tenant-list-wrapper').html(data.html);
                    if (typeof feather !== 'undefined') feather.replace();
                },
                error: function() {
                    $('#tenant-list-wrapper').html(`
                <div class="text-center py-5">
                    <i class="ti ti-alert-circle text-danger" style="font-size:48px;"></i>
                    <p class="text-danger mt-2">Something went wrong. Please try again.</p>
                </div>
            `);
                }
            });
        }

        // Pagination
        $(document).on('click', '#tenant-list-wrapper .pagination a', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            if (!href || href === '#') return;
            var page = new URL(href, window.location.origin).searchParams.get('page') || 1;
            loadTenants(page);
        });

        // Search
        $('#btn_search').on('click', function() {
            loadTenants(1);
        });

        // Enter key
        $('#filter_search').on('keydown', function(e) {
            if (e.key === 'Enter') loadTenants(1);
        });

        // Reset
        $('#btn_reset').on('click', function() {
            $('#filter_search').val('');
            $('#property_id').val('').trigger('change');
            $('#unit_id').val('').trigger('change');
            $('#demo').val('');
            loadTenants(1);
        });

        $(document).on('change', '.property', function() {

            let property_id = $(this).val();

            $('.unit').html('<option value="">{{ __('Select Unit') }}</option>').trigger('change');
            $('.tenant').html('<option value="">{{ __('Select Tenant') }}</option>').trigger('change');

            if (!property_id) return;

            $.ajax({
                url: '{{ route('property.unit', ':id') }}'.replace(':id', property_id),
                type: 'GET',
                success: function(data) {

                    $.each(data, function(key, value) {
                        $('.unit').append('<option value="' + key + '">' + value +
                            '</option>');
                    });

                    $('.unit').trigger('change.select2');
                }
            });
        });
    </script>
@endpush
