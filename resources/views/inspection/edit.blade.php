@extends('layouts.app')

@section('page-title')
    {{ \App\Models\Inspection::types()[$inspection->type] ?? '' }} {{ __('Inspection') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inspection.index') }}">{{ __('Inspections') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Edit') }}</li>
@endsection

@section('content')
    @php $conditions = \App\Models\Inspection::conditions(); @endphp
    {{ Form::model($inspection, ['route' => ['inspection.update', $inspection->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"><div class="text-muted small">{{ __('Tenant') }}</div><div class="fw-semibold">{{ optional(optional($inspection->tenants)->user)->name }}</div></div>
                        <div class="col-md-3"><div class="text-muted small">{{ __('Property / Unit') }}</div><div class="fw-semibold">{{ optional($inspection->properties)->name }} / {{ optional($inspection->units)->name }}</div></div>
                        <div class="col-md-3">
                            {{ Form::label('inspection_date', __('Date'), ['class' => 'form-label small text-muted mb-0']) }}
                            {{ Form::date('inspection_date', null, ['class' => 'form-control form-control-sm', 'required' => 'required']) }}
                        </div>
                        <div class="col-md-3"><div class="text-muted small">{{ __('Status') }}</div><div class="fw-semibold">{{ $inspection->status == 'completed' ? __('Completed') : __('Draft') }}</div></div>
                    </div>
                </div>
            </div>

            @foreach ($inspection->items->groupBy('area') as $area => $items)
                <div class="card">
                    <div class="card-header py-2"><h5 class="mb-0">{{ $area }}</h5></div>
                    <div class="card-body py-2">
                        @foreach ($items as $item)
                            <div class="row g-2 align-items-center py-2 border-bottom">
                                <div class="col-md-3 fw-semibold">{{ $item->item }}</div>
                                <div class="col-md-2">
                                    {{ Form::select('items[' . $item->id . '][condition]', $conditions, $item->condition, ['class' => 'form-control form-control-sm']) }}
                                </div>
                                <div class="col-md-3">
                                    {{ Form::text('items[' . $item->id . '][notes]', $item->notes, ['class' => 'form-control form-control-sm', 'placeholder' => __('Notes')]) }}
                                </div>
                                <div class="col-md-3 d-flex align-items-center gap-2">
                                    @if (!empty($item->photo))
                                        <a href="{{ fetch_file($item->photo, 'upload/inspection/') }}" target="_blank"><img src="{{ fetch_file($item->photo, 'upload/inspection/') }}" style="height:36px;width:48px;object-fit:cover" class="rounded"></a>
                                    @endif
                                    <input type="file" name="photos[{{ $item->id }}]" accept=".jpg,.jpeg,.png" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-1 text-end">
                                    <label class="small text-danger"><input type="checkbox" name="items[{{ $item->id }}][remove]" value="1"> {{ __('Remove') }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="card">
                <div class="card-header py-2"><h5 class="mb-0">{{ __('Add items') }}</h5></div>
                <div class="card-body py-2">
                    @for ($i = 0; $i < 3; $i++)
                        <div class="row g-2 py-1">
                            <div class="col-md-3">{{ Form::text('new_items[' . $i . '][area]', null, ['class' => 'form-control form-control-sm', 'placeholder' => __('Room / Area')]) }}</div>
                            <div class="col-md-3">{{ Form::text('new_items[' . $i . '][item]', null, ['class' => 'form-control form-control-sm', 'placeholder' => __('Item')]) }}</div>
                            <div class="col-md-2">{{ Form::select('new_items[' . $i . '][condition]', $conditions, 'good', ['class' => 'form-control form-control-sm']) }}</div>
                            <div class="col-md-4">{{ Form::text('new_items[' . $i . '][notes]', null, ['class' => 'form-control form-control-sm', 'placeholder' => __('Notes')]) }}</div>
                        </div>
                    @endfor
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    {{ Form::label('notes', __('General Notes'), ['class' => 'form-label']) }}
                    {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2]) }}
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="submit" name="action" value="save" class="btn btn-light-secondary">{{ __('Save Draft') }}</button>
                        <button type="submit" name="action" value="complete" class="btn btn-secondary">{{ __('Save & Complete') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{ Form::close() }}
@endsection
