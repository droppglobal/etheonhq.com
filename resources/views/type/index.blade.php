@extends('layouts.app')
@section('page-title')
    {{ __('Types') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Types') }}</a>
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
                            <h5>{{ __('Type List') }}</h5>
                        </div>
                        @if (Gate::check('create types'))
                            <div class="col-auto">
                                <a class="btn btn-secondary customModal" href="#" data-size="md" data-url="{{ route('type.create') }}"
                                data-title="{{ __('Create Type') }}"> <i class="ti ti-circle-plus align-text-bottom"></i>{{ __('Create Type') }}</a>

                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                        <thead>
                            <tr>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Type') }}</th>
                                @if (Gate::check('edit types') || Gate::check('delete types'))
                                    <th class="text-right">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($types as $type)
                                <tr role="row">
                                    <td>
                                        {{ $type->title }}
                                    </td>
                                    <td>
                                        {{ \App\Models\Type::type()[$type->type] }}
                                    </td>
                                    @if (Gate::check('edit types') || Gate::check('delete types'))
                                        <td class="text-right">
                                            <div class="cart-action">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['type.destroy', $type->id]]) !!}
                                                @can('edit types')
                                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Edit') }}" href="#"
                                                        data-url="{{ route('type.edit', $type->id) }}"
                                                        data-title="{{ __('Edit Type') }}"> <i data-feather="edit"></i></a>
                                                @endcan
                                                @can('delete types')
                                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" data-bs-toggle="tooltip"
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
