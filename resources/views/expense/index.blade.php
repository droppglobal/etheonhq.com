@extends('layouts.app')

@section('page-title')
    {{ __('Expense') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Expense') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row g-3 align-items-end">

                        @if (auth()->user()->type != 'tenant')
                            <div class="col-lg-2 col-md-6">
                                {!! Form::select('property', $properties, null, [
                                    'class' => 'form-control select2 property',
                                    'id' => 'propertyId',
                                ]) !!}
                            </div>

                            <div class="col-lg-2 col-md-6">
                                {!! Form::select('unit', [], null, [
                                    'class' => 'form-control select2 unit',
                                    'id' => 'unitId',
                                    'placeholder' => __('Select Unit'),
                                ]) !!}
                            </div>

                            <div class="col-lg-2 col-md-6">
                                {!! Form::select('type', $types, null, ['class' => 'form-control select2 type']) !!}
                            </div>
                        @endif

                        <div class="col-lg-2 col-md-6">
                            {{ Form::text('date', request('date'), [
                                'class' => 'form-control  date',
                                'placeholder' => __('Select expense date'),
                                'id' => 'demo',
                                'autocomplete' => 'off',
                            ]) }}
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

                        @can('create expense')
                            <div class="col-lg-auto col-md-12 ms-lg-auto">
                                <a href="#" data-url="{{ route('expense.create') }}" class="btn btn-secondary customModal"
                                    data-size="lg" data-title="{{ __('Create Expense') }}">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Expense') }}
                                </a>
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
        $('#demo').daterangepicker({
            autoApply: true,
            autoUpdateInput: false,
            locale: {
                format: 'MM/DD/YYYY'
            }
        }, function(start, end) {
            $('#demo').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
        });

        $(document).ready(function() {
            $('.subbtn').trigger('click');
        });

        $(document).on('click', '.subbtn', function() {
            $.ajax({
                type: 'GET',
                url: '{{ route('expense.index') }}',
                data: {
                    property: $('.property').val(),
                    unit: $('#unitId').val(),
                    type: $('.type').val(),
                    date: $('#demo').val()
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

        $(document).on('change', '#propertyId', function() {

            let property_id = $(this).val();

            $('#unitId').html('<option value="">{{ __('Select Unit') }}</option>').trigger('change');

            if (!property_id) return;

            $.ajax({
                url: '{{ route('property.unit', ':id') }}'.replace(':id', property_id),
                type: 'GET',
                success: function(data) {

                    $.each(data, function(key, value) {
                        $('#unitId').append('<option value="' + key + '">' + value +
                            '</option>');
                    });

                    $('#unitId').trigger('change');
                }
            });
        });
    </script>
@endpush
