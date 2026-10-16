@extends('layouts.app')
@section('page-title')
    {{ __('Units') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Units') }}</li>
@endsection


@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-3 col-md-6">
                            {{ Form::select('property', $properties, request('property'), [
                                'class' => 'form-control select2 property',
                                'id' => 'property_id',
                            ]) }}
                        </div>

                        <div class="col-lg-3 col-md-6">
                            {{ Form::select('unit', [], request('unit'), [
                                'class' => 'form-control select2 unit',
                                'id' => 'unit_id',
                                'placeholder' => __('Select Unit'),
                            ]) }}
                        </div>
                        <div class="col-lg-3 col-md-6">
                            {!! Form::select('status', $status, request('status'), ['class' => 'form-control hidesearch status']) !!}
                        </div>


                        <div class="col-lg-1 col-md-6">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary subbtn" id="btn_search" data-bs-toggle="tooltip"
                                    title="{{ __('Search') }}">
                                    <i class="ti ti-search"></i>
                                </button>
                                <button type="button" class="btn btn-primary" id="btn_reset" data-bs-toggle="tooltip" onclick="window.location.reload();"
                                    title="{{ __('Reset') }}">
                                    <i class="ti ti-refresh"></i>
                                </button>
                            </div>
                        </div>

                        @if (Gate::check('create unit'))
                           <div class="col-lg-auto col-md-12 ms-lg-auto d-flex flex-wrap gap-2 justify-content-lg-end">
                                <a href="#" class="btn btn-light-secondary customModal" data-size="lg"
                                    data-url="{{ route('unit.bulk.create') }}" data-title="{{ __('Add Many Units') }}">
                                    <i class="ti ti-layout-grid-add align-text-bottom"></i> {{ __('Add Many Units') }}
                                </a>
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="{{ route('unit.direct-create') }}" data-title="{{ __('Create Unit') }}" >
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Unit') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-body pt-0 filter-section">

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
            let tenant = $('.tenant').val();
            let property = $('.property').val();
            let status = $('.status').val();


            $.ajax({
                type: "GET",
                url: '{{ route('unit.index') }}',
                data: {
                    status: status,
                    tenant: tenant,
                    property: property
                },
                success: function(response) {
                    $('.filter-section').html(response.html);
                    datatable();
                }
            });

            return false;

        });
    </script>
@endpush
