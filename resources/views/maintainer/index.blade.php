@extends('layouts.app')
@section('page-title')
    {{ __('Maintainer') }}
@endsection
@php
    $profile = asset(Storage::url('upload/profile/avatar.png'));
@endphp

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Maintainer') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row g-3 align-items-end">

                        <div class="col-lg-3 col-md-6">

                            <input type="text" id="filter_search" class="form-control"
                                placeholder="{{ __('Name or phone number...') }}">
                        </div>

                        <div class="col-lg-3 col-md-6">

                            {{ Form::select('property', $property, request('property'), [
                                'class' => 'form-control select2',
                                'id' => 'filter_property',
                            ]) }}
                        </div>

                        <div class="col-lg-3 col-md-6">

                            {{ Form::select('type', $types, request('type'), [
                                'class' => 'form-control select2',
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

                        @can('create maintainer')
                            <div class="col-lg-auto col-md-12 ms-lg-auto">
                                <a class="btn btn-secondary customModal w-100 w-lg-auto" href="#"
                                    data-url="{{ route('maintainer.create') }}" data-title="{{ __('Create Maintainer') }}"
                                    data-size="lg">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Maintainer') }}
                                </a>
                            </div>
                        @endcan

                    </div>
                </div>

                <div class="card-body" id="tenant-list-wrapper">
                    @include('maintainer.maintainer_list', ['maintainers' => $maintainers])
                </div>
            </div>

        </div>
    </div>
@endsection
@push('script-page')
    <script>
        var baseUrl = "{{ route('maintainer.index') }}";

        function loadTenants(page) {
            page = page || 1;

            var search = $('#filter_search').val();
            var property = $('#filter_property').val();
            var type = $('#filter_type').val();

            $.ajax({
                url: baseUrl,
                type: 'GET',
                data: {
                    page: page,
                    search: search,
                    property: property,
                    type: type,
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(data) {
                    $('#tenant-list-wrapper').html(data.html);
                    if (typeof feather !== 'undefined') feather.replace();
                },
                error: function() {
                    $('#tenant-list-wrapper').html(`
                <div class="text-center py-5">
                    <i class="ti ti-alert-circle text-danger" style="font-size:48px;"></i>
                    <p class="text-danger mt-2">Something went wrong. Please try again.</p>
                </div>
            `);
                }
            });
        }

        // Pagination
        $(document).on('click', '#tenant-list-wrapper .pagination a', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            if (!href || href === '#') return;
            var page = new URL(href, window.location.origin).searchParams.get('page') || 1;
            loadTenants(page);
        });

        // Search
        $('#btn_search').on('click', function() {
            loadTenants(1);
        });

        $('#filter_search').on('keydown', function(e) {
            if (e.key === 'Enter') loadTenants(1);
        });

        $('#btn_reset').on('click', function() {
            $('#filter_search').val('');
            $('#filter_property').val('').trigger('change');
            $('#filter_type').val('').trigger('change');;
            loadTenants(1);
        });
    </script>
@endpush
