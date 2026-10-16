<div class="dt-responsive table-responsive">
    <table class="table table-hover advance-datatable">
        <thead>
            <tr>
                <th>{{ __('Property') }}</th>
                <th>{{ __('Unit') }}</th>
                <th>{{ __('Tenant') }}</th>
                <th>{{ __('Issue') }}</th>
                <th>{{ __('Maintainer') }}</th>
                <th>{{ __('Request Date') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Attachment') }}</th>
                @if (Gate::check('edit maintenance request') ||
                        Gate::check('delete maintenance request') ||
                        Gate::check('show maintenance request'))
                    <th class="text-right">{{ __('Action') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($maintenanceRequests as $request)
                <tr role="row">
                    <td> {{ !empty($request->properties) ? $request->properties->name : '-' }} </td>
                    <td> {{ !empty($request->units) ? $request->units->name : '-' }} </td>
                    <td>{{ !empty($request->tenetData->user) ? $request->tenetData->user->full_name : '-' }}</td>
                    <td> {{ !empty($request->types) ? $request->types->title : '-' }}
                        @if (!empty($request->ai_priority) && $request->status != 'completed')
                            <br><span class="badge bg-light-{{ \App\Support\MaintenanceTriage::color($request->ai_priority) }}" title="{{ $request->ai_summary }}">{{ \App\Support\MaintenanceTriage::priorities()[$request->ai_priority] ?? '' }}</span>
                        @endif
                    </td>
                    @if (Gate::check('assign maintainer'))
                        <td>
                            @if (!empty($request->maintainers))
                                {{ $request->maintainers->name }}
                            @else
                                @can('create maintenance request')
                                    <a href="#" class="customModal" data-size="md"
                                        data-url="{{ route('maintenance.assign', ['request_id' => $request->id]) }}"
                                        data-title="{{ __('Assign Maintainer') }}">
                                        <i class="ti ti-circle-plus text-secondary fs-2 text-center"
                                            data-bs-original-title="{{ __('Assign Maintainer') }}"
                                            data-bs-toggle="tooltip"></i>
                                    </a>
                                @else
                                    -
                                @endcan
                            @endif
                        </td>
                    @else
                        <td>
                            {{ !empty($request->maintainers) ? $request->maintainers->name : '-' }}
                        </td>
                    @endif
                    <td> {{ dateFormat($request->request_date) }} </td>
                    <td>
                        @if ($request->status == 'pending')
                            <span class="badge bg-light-warning">
                                {{ \App\Models\MaintenanceRequest::status()[$request->status] }}</span>
                        @elseif($request->status == 'in_progress')
                            <span class="badge bg-light-info">
                                {{ \App\Models\MaintenanceRequest::status()[$request->status] }}</span>
                        @else
                            <span class="badge bg-light-success">
                                {{ \App\Models\MaintenanceRequest::status()[$request->status] }}</span>
                        @endif
                    </td>
                    <td>
                        @if (!empty($request->issue_attachment))
                            <a href="{{ !empty($request->issue_attachment) ? fetch_file($request->issue_attachment, 'upload/issue_attachment/') : '#' }}"
                                download="download"><i class="ti ti-download"></i></a>
                        @else
                            -
                        @endif
                    </td>
                    @if (Gate::check('edit maintenance request') ||
                            Gate::check('delete maintenance request') ||
                            Gate::check('show maintenance request'))
                        <td class="text-right">
                            <div class="cart-action">
                                {!! Form::open(['method' => 'DELETE', 'route' => ['maintenance-request.destroy', $request->id]]) !!}
                                @can('show maintenance request')
                                    <a class="avtar avtar-xs btn-link-warning text-warning" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Show') }}"
                                        href="{{ route('maintenance-request.show', \Crypt::encrypt($request->id)) }}"
                                        data-title="{{ __('Maintenance Request Details') }}"> <i
                                            data-feather="eye"></i></a>
                                @endcan
                                @if ($request->status != 'completed')
                                    @can('edit maintenance request')
                                        <a class="avtar avtar-xs btn-link-secondary text-secondary customModal"
                                            data-size="lg" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('Edit') }}" href="#"
                                            data-url="{{ route('maintenance-request.edit', $request->id) }}"
                                            data-title="{{ __('Maintenance Request') }}"> <i data-feather="edit"></i></a>
                                    @endcan
                                @endif
                                @can('delete maintenance request')
                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Detete') }}"
                                        href="#"> <i data-feather="trash-2"></i></a>
                                @endcan
                                @if (\Auth::user()->type == 'maintainer' && $request->status != 'completed')
                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal"
                                        data-size="lg" data-bs-toggle="tooltip"
                                        data-bs-original-title="{{ __('Status Update') }}" href="#"
                                        data-url="{{ route('maintenance-request.action', $request->id) }}"
                                        data-title="{{ __('Maintenance Request Status') }}"> <i
                                            data-feather="check-square"></i></a>
                                @endif
                                {!! Form::close() !!}
                            </div>

                        </td>
                    @endif
                </tr>
            @endforeach

        </tbody>
    </table>
</div>
