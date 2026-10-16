@extends('layouts.app')

@section('page-title')
    {{ __('Vendors') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Vendors') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Vendors') }}</h5>
                    @can('create vendor')
                        <a href="#" class="btn btn-secondary customModal" data-size="lg" data-url="{{ route('vendor.create') }}"
                            data-title="{{ __('Add Vendor') }}"><i class="ti ti-circle-plus align-text-bottom"></i> {{ __('Add Vendor') }}</a>
                    @endcan
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Service') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th>{{ __('Jobs') }}</th>
                                    <th>{{ __('Total Spent') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vendors as $vendor)
                                    <tr>
                                        <td><a href="{{ route('vendor.show', $vendor->id) }}" class="fw-semibold">{{ $vendor->name }}</a>
                                            @if ($vendor->company)<br><small class="text-muted">{{ $vendor->company }}</small>@endif</td>
                                        <td>{{ $vendor->service ?: '-' }}</td>
                                        <td>{{ $vendor->phone }}<br><small class="text-muted">{{ $vendor->email }}</small></td>
                                        <td>{{ $jobs[$vendor->id] ?? 0 }}</td>
                                        <td>{{ priceFormat($spent[$vendor->id] ?? 0) }}</td>
                                        <td><span class="badge bg-light-{{ $vendor->is_active ? 'success' : 'secondary' }}">{{ $vendor->is_active ? __('Active') : __('Inactive') }}</span></td>
                                        <td class="text-right">
                                            <div class="cart-action">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['vendor.destroy', $vendor->id]]) !!}
                                                <a class="avtar avtar-xs btn-link-warning text-warning" href="{{ route('vendor.show', $vendor->id) }}"><i data-feather="eye"></i></a>
                                                @can('edit vendor')
                                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" href="#" data-size="lg"
                                                        data-url="{{ route('vendor.edit', $vendor->id) }}" data-title="{{ __('Edit Vendor') }}"><i data-feather="edit"></i></a>
                                                @endcan
                                                @can('delete vendor')
                                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#"><i data-feather="trash-2"></i></a>
                                                @endcan
                                                {!! Form::close() !!}
                                            </div>
                                        </td>
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
