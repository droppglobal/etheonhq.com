@extends('layouts.app')

@section('page-title')
    {{ __('Inspections') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Inspections') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Inspections') }}</h5>
                    @can('create inspection')
                        <a href="#" class="btn btn-secondary customModal" data-size="md" data-url="{{ route('inspection.create') }}"
                            data-title="{{ __('New Inspection') }}"><i class="ti ti-circle-plus align-text-bottom"></i> {{ __('New Inspection') }}</a>
                    @endcan
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Tenant') }}</th>
                                    <th>{{ __('Property') }}</th>
                                    <th>{{ __('Issues') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Tenant Signed') }}</th>
                                    <th class="text-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inspections as $inspection)
                                    @php $issues = $inspection->items->whereIn('condition', ['poor', 'damaged'])->count(); @endphp
                                    <tr>
                                        <td>{{ dateFormat($inspection->inspection_date) }}</td>
                                        <td>{{ \App\Models\Inspection::types()[$inspection->type] ?? $inspection->type }}</td>
                                        <td>{{ optional(optional($inspection->tenants)->user)->name ?? '-' }}</td>
                                        <td>{{ optional($inspection->properties)->name }} / {{ optional($inspection->units)->name }}</td>
                                        <td>
                                            @if ($issues)
                                                <span class="badge bg-light-danger">{{ $issues }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-light-{{ $inspection->status == 'completed' ? 'success' : 'secondary' }}">{{ $inspection->status == 'completed' ? __('Completed') : __('Draft') }}</span></td>
                                        <td>{{ $inspection->tenant_signed_at ? dateFormat($inspection->tenant_signed_at) : '-' }}</td>
                                        <td class="text-right">
                                            <div class="cart-action">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['inspection.destroy', $inspection->id]]) !!}
                                                <a class="avtar avtar-xs btn-link-warning text-warning" href="{{ route('inspection.show', $inspection->id) }}"><i data-feather="eye"></i></a>
                                                @if (\Auth::user()->type != 'tenant')
                                                    @can('edit inspection')
                                                        @if (empty($inspection->tenant_signed_at))
                                                            <a class="avtar avtar-xs btn-link-secondary text-secondary" href="{{ route('inspection.edit', $inspection->id) }}"><i data-feather="edit"></i></a>
                                                        @endif
                                                    @endcan
                                                    @can('delete inspection')
                                                        <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#"><i data-feather="trash-2"></i></a>
                                                    @endcan
                                                @endif
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
