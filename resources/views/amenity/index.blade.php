@extends('layouts.app')
@section('page-title')
    {{ __('Property Amenity') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Property Amenity') }}</a>
        </li>
    </ul>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('Property Amenity List') }}</h5>
                        </div>
                        @if (Gate::check('create amenity'))
                            <div class="col-auto">
                                <a class="btn btn-secondary customModal" href="#" data-size="md"
                                    data-url="{{ route('amenity.create') }}" data-title="{{ __('Create Amenity') }}"> <i
                                        class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create Amenity') }}</a>

                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Image') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    @if (Gate::check('edit amenity') || Gate::check('delete amenity'))
                                        <th class="text-right">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($amenities as $amenity)
                                    <tr role="row">
                                        <td class="table-user">
                                            <img src="{{ fetch_file($amenity->image, 'upload/amenity/') }}"
                                                alt="" class="mr-2 amenity-img">

                                        </td>
                                        <td>
                                            {{ ucfirst($amenity->name) }}
                                        </td>
                                        <td>
                                             {{ substr($amenity->description, 0, 200) }}{{ !empty($amenity->description) ? '...' : '' }}
                                        </td>
                                        @if (Gate::check('edit amenity') || Gate::check('delete amenity'))
                                            <td class="text-right">
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['amenity.destroy', $amenity->id]]) !!}
                                                    @can('edit amenity')
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary customModal"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}" href="#"
                                                            data-url="{{ route('amenity.edit', $amenity->id) }}"
                                                            data-title="{{ __('Edit Amenity') }}"> <i
                                                                data-feather="edit"></i></a>
                                                    @endcan
                                                    @can('delete amenity')
                                                        <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"
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
