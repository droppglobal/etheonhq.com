@extends('layouts.app')
@section('page-title')
    {{ __('Website Pages') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('front-home.index') }}">{{ __('Website') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Pages') }}</li>
@endsection
@section('card-action-btn')
    @if (Gate::check('edit front home page'))
        <a class="btn btn-secondary btn-sm ml-20" href="{{ route('website-pages.create') }}"><i class="ti-plus mr-5"></i>{{ __('Create Page') }}</a>
    @endif
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('Website Pages') }}</h5>
                            <small class="text-muted">{{ __('Extra pages for your website, such as About Us, Services or House Rules. Pages marked "In menu" appear in the website menu.') }}</small>
                        </div>
                        @if (Gate::check('edit front home page'))
                            <div class="col-auto">
                                <a class="btn btn-secondary" href="{{ route('website-pages.create') }}"><i class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create Page') }}</a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Address') }}</th>
                                    <th>{{ __('In menu') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-end">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pages as $page)
                                    @php $url = route('web.custom.page', [$code, $page->slug]); @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $page->title }}</td>
                                        <td><a href="{{ $url }}" target="_blank" class="small">/page/{{ $page->slug }} <i class="ti ti-external-link"></i></a></td>
                                        <td>{!! $page->show_in_menu ? '<span class="badge bg-light-success">' . __('Yes') . '</span>' : '<span class="badge bg-light-secondary">' . __('No') . '</span>' !!}</td>
                                        <td>{!! $page->is_active ? '<span class="badge bg-light-success">' . __('Published') . '</span>' : '<span class="badge bg-light-warning">' . __('Hidden') . '</span>' !!}</td>
                                        <td class="text-end">
                                            @if (Gate::check('edit front home page'))
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['website-pages.destroy', $page->id], 'id' => 'page-delete-form-' . $page->id]) !!}
                                                <a class="avtar avtar-xs btn-link-secondary text-secondary" href="{{ route('website-pages.edit', $page->id) }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"><i data-feather="edit"></i></a>
                                                <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"><i data-feather="trash-2"></i></a>
                                                {!! Form::close() !!}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="ti ti-file-plus f-36 d-block mb-2"></i>
                                            {{ __('No pages yet. Create an About Us or Services page to tell visitors more about you.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
