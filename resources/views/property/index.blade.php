@extends('layouts.app')
@section('page-title')
    {{ __('Property') }}
@endsection


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Property') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row g-3 align-items-end">

                        <div class="col-lg-4 col-md-6">
                            <input type="text" id="filter_search" class="form-control "
                                placeholder="{{ __('Property name...') }}">
                        </div>

                        <div class="col-lg-4 col-md-6">
                            {{ Form::select('type', $types, request('type'), [
                                'class' => 'form-control hidesearch',
                                'id' => 'filter_type',
                            ]) }}
                        </div>
                        <div class="col-lg-1 col-md-6">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary" id="btn_search" data-bs-toggle="tooltip"
                                    title="{{ __('Search') }}">
                                    <i class="ti ti-search"></i>
                                </button>
                                <button type="button" class="btn btn-primary" id="btn_reset" data-bs-toggle="tooltip" onclick="window.location.reload();"
                                    title="{{ __('Reset') }}">
                                    <i class="ti ti-refresh"></i>
                                </button>
                            </div>
                        </div>

                        @can('create property')
                            <div class="col-lg-auto col-md-12 ms-lg-auto">
                                <a class="btn btn-secondary w-100 w-lg-auto" href="{{ route('property.create') }}">
                                    <i class="ti ti-circle-plus me-1"></i> {{ __('Create Property') }}
                                </a>
                            </div>
                        @endcan
                    </div>
                </div>

                <div class="card-body" id="property-list-wrapper">
                    @include('property.property_list', ['properties' => $properties])
                </div>

            </div>

        </div>
    </div>
@endsection
@push('script-page')
    <script>
        var baseUrl = "{{ route('property.index') }}";

        function loadPropertys(page) {
            page = page || 1;

            var search = $('#filter_search').val();
            var type = $('#filter_type').val();
            var unit = $('#filter_unit').val();

            $.ajax({
                url: baseUrl,
                type: 'GET',
                data: {
                    page: page,
                    search: search,
                    type: type,
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(data) {
                    $('#property-list-wrapper').html(data.html);
                    if (typeof feather !== 'undefined') feather.replace();
                },
                error: function() {
                    $('#property-list-wrapper').html(`
                <div class="text-center py-5">
                    <i class="ti ti-alert-circle text-danger" style="font-size:48px;"></i>
                    <p class="text-danger mt-2">Something went wrong. Please try again.</p>
                </div>
            `);
                }
            });
        }

        // Pagination
        $(document).on('click', '#property-list-wrapper .pagination a', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            if (!href || href === '#') return;
            var page = new URL(href, window.location.origin).searchParams.get('page') || 1;
            loadPropertys(page);
        });

        // Search
        $('#btn_search').on('click', function() {
            loadPropertys(1);
        });

        // Enter key
        $('#filter_search').on('keydown', function(e) {
            if (e.key === 'Enter') loadPropertys(1);
        });

        // Reset
        $('#btn_reset').on('click', function() {
            $('#filter_search').val('');
            $('#filter_type').val('').trigger('change');
            loadPropertys(1);
        });
    </script>
@endpush
