@extends('layouts.app')
@section('page-title')
    {{ __('Frontend Settings') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Frontend Settings') }}</li>
@endsection
@php
    $profile = asset(Storage::url('upload/profile'));
    $settings = settings();
    $activeTab = session('tab', 'profile_tab_' . ($additionals->first()->id ?? 0));
@endphp
@push('script-page')
    <script>
        $('.location').on('click', '.location_list_remove', function() {
            if ($('.location_list').length > 1) {
                $(this).closest('.location_remove').remove();
            }
        });
        $('.location').on('click', '.location_clone', function() {
            var clonedlocation = $(this).closest('.location').find('.location_list').first().clone();
            clonedlocation.find('input[type="text"]').val('');
            $(this).closest('.location').find('.location_list_results').append(clonedlocation);
        });
    </script>
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="row setting_page_cnt">
                        <div class="col-lg-4">
                            <ul class="nav flex-column nav-tabs account-tabs mb-3" id="myTab" role="tablist">
                                @foreach ($additionals as $section_key => $section)
                                    @php
                                        $section->content_value = !empty($section->content_value)
                                            ? json_decode($section->content_value, true)
                                            : [];
                                    @endphp
                                    <li class="nav-item">
                                        <a class="nav-link {{ empty($activeTab) || $activeTab == 'profile_tab_' . $section->id ? ' active ' : '' }}"
                                            id="profile-tab-{{ $section->id }}" data-bs-toggle="tab"
                                            href="#profile_tab_{{ $section->id }}" role="tab" aria-selected="true">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ti-view-list me-2 f-20"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h5 class="mb-0">
                                                        {{ !empty($section->content_value['name']) ? $section->content_value['name'] : $section->title }}
                                                    </h5>
                                                    <small class="text-muted"> {{ $section->title }}
                                                        {{ __('Section Settings') }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-lg-8">
                            @if (Gate::check('edit additional'))
                                <div class="tab-content">
                                    @foreach ($additionals as $section)
                                        <div class="tab-pane {{ empty($activeTab) || $activeTab == 'profile_tab_' . $section->id ? ' active show ' : '' }}"
                                            id="profile_tab_{{ $section->id }}" role="tabpanel"
                                            aria-labelledby="footer_column_1">
                                            {{ Form::model($section, ['route' => ['additional.update', $section->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                                        {{ Form::text('content_value[name]', !empty($section->content_value['name']) ? $section->content_value['name'] : $section->title, ['class' => 'form-control', 'placeholder' => __('Enter Section name')]) }}
                                                    </div>
                                                </div>


                                                @if ($section->section == 'Section 0')
                                                    <div class="col-md-6 form-group">
                                                        {{ Form::label('enabled_email', __('Section Enabled'), ['class' => 'form-label']) }}
                                                        <div class="form-check form-switch">
                                                            {{ Form::hidden('content_value[section_enabled]', 'deactive') }}
                                                            {{ Form::checkbox('content_value[section_enabled]', 'active', !empty($section->content_value['section_enabled']) && $section->content_value['section_enabled'] == 'active' ? true : false, ['class' => 'form-check-input', 'role' => 'switch', 'id' => 'section_enabled']) }}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
                                                        {{ Form::text('content_value[title]', !empty($section->content_value['title']) ? $section->content_value['title'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Section name')]) }}
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        {{ Form::label('sub_title', __('Sub Title'), ['class' => 'form-label']) }}
                                                        {{ Form::text('content_value[sub_title]', !empty($section->content_value['sub_title']) ? $section->content_value['sub_title'] : '', ['class' => 'form-control', 'placeholder' => __('Enter sub title')]) }}
                                                    </div>

                                                    <div class="col-md-4 form-group">
                                                        {{ Form::label('banner_image1', __('Main Image'), ['class' => 'form-label']) }}
                                                        <a href="{{ asset(Storage::url($section->content_value['banner_image1_path'])) }}"
                                                            target="_blank"><i class="ti ti-eye ms-2 f-15"></i></a>
                                                        {{ Form::file('content_value[banner_image1]', ['class' => 'form-control']) }}
                                                    </div>
                                                @endif


                                                {{-- @if ($section->section == 'Section 1')
                                                    <div class="col-md-6 form-group">
                                                        {{ Form::label('enabled_email', __('Section Enabled'), ['class' => 'form-label']) }}
                                                        <div class="form-check form-switch">
                                                            {{ Form::hidden('content_value[section_enabled]', 'deactive') }}
                                                            {{ Form::checkbox('content_value[section_enabled]', 'active', !empty($section->content_value['section_enabled']) && $section->content_value['section_enabled'] == 'active' ? true : false, ['class' => 'form-check-input', 'role' => 'switch', 'id' => 'section_enabled']) }}
                                                        </div>
                                                    </div>
                                                @endif --}}

                                                @if ($section->section == 'Section 2')
                                                    <div class="col-md-6 form-group">
                                                        {{ Form::label('enabled_email', __('Section Enabled'), ['class' => 'form-label']) }}
                                                        <div class="form-check form-switch">
                                                            {{ Form::hidden('content_value[section_enabled]', 'deactive') }}
                                                            {{ Form::checkbox('content_value[section_enabled]', 'active', !empty($section->content_value['section_enabled']) && $section->content_value['section_enabled'] == 'active' ? true : false, ['class' => 'form-check-input', 'role' => 'switch', 'id' => 'section_enabled']) }}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        {{ Form::label('sec2_title', __('Title'), ['class' => 'form-label']) }}
                                                        {{ Form::text('content_value[sec2_title]', !empty($section->content_value['sec2_title']) ? $section->content_value['sec2_title'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Section name')]) }}
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        {{ Form::label('sec2_sub_title', __(key: 'Sub Title'), ['class' => 'form-label']) }}
                                                        {{ Form::text('content_value[sec2_sub_title]', !empty($section->content_value['sec2_sub_title']) ? $section->content_value['sec2_sub_title'] : '', ['class' => 'form-control', 'placeholder' => __('Enter sub title')]) }}
                                                    </div>

                                                    <div class="col-md-4 form-group">
                                                        {{ Form::label('sec2_banner_image', __('Banner Image'), ['class' => 'form-label']) }}
                                                        <a href="{{ asset(Storage::url($section->content_value['sec2_banner_image_path'])) }}"
                                                            target="_blank"><i class="ti ti-eye ms-2 f-15"></i></a>
                                                        {{ Form::file('content_value[sec2_banner_image]', ['class' => 'form-control']) }}
                                                    </div>
                                                @endif

                                                @if ($section->section == 'Section 3')
                                                    <div class="col-md-6 form-group">
                                                        {{ Form::label('enabled_email', __('Section Enabled'), ['class' => 'form-label']) }}
                                                        <div class="form-check form-switch">
                                                            {{ Form::hidden('content_value[section_enabled]', 'deactive') }}
                                                            {{ Form::checkbox('content_value[section_enabled]', 'active', !empty($section->content_value['section_enabled']) && $section->content_value['section_enabled'] == 'active' ? true : false, ['class' => 'form-check-input', 'role' => 'switch', 'id' => 'section_enabled']) }}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        {{ Form::label('sec3_title', __('Title'), ['class' => 'form-label']) }}
                                                        {{ Form::text('content_value[sec3_title]', !empty($section->content_value['sec3_title']) ? $section->content_value['sec3_title'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Section name')]) }}
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        {{ Form::label('sec3_sub_title', __('Sub Title'), ['class' => 'form-label']) }}
                                                        {{ Form::text('content_value[sec3_sub_title]', !empty($section->content_value['sec3_sub_title']) ? $section->content_value['sec3_sub_title'] : '', ['class' => 'form-control', 'placeholder' => __('Enter sub title')]) }}
                                                    </div>

                                                    <div class="col-md-4 form-group">
                                                        {{ Form::label('sec3_banner_image', __('Banner Image'), ['class' => 'form-label']) }}
                                                        <a href="{{ asset(Storage::url($section->content_value['sec3_banner_image_path'])) }}"
                                                        target="_blank"><i class="ti ti-eye ms-2 f-15"></i></a>
                                                        {{ Form::file('content_value[sec3_banner_image]', ['class' => 'form-control']) }}
                                                    </div>
                                                @endif





                                            </div>

                                            <div class="row mt-3">
                                                <div class="col-6">


                                                </div>
                                                <div class="col-6 text-end">
                                                    <input type="hidden" name="tab"
                                                        value="profile_tab_{{ $section->id }}">
                                                    {{ Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded']) }}
                                                </div>
                                            </div>
                                            {{ Form::close() }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('Blog') }}</h5>
                        </div>
                        @can('create blog')
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="xl"
                                    data-url="{{ route('blog.create') }}" data-title="{{ __('Create New blog') }}">
                                    <i class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create blog') }}</a>
                            </div>
                        @endcan

                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th class="w-20">{{ __('Image') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Content') }}</th>
                                    <th>{{ __('Enable') }}</th>
                                    @if (Gate::check('edit blog') || Gate::check('delete blog'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($blogs as $blog)
                                    @if (!empty($blog->image) && !empty($blog->image))
                                        @php $image= $blog->image; @endphp
                                    @else
                                        @php $image= 'default.png'; @endphp
                                    @endif
                                    <tr>
                                        <td class="w-20"> <img src="{{ asset(Storage::url('upload/blog/image')) . '/' . $image }}"
                                                alt="{{ $blog->name }}" style="width:60px; height:60px;" /></td>
                                        <td> {{ ucfirst($blog->title) }} </td>
                                        <td>{{ \Illuminate\Support\Str::limit(strip_tags($blog->content), 50, '...') }}
                                        </td>
                                        <td>

                                            @if ($blog->enabled == 1)
                                                <span class="d-inline badge text-bg-success">{{ __('Enable') }}</span>
                                            @else
                                                <span class="d-inline badge text-bg-danger">{{ __('Disable') }}</span>
                                            @endif

                                        </td>
                                        @if (Gate::check('edit blog') || Gate::check('delete blog'))
                                            <td>
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['blog.destroy', $blog->id]]) !!}
                                                    @can('edit blog')
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary customModal"
                                                            data-bs-toggle="tooltip" data-size="lg"
                                                            data-bs-original-title="{{ __('Edit') }}" href="#"
                                                            data-url="{{ route('blog.edit', $blog->id) }}"
                                                            data-title="{{ __('Edit blog') }}"> <i data-feather="edit"></i></a>
                                                    @endcan
                                                    @can('delete blog')
                                                        <a class=" avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Detete') }}" href="#"> <i
                                                                data-feather="trash-2"></i></a>
                                                    @endcan
                                                    {!! Form::close() !!}
                                                </div>

                                            </td>
                                        @endif
                                    </tr>
                                @endforeach


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
