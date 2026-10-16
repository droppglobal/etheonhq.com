@extends('layouts.app')

@section('page-title')
    {{ __('Property Unit Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Property Unit Report') }}</li>
@endsection

@push('script-page')
    <script>
        $(function() {

            $('.subbtn').on('click', function(e) {
                e.preventDefault();
                loadReport();
            });

            function loadReport() {
                $.get('{{ route('report.property_unit') }}', {
                    property_id: $('#property_id').val(),
                }, function(response) {
                    $('#property-unit-report').html(response.html);
                    if (typeof datatable === 'function') datatable();
                }).fail(function() {
                    console.error('Error loading property unit report');
                });
            }

            // Initial load
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
                                    {{ Form::select('property_id', $property, request('property_id'), [
                                        'class' => 'form-control select2 property',
                                        'id' => 'property_id',
                                    ]) }}
                                </div>

                                <div class="col-auto d-flex gap-2">
                                    <button type="button" class="btn btn-secondary subbtn">
                                        <i class="ti ti-search"></i>
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="window.location.reload();">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                </div>

                        </div>
                    </div>

                </div>
                <div class="card-body pt-0">
                    <div id="property-unit-report"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
