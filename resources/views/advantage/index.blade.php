@extends('layouts.app')
@section('page-title')
    {{ __('Property Advantage') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Property Advantage') }}</a>
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
                            <h5>{{ __('Property Advantage List') }}</h5>
                        </div>
                        @if (Gate::check('create advantage'))
                            <div class="col-auto">
                                <a class="btn btn-secondary customModal" href="#" data-size="md"
                                    data-url="{{ route('advantage.create') }}" data-title="{{ __('Create Advantage') }}"> <i
                                        class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create Advantage') }}</a>

                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    @if (Gate::check('edit advantage') || Gate::check('delete advantage'))
                                        <th class="text-right">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($advantages as $advantage)
                                    <tr role="row">

                                        <td>
                                            {{ ucfirst($advantage->name) }}
                                        </td>
                                        <td>
                                             {{ substr($advantage->description, 0, 200) }}{{ !empty($advantage->description) ? '...' : '' }}
                                        </td>
                                        @if (Gate::check('edit advantage') || Gate::check('delete advantage'))
                                            <td class="text-right">
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['advantage.destroy', $advantage->id]]) !!}
                                                    @can('edit advantage')
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary customModal"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}" href="#"
                                                            data-url="{{ route('advantage.edit', $advantage->id) }}"
                                                            data-title="{{ __('Edit Advantage') }}"> <i
                                                                data-feather="edit"></i></a>
                                                    @endcan
                                                    @can('delete advantage')
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
