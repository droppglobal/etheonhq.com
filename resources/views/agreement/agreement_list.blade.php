<div class="dt-responsive table-responsive">
    <table class="table table-hover advance-datatable">
        <thead>
            <tr>
                <th>{{ __('Agreement') }}</th>
                <th>{{ __('Property') }}</th>
                <th>{{ __('Unit') }}</th>
                <th>{{ __('Tenant') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Status') }}</th>
                @if (Gate::check('edit agreement') || Gate::check('delete agreement') || Gate::check('show agreement'))
                    <th class="text-right">{{ __('Action') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($agreements as $agreement)
                <tr>
                    <td>{{ agreementPrefix() . $agreement->agreement_id }} </td>
                    <td>{{ !empty($agreement->properties) ? $agreement->properties->name : '-' }} </td>
                    <td>{{ !empty($agreement->units) ? $agreement->units->name : '-' }} </td>
                    <td>{{ !empty($agreement->tenants->user) ? $agreement->tenants->user->name : '-' }} </td>
                    <td>{{ dateFormat($agreement->date) }} </td>
                    <td>
                        @if ($agreement->status == 'Draft')
                            <span
                                class="badge bg-light-dark">{{ \App\Models\agreement::status()[$agreement->status] }}</span>
                        @elseif($agreement->status == 'Pending')
                            <span
                                class="badge bg-light-warning">{{ \App\Models\agreement::status()[$agreement->status] }}</span>
                        @elseif($agreement->status == 'Completed')
                            <span
                                class="badge bg-light-success">{{ \App\Models\agreement::status()[$agreement->status] }}</span>
                        @elseif($agreement->status == 'Active')
                            <span
                                class="badge bg-light-info">{{ \App\Models\agreement::status()[$agreement->status] }}</span>
                        @elseif($agreement->status == 'Cancelled')
                            <span
                                class="badge bg-light-danger">{{ \App\Models\agreement::status()[$agreement->status] }}</span>
                        @elseif($agreement->status == 'Confirmed')
                            <span
                                class="badge bg-light-secondary">{{ \App\Models\agreement::status()[$agreement->status] }}</span>
                        @endif
                    </td>

                    @if (Gate::check('edit agreement') || Gate::check('delete agreement') || Gate::check('show agreement'))
                        <td>
                            <div class="cart-action">
                                {!! Form::open(['method' => 'DELETE', 'route' => ['agreement.destroy', $agreement->id]]) !!}

                                @can('show agreement')
                                    <a class="avtar avtar-xs btn-link-warning text-warning"
                                        href="{{ route('agreement.show', \Crypt::encrypt($agreement->id)) }}"
                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}"> <i
                                            data-feather="eye"></i></a>
                                @endcan
                                @can('edit agreement')
                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" data-size="lg"
                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"
                                        href="#" data-url="{{ route('agreement.edit', $agreement->id) }}"
                                        data-title="{{ __('Edit Agreement') }}"> <i data-feather="edit"></i></a>
                                @endcan
                                @can('delete agreement')
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
