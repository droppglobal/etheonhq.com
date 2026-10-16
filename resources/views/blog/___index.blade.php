@extends('layouts.app')
@section('page-title')
    {{ __('Blog') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Blog') }}</li>
@endsection

@section('content')
    <div class="row">
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
                                    <th>{{ __('Image') }}</th>
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
                                        <td> <img
                                                src="{{ asset(Storage::url('upload/blog/image')) . '/' . $image }}"
                                                alt="{{ $blog->name }}" class="img-prod" /></td>
                                        <td> {{ ucfirst($blog->title) }} </td>
                                        <td>{{ \Illuminate\Support\Str::limit(strip_tags($blog->content), 50, '...') }}</td>
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
