@extends('layouts.app')

@section('page-title')
    {{ __('Income Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Income Report') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2 flex-wrap">
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-3 align-items-end">

                                <div class="col">
                                    {{ Form::select('property_id', $property, request('property_id'), [
                                        'class' => 'form-control select2 property',
                                        'id' => 'property_id',
                                    ]) }}
                                </div>

                                <div class="col">
                                    <div class="unit_div">
                                        <select class="form-control select2 unit" id="unit_id" name="unit_id">
                                            <option value="">{{ __('Select Unit') }}</option>
                                            @foreach ($units as $id => $name)
                                                <option value="{{ $id }}"
                                                    {{ request('unit_id') == $id ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="{{ __('Select Invoice Month') }}">
                                    {{ Form::month('month', request('month'), [
                                        'class' => 'form-control month',
                                    ]) }}
                                </div>
                                <div class="col">
                                    {{ Form::text('date', request('date'), [
                                        'class' => 'form-control  date',
                                        'placeholder' => __('Select end date'),
                                        'id' => 'demo',
                                        'autocomplete' => 'off',
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

                </div>

                <div class="card-body pt-0" id="invoice-report">

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

        $(function() {
            $('#property_id').on('change', function() {
                let property_id = $(this).val();

                if (!property_id) {
                    resetUnitDropdown();
                    return;
                }

                let url = '{{ route('property.unit', ':id') }}'.replace(':id', property_id);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(data) {
                        let options = `<option value="">Select Unit</option>`;

                        $.each(data, function(key, value) {
                            options += `<option value="${key}">${value}</option>`;
                        });

                        $('#unit_id').html(options).trigger('change');
                    },
                    error: function() {
                        resetUnitDropdown();
                    }
                });
            });

            $('.subbtn').on('click', function() {
                loadReport();
            });

            function loadReport() {

                let data = {
                    property_id: $('#property_id').val(),
                    unit_id: $('#unit_id').val(),
                    month: $('.month').val(),
                    date: $('#demo').val(),
                };

                $.ajax({
                    url: '{{ route('report.income') }}',
                    method: 'GET',
                    data: data,
                    success: function(response) {
                        $('#invoice-report').html(response.html);
                        if (typeof datatable === 'function') {
                            datatable();
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Error loading report';
                        if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        console.error(msg);
                    }
                });
            }

            function resetUnitDropdown() {
                $('#unit_id').html(`<option value="">Select Unit</option>`).trigger('change');
            }

            loadReport();
        });
    </script>
@endpush
