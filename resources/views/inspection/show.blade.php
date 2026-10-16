@extends('layouts.app')

@section('page-title')
    {{ \App\Models\Inspection::types()[$inspection->type] ?? '' }} {{ __('Inspection') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inspection.index') }}">{{ __('Inspections') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ dateFormat($inspection->inspection_date) }}</li>
@endsection

@section('content')
    @php $conditions = \App\Models\Inspection::conditions(); @endphp
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"><div class="text-muted small">{{ __('Tenant') }}</div><div class="fw-semibold">{{ optional(optional($inspection->tenants)->user)->name }}</div></div>
                        <div class="col-md-3"><div class="text-muted small">{{ __('Property / Unit') }}</div><div class="fw-semibold">{{ optional($inspection->properties)->name }} / {{ optional($inspection->units)->name }}</div></div>
                        <div class="col-md-2"><div class="text-muted small">{{ __('Date') }}</div><div class="fw-semibold">{{ dateFormat($inspection->inspection_date) }}</div></div>
                        <div class="col-md-2"><div class="text-muted small">{{ __('Status') }}</div><div class="fw-semibold">{{ $inspection->status == 'completed' ? __('Completed') : __('Draft') }}</div></div>
                        <div class="col-md-2 text-end">
                            <a href="javascript:window.print()" class="btn btn-light-secondary btn-sm"><i class="ti ti-printer"></i> {{ __('Print') }}</a>
                            @if (\Auth::user()->type != 'tenant' && empty($inspection->tenant_signed_at))
                                @can('edit inspection')
                                    <a href="{{ route('inspection.edit', $inspection->id) }}" class="btn btn-secondary btn-sm"><i class="ti ti-edit"></i></a>
                                @endcan
                            @endif
                        </div>
                    </div>
                    @if ($baseline)
                        <div class="alert alert-light-info mt-3 mb-0">{{ __('Compared with the move-in inspection of') }} {{ dateFormat($baseline->inspection_date) }}. {{ __('Rows marked "Changed" are worse than at move-in.') }}</div>
                    @endif
                </div>
            </div>

            @foreach ($inspection->items->groupBy('area') as $area => $items)
                <div class="card">
                    <div class="card-header py-2"><h5 class="mb-0">{{ $area }}</h5></div>
                    <div class="card-body py-0">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th style="width:25%">{{ __('Item') }}</th>
                                    @if ($baseline)<th>{{ __('At Move-in') }}</th>@endif
                                    <th>{{ __('Condition') }}</th>
                                    <th>{{ __('Notes') }}</th>
                                    <th>{{ __('Photo') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    @php
                                        $before = $baselineItems->get(mb_strtolower($item->area . '|' . $item->item));
                                        $rank = ['good' => 0, 'fair' => 1, 'poor' => 2, 'damaged' => 3];
                                        $worse = $before && isset($rank[$item->condition], $rank[$before->condition]) && $rank[$item->condition] > $rank[$before->condition];
                                    @endphp
                                    <tr class="{{ $worse ? 'table-danger' : '' }}">
                                        <td class="fw-semibold">{{ $item->item }} @if ($worse)<span class="badge bg-danger ms-1">{{ __('Changed') }}</span>@endif</td>
                                        @if ($baseline)
                                            <td>
                                                @if ($before)
                                                    <span class="badge bg-light-{{ \App\Models\Inspection::conditionColor($before->condition) }}">{{ $conditions[$before->condition] ?? $before->condition }}</span>
                                                    @if (!empty($before->photo))
                                                        <a href="{{ fetch_file($before->photo, 'upload/inspection/') }}" target="_blank" class="ms-1"><i class="ti ti-photo"></i></a>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        @endif
                                        <td><span class="badge bg-light-{{ \App\Models\Inspection::conditionColor($item->condition) }}">{{ $conditions[$item->condition] ?? $item->condition }}</span></td>
                                        <td>{{ $item->notes }}</td>
                                        <td>
                                            @if (!empty($item->photo))
                                                <a href="{{ fetch_file($item->photo, 'upload/inspection/') }}" target="_blank"><img src="{{ fetch_file($item->photo, 'upload/inspection/') }}" style="height:40px;width:56px;object-fit:cover" class="rounded"></a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            @if (!empty($inspection->notes))
                <div class="card"><div class="card-body"><h6>{{ __('General Notes') }}</h6>{!! nl2br(e($inspection->notes)) !!}</div></div>
            @endif

            <div class="card">
                <div class="card-body">
                    <h6>{{ __('Tenant Confirmation') }}</h6>
                    @if (!empty($inspection->tenant_signed_at))
                        <p class="mb-0"><i class="ti ti-circle-check text-success"></i> {{ __('Signed by') }} <strong>{{ $inspection->tenant_signed_name }}</strong>
                            {{ __('on') }} {{ dateFormat($inspection->tenant_signed_at) }} {{ timeFormat($inspection->tenant_signed_at) }}
                            <span class="text-muted">(IP {{ $inspection->tenant_signed_ip }})</span></p>
                    @elseif (\Auth::user()->type == 'tenant')
                        {{ Form::open(['route' => ['inspection.sign', $inspection->id], 'method' => 'post']) }}
                        <p class="text-muted">{{ __('Please check the report above. Signing confirms you agree it reflects the condition of the unit.') }}</p>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                {{ Form::label('signed_name', __('Type your full name'), ['class' => 'form-label']) }}
                                {{ Form::text('signed_name', \Auth::user()->name, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                            <div class="col-md-4">
                                <label><input type="checkbox" name="agree" value="1" required> {{ __('I agree with this report') }}</label>
                            </div>
                            <div class="col-md-3"><button type="submit" class="btn btn-secondary w-100">{{ __('Sign') }}</button></div>
                        </div>
                        {{ Form::close() }}
                    @else
                        <p class="mb-0 text-muted">{{ $inspection->status == 'completed' ? __('Waiting for the tenant to sign.') : __('Complete the inspection so the tenant can review and sign it.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
