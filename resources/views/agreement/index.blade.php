@extends('layouts.app')

@section('page-title')
    {{ __('Agreement') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Agreement') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row g-3 align-items-end">

                        <div class="col-lg-4 col-md-6">
                            {!! Form::select('property', $properties, null, ['class' => 'form-control select2 property']) !!}
                        </div>

                        <div class="col-lg-4 col-md-6">
                            {!! Form::select('unit', [], null, ['class' => 'form-control select2 unit', 'placeholder' => __('Select Unit')]) !!}
                        </div>
                        <div class="col-lg-1 col-md-6">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary subbtn">
                                    <i class="ti ti-search align-text-bottom"></i>
                                </button>
                                <button type="button" class="btn btn-primary btn-rounded"
                                    onclick="window.location.reload();">
                                    <i class="ti ti-refresh align-text-bottom"></i>
                                </button>
                            </div>
                        </div>
                        @if (Gate::check('create agreement'))
                            <div class="col-lg-auto col-md-12 ms-lg-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="{{ route('agreement.create') }}" data-title="{{ __('Create Agreement') }}">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Agreement') }}
                                </a>
                            </div>
                        @endif

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
                url: '{{ route('agreement.index') }}',
                data: {
                    property: $('.property').val(),
                    unit: $('.unit').val(),
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
