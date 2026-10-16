<div class="dt-responsive table-responsive">
    <table class="table table-hover advance-datatable">
        <thead>
            <tr>
                <th>{{ __('Property') }}</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Tenant') }}</th>
                <th>{{ __('Rent Type') }}</th>
                <th>{{ __('Rent') }}</th>
                <th>{{ __('Rent Duration') }}</th>
                <th>{{ __('Status') }}</th>
                @if (Gate::check('edit unit') || Gate::check('delete unit'))
                    <th class="text-right">{{ __('Action') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($units as $unit)
                <tr>
                    <td>{{ !empty($unit->properties) ? $unit->properties->name : '-' }} </td>
                    <td>{{ ucfirst($unit->name) }} </td>
                    @php
                        $tenant = $unit->tenants();
                    @endphp

                    @if (empty($tenant) || !empty($tenant->exit_date))
                        <td>{{ '-' }}</td>
                    @else
                        <td>{{ !empty($tenant->user) ? $tenant->user->name : '-' }}</td>
                    @endif
                    <td>{{ $unit->rent_type }} </td>
                    <td>{{ priceFormat($unit->rent) }} </td>
                    <td>
                        @if ($unit->rent_type == 'custom')
                            <span>{{ __('Start Date') }} :- </span>{{ dateFormat($unit->start_date) }}
                            <br>
                            <span>{{ __('End Date') }} :- </span>{{ dateFormat($unit->end_date) }}
                            <br>
                            <span>{{ __('Payment Due Date') }} :-
                            </span>{{ dateFormat($unit->payment_due_date) }}
                        @else
                            {{ $unit->rent_duration }}
                        @endif
                    </td>
                    </td>
                    @if ($unit->is_occupied == 1)
                        <td class="text-danger">
                            <i class="fas fa-circle f-10 m-r-10"></i>
                            {{ __('Occupied') }}
                        </td>
                    @else
                        <td class="text-success">
                            <i class="fas fa-circle f-10 m-r-10"></i>
                            {{ __('Vacant') }}
                        </td>
                    @endif

                    @if (Gate::check('edit unit') || Gate::check('delete unit'))
                        <td class="text-right">
                            <div class="cart-action">
                                {!! Form::open(['method' => 'DELETE', 'route' => ['unit.destroy', [$unit->property_id, $unit->id]]]) !!}

                                @can('show unit')
                                    <a class="avtar avtar-xs btn-link-warning text-warning"
                                        href="{{ route('unit.show', \Crypt::encrypt($unit->id)) }}"
                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Show') }}">
                                        <i data-feather="eye"></i></a>
                                @endcan
                                @can('edit unit')
                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal"
                                        data-url="{{ route('unit.edit', [$unit->property_id, $unit->id]) }}" href="#"
                                        data-size="lg" data-title="{{ __('Edit Unit') }}" data-bs-toggle="tooltip"
                                        data-bs-original-title="{{ __('Edit') }}">
                                        <i data-feather="edit"></i></a>
                                @endcan

                                @can('delete unit')
                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Detete') }}"
                                        href="#"> <i data-feather="trash-2"></i></a>
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
