@extends('layouts.app')

@section('page-title')
    {{ __('Maintenance Report') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Maintenance Report') }}</li>
@endsection

@push('script-page')
    <script>
        $(function() {
            $('#property_id').on('change', function() {
                let property_id = $(this).val();

                $('#unit_id').html(`<option value="">{{ __('Select Unit') }}</option>`);

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
                $.get('{{ route('report.maintenance') }}', {
                    property_id: $('#property_id').val(),
                    unit_id: $('#unit_id').val(),
                    tenant_id: $('#tenant_id').val(),
                    status: $('status').val()
                }, function(response) {
                    $('#maintenance-report').html(response.html);
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

                            <div class="col">
                                {{ Form::select('tenant_id', $tenant_options, request('tenant_id'), [
                                    'class' => 'form-control select2',
                                    'id' => 'tenant_id',
                                ]) }}
                            </div>
                            <div class="col">
                                {{ Form::select('property_id', $property, request('property_id'), [
                                    'class' => 'form-control select2',
                                    'id' => 'property_id',
                                ]) }}
                            </div>

                            <div class="col">

                                <div class="unit_div">
                                    <select class="form-control select2 unit" id="unit_id" name="unit_id">
                                        <option value="">{{ __('Select Unit') }}</option>
                                        @if (!empty($units))
                                            @foreach ($units as $id => $name)
                                                <option value="{{ $id }}"
                                                    {{ request('unit_id') == $id ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col">
                                {{ Form::select('status', $status, request('status'), [
                                    'class' => 'form-control hidesearch',
                                    'placeholder' => __('Select Status'),
                                ]) }}
                            </div>
                            <div>
                                <button type="button" class="btn btn-secondary subbtn">
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
                    <div id="maintenance-report">

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
