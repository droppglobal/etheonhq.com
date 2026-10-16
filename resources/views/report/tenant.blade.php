@extends('layouts.app')

@section('page-title')
    {{ __('Tenant History Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Tenant History Report') }}</li>
@endsection

@push('script-page')
    <script>
        $(function() {
            $('#property_id').on('change', function() {
                let property_id = $(this).val();

                $('#unit_id').html(`<option value="">{{ __('Select Unit') }}</option>`);
                $('#tenant_id').html(`<option value="">{{ __('Select Tenant') }}</option>`);

                if (!property_id) return;

                $.get('{{ route('property.unit', ':id') }}'.replace(':id', property_id), function(data) {
                    let options = `<option value="">{{ __('Select Unit') }}</option>`;
                    $.each(data, function(key, value) {
                        options += `<option value="${key}">${value}</option>`;
                    });
                    $('#unit_id').html(options).trigger('change');
                });
            });

            $('.subbtn').on('click', function(e) {
                e.preventDefault();
                loadReport();
            });

            function loadReport() {
                $.get('{{ route('report.tenant') }}', {
                    property_id: $('#property_id').val(),
                    unit_id: $('#unit_id').val(),
                    tenant_id: $('#tenant_id').val(),
                }, function(response) {
                    $('#tenant-report').html(response.html);
                    if (typeof datatable === 'function') datatable();
                }).fail(function() {
                    console.error('Error loading tenant report');
                });
            }

            loadReport();
        });
    </script>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">

                <div class="card-header">
                    <div class="row align-items-center g-2 flex-wrap">


                        <div class="d-flex flex-wrap gap-3 align-items-end">

                            <div class="col-md-4">
                                {!! Form::select('property_id', $property, request('property_id'), [
                                    'id' => 'property_id',
                                    'class' => 'form-control select2',
                                ]) !!}
                            </div>

                            <div class="col-md-4">
                                {!! Form::select('unit_id', [], null, [
                                    'id' => 'unit_id',
                                    'class' => 'form-control select2',
                                    'placeholder' => __('Select Unit'),
                                ]) !!}
                            </div>

                            <div>
                                <button type="button" class="btn btn-secondary me-1 subbtn">
                                    <i class="ti ti-search align-text-bottom"></i>
                                </button>
                                <button type="button" class="btn btn-primary btn-rounded"
                                    onclick="window.location.reload();">
                                    <i class="ti ti-refresh align-text-bottom"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card-body pt-0">
                    <div id="tenant-report">

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
