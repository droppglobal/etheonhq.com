<div class="card-body pt-0">
    <div class="dt-responsive table-responsive">
        <table class="table table-hover advance-datatable">
            <thead>
                <tr>
                    <th>{{ __('Property') }}</th>
                    <th>{{ __('Unit') }}</th>
                    <th>{{ __('Tenant Name') }}</th>
                    <th>{{ __('Maintainer Name') }}</th>
                    <th>{{ __('Issue') }}</th>
                    <th>{{ __('Status') }}</th>

                </tr>
            </thead>
            <tbody>

                @foreach ($maintenances as $maintenance)
                    <tr>
                        <td>{{ $maintenance->properties->name ?? '-' }}</td>
                        <td>{{ $maintenance->units->name ?? '-' }}</td>
                        <td>{{ $maintenance->tenetData?->user?->first_name . ' ' . $maintenance->tenetData?->user?->last_name ?? '-' }}
                        </td>
                        <td>{{ $maintenance->maintainers->first_name ?? '-' }}</td>
                        <td>{{ $maintenance->types->title ?? '-' }}</td>

                        <td>

                            @if ($maintenance->status == 'in_progress')
                                <span class="badge bg-light-info">
                                    {{ __('In Progress') }}</span>
                            @elseif ($maintenance->status == 'completed')
                                <span class="badge bg-light-success">
                                    {{ ucfirst($maintenance->status) }}</span>
                            @elseif ($maintenance->status == 'pending')
                                <span class="badge bg-light-warning">
                                    {{ ucfirst($maintenance->status) }}</span>
                            @else
                                <span>{{ __('No Invoice Found') }}</span>
                            @endif

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
