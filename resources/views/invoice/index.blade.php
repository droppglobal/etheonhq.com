@extends('layouts.app')

@section('page-title')
    {{ __('Invoice') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Invoice') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">

                <div class="card-header">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-2 col-md-6">
                            {!! Form::select('property', $properties, request('property'), ['class' => 'form-control select2 property']) !!}
                        </div>

                        <div class="col-lg-2 col-md-6">
                            {!! Form::select('unit', [], null, ['class' => 'form-control select2 unit', 'placeholder' => __('Select Unit')]) !!}
                        </div>
                        <div class="col-lg-2 col-md-6">
                            {!! Form::select('tenant', [], null, [
                                'class' => 'form-control select2 tenant',
                                'placeholder' => __('Select Tenant'),
                            ]) !!}
                        </div>
                        <div class="col-lg-2 col-md-6" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="{{ __('Select Invoice Month') }}">
                            {{ Form::month('month', null, [
                                'class' => 'form-control month',
                                'placeholder' => __('Select month'),
                            ]) }}
                        </div>

                        <div class="col-lg-1 col-md-6">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-secondary subbtn">
                                    <i class="ti ti-search align-text-bottom"></i>
                                </button>
                                <button type="button" class="btn btn-primary btn-rounded"
                                    onclick="window.location.reload();">
                                    <i class="ti ti-refresh align-text-bottom"></i>
                                </button>
                            </div>
                        </div>
                        @can('create invoice')
                            <div class="col-lg-auto col-md-12 ms-lg-auto d-flex flex-wrap gap-2">
                                @if (!in_array(\Auth::user()->type, ['tenant', 'maintainer', 'landlord']))
                                    <a href="{{ route('invoice.generate') }}" class="btn btn-light-secondary">
                                        <i class="ti ti-files align-text-bottom"></i> {{ __('Generate Monthly Rent') }}</a>
                                @endif
                                <a href="{{ route('invoice.create') }}" class="btn btn-secondary" data-size="lg">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Invoice') }}</a>
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="filter-section"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script-page')
    <script>
        $(document).ready(function() {
            $('.subbtn').trigger('click');
        });

        $(document).on('click', '.subbtn', function() {
            $.ajax({
                type: 'GET',
                url: '{{ route('invoice.index') }}',
                data: {
                    property: $('.property').val(),
                    unit: $('.unit').val(),
                    tenant: $('.tenant').val(),
                    month: $('.month').val()
                },
                success: function(response) {
                    $('.filter-section').html(response.html);
                    feather.replace();
                    datatable();
                    $('[data-bs-toggle="tooltip"]').tooltip();
                },
                error: function() {
                    $('.filter-section').html(`
                    <div class="text-center text-danger py-3">
                        {{ __('Failed to load data.') }}
                    </div>
                `);
                }
            });
            return false;
        });
    </script>
    <script>
        $(document).ready(function() {

            $(document).on('change', '.property', function() {

                let property_id = $(this).val();

                $('.unit').html('<option value="">Select Unit</option>').trigger('change');
                $('.tenant').html('<option value="">Select Tenant</option>').trigger('change');

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

            $(document).on('change', '.unit', function() {

                let unit_id = $(this).val();

                $('.tenant').html('<option value="">Select Tenant</option>').trigger('change');

                if (!unit_id) return;

                $.ajax({
                    url: '{{ route('unit.by.tenant', ':id') }}'.replace(':id', unit_id),
                    type: 'GET',
                    success: function(data) {

                        $.each(data, function(key, value) {
                            $('.tenant').append('<option value="' + key + '">' + value +
                                '</option>');
                        });

                        $('.tenant').trigger('change.select2');
                    }
                });
            });

        });
    </script>
@endpush
