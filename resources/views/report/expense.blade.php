@extends('layouts.app')

@section('page-title')
    {{ __('Expense Report') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Expense Report') }}</li>
@endsection

@push('script-page')
    <script>
        $(function() {
            $('#demo').daterangepicker({
                autoApply: true,
                autoUpdateInput: false,
                locale: {
                    format: 'MM/DD/YYYY'
                }
            }, function(start, end) {
                $('#demo').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
            });

            $('#property_id').on('change', function() {
                let property_id = $(this).val();

                if (!property_id) {
                    resetUnitDropdown();
                    return;
                }

                let url = '{{ route('property.unit', ':id') }}'.replace(':id', property_id);

                $.get(url, function(data) {
                    let options = `<option value="">Select Unit</option>`;

                    $.each(data, function(key, value) {
                        options += `<option value="${key}">${value}</option>`;
                    });

                    $('#unit_id').html(options).trigger('change');
                }).fail(function() {
                    resetUnitDropdown();
                });
            });

            $('.subbtn').on('click', function(e) {
                e.preventDefault();
                loadReport();
            });

            function loadReport() {
                $.get('{{ route('report.expense') }}', {
                    property_id: $('#property_id').val(),
                    unit_id: $('#unit_id').val(),
                    date: $('#demo').val(),
                }, function(response) {
                    $('#expense-report').html(response.html);
                    if (typeof datatable === 'function') datatable();
                }).fail(function() {
                    console.error('Error loading report');
                });
            }

            function resetUnitDropdown() {
                $('#unit_id').html(`<option value="">Select Unit</option>`).trigger('change');
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
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-3 col-md-6">
                            {{ Form::select('property_id', $property, request('property_id'), [
                                'class' => 'form-control select2 property',
                                'id' => 'property_id',
                            ]) }}
                        </div>

                        <div class="col-lg-3 col-md-6">
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

                        <div class="col-lg-3 col-md-6">
                            {{ Form::text('date', request('date'), [
                                'class' => 'form-control  date',
                                'placeholder' => __('Select expense date'),
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

                <div class="card-body pt-0" id="expense-report">

                </div>
            </div>
        </div>
    </div>
@endsection
