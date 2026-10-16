@extends('layouts.app')

@section('page-title')
    {{ __('Profit & Loss Report') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Profit & Loss Report') }}</li>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            let chart = null;
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

            function initChart() {
                let el = document.querySelector('#incomeExpense');

                if (!el) return;

                chart = new ApexCharts(el, {
                    chart: {
                        type: 'area',
                        height: 450,
                        toolbar: {
                            show: false
                        }
                    },
                    colors: ['#2ca58d', '#0a2342'],
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        show: true,
                        position: 'top'
                    },
                    stroke: {
                        width: 2,
                        curve: 'smooth'
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            opacityFrom: 0.5,
                            opacityTo: 0
                        }
                    },
                    series: [{
                            name: "{{ __('Total Income') }}",
                            data: []
                        },
                        {
                            name: "{{ __('Total Expense') }}",
                            data: []
                        }
                    ],
                    xaxis: {
                        categories: []
                    }
                });

                chart.render();
            }

            function loadReport() {
                $.get('{{ route('report.profit_loss') }}', {
                    property_id: $('#property_id').val(),
                    unit_id: $('#unit_id').val(),
                    year: $('#year').val(),
                }, function(response) {
                    $('#profit-loss-report').html(response.html);
                    if (!chart) {
                        initChart();
                    }
                    if (chart) {
                        chart.updateOptions({
                            series: [{
                                    name: "{{ __('Total Income') }}",
                                    data: response.income || []
                                },
                                {
                                    name: "{{ __('Total Expense') }}",
                                    data: response.expense || []
                                }
                            ],
                            xaxis: {
                                categories: response.labels || []
                            }
                        });
                    }

                    if (typeof datatable === 'function') datatable();

                }).fail(function() {
                    console.error('Error loading profit loss report');
                });
            }

            $('.subbtn').on('click', function(e) {
                e.preventDefault();
                loadReport();
            });

            initChart();
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
                                {!! Form::select('property_id', $property, request('property_id'), [
                                    'id' => 'property_id',
                                    'class' => 'form-control select2',
                                ]) !!}
                            </div>

                            <div class="col">
                                {!! Form::select('unit_id', [], null, [
                                    'id' => 'unit_id',
                                    'class' => 'form-control select2',
                                    'placeholder' => __('Select Unit'),
                                ]) !!}
                            </div>

                            <div class="col">
                                <select class="form-control select2" name="year" id="year" class="btn btn-secondary"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-title="{{ __('Select Year') }}">

                                    @foreach ($years as $yr)
                                        <option value="{{ $yr }}"
                                            {{ request('year', $year) == $yr ? 'selected' : '' }}>
                                            {{ $yr }}
                                        </option>
                                    @endforeach
                                </select>
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
                    <div id="profit-loss-report"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-12 col-md-12">
            <div class="card">

                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <h5 class="mb-1">{{ __('Analysis Report') }}</h5>
                            <p class="text-muted mb-2">{{ __('Income and Expense Overview') }}</p>
                        </div>

                    </div>
                    <div id="incomeExpense"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
