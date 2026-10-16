@extends('layouts.app')

@section('page-title')
    {{ __('Landlords') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Landlords') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ __('Landlords') }}</h5>
                        <small class="text-muted">{{ __('Property owners whose properties you manage. They log in to see their statements and payouts.') }}</small>
                    </div>
                    @can('create landlord')
                        <a href="#" class="btn btn-secondary customModal" data-size="lg" data-url="{{ route('landlord.create') }}"
                            data-title="{{ __('Add Landlord') }}"><i class="ti ti-circle-plus align-text-bottom"></i> {{ __('Add Landlord') }}</a>
                    @endcan
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th>{{ __('Properties') }}</th>
                                    <th>{{ __('Management Fee') }}</th>
                                    <th>{{ __('Balance Owed') }}</th>
                                    <th class="text-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($landlords as $landlord)
                                    <tr>
                                        <td><a href="{{ route('landlord.show', $landlord->id) }}" class="fw-semibold">{{ $landlord->name }}</a></td>
                                        <td>{{ $landlord->email }}<br><small class="text-muted">{{ $landlord->phone_number }}</small></td>
                                        <td>{{ $propertyCounts[$landlord->id] ?? 0 }}</td>
                                        <td>{{ (float) optional($profiles->get($landlord->id))->management_fee_percent }}%</td>
                                        <td class="{{ ($balances[$landlord->id] ?? 0) < 0 ? 'text-danger' : '' }}">{{ priceFormat($balances[$landlord->id] ?? 0) }}</td>
                                        <td class="text-right">
                                            <div class="cart-action">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['landlord.destroy', $landlord->id]]) !!}
                                                <a class="avtar avtar-xs btn-link-warning text-warning" href="{{ route('landlord.show', $landlord->id) }}"><i data-feather="eye"></i></a>
                                                @can('edit landlord')
                                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" href="#" data-size="lg"
                                                        data-url="{{ route('landlord.edit', $landlord->id) }}" data-title="{{ __('Edit Landlord') }}"><i data-feather="edit"></i></a>
                                                @endcan
                                                @can('delete landlord')
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
