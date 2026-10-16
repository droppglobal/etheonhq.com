@php
    $profile = asset(Storage::url('upload/profile/avatar.png'));
@endphp

<div class="row g-3">
    @forelse ($tenants as $tenant)
        <div class="col-xxl-3 col-xl-4 col-md-6">
            <div class="card h-100 shadow-sm border">
                <div class="card-body p-3">

                    {{-- ===== Avatar + Dropdown ===== --}}
                    <div class="d-flex justify-content-between align-items-start mb-3">

                        {{-- Avatar --}}
                        <div class="flex-grow-1 text-center">
                            <img class="rounded-circle"
                                style="width:70px;height:70px;object-fit:cover;border:2px solid #e2e8f0;"
                                src="{{ !empty($tenant->user->profile) ? fetch_file($tenant->user->profile, 'upload/profile/') : $profile }}"
                                alt="Profile">
                        </div>

                        {{-- Dropdown --}}
                        @if (Gate::check('edit tenant') || Gate::check('delete tenant') || Gate::check('show tenant'))
                            <div class="flex-shrink-0">
                                <div class="dropdown">
                                    <a class="dropdown-toggle text-primary opacity-50 arrow-none" href="#"
                                        data-bs-toggle="dropdown">
                                        <i class="ti ti-dots f-16"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        @can('edit tenant')
                                            <a class="dropdown-item"
                                                href="{{ route('tenant.edit', \Crypt::encrypt($tenant->id)) }}">
                                                <i class="material-icons-two-tone me-1">edit</i>
                                                {{ __('Edit Tenant') }}
                                            </a>
                                        @endcan
                                        @can('show tenant')
                                            <a class="dropdown-item"
                                                href="{{ route('tenant.show', \Crypt::encrypt($tenant->id)) }}">
                                                <i class="material-icons-two-tone me-1">remove_red_eye</i>
                                                {{ __('View Tenant') }}
                                            </a>
                                        @endcan
                                        @can('delete tenant')
                                            {!! Form::open([
                                                'method' => 'DELETE',
                                                'route' => ['tenant.destroy', $tenant->id],
                                                'id' => 'tenant-' . $tenant->id,
                                            ]) !!}
                                            <a class="dropdown-item confirm_dialog" href="#">
                                                <i class="material-icons-two-tone me-1">delete</i>
                                                {{ __('Delete Tenant') }}
                                            </a>
                                            {!! Form::close() !!}
                                        @endcan
                                        @php
                                            $leaseEndDate = \Carbon\Carbon::parse($tenant->lease_end_date);
                                            $daysUntilExpiry = now()->diffInDays($leaseEndDate, false);
                                        @endphp
                                        @can('manage lease renew')
                                            @if ($daysUntilExpiry <= 3)
                                                <a class="dropdown-item customModal" href="#"
                                                    data-url="{{ route('lease.renew', ['tenant_id' => $tenant->id]) }}"
                                                    data-title="{{ __('Renew Lease') }}" data-size="lg">
                                                    <span class="material-icons-two-tone me-1">autorenew</span>
                                                    {{ __('Renew Lease') }}
                                                </a>
                                            @endif
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- ===== Name & Email ===== --}}
                    <div class="text-center mb-3">
                        @can('show tenant')
                            <a href="{{ route('tenant.show', \Crypt::encrypt($tenant->id)) }}">
                                <h5 class="mb-1">
                                    {{ ucfirst($tenant->user->first_name ?? '') . ' ' . ucfirst($tenant->user->last_name ?? '') }}
                                </h5>
                            </a>
                        @else
                            <h5 class="mb-1">
                                {{ ucfirst($tenant->user->first_name ?? '') . ' ' . ucfirst($tenant->user->last_name ?? '') }}
                            </h5>
                        @endcan
                        <p class="text-muted text-sm mb-1 text-truncate">
                            {{ $tenant->user->email ?? '-' }}
                        </p>
                        @if (!empty($tenant->address))
                            <p class="text-muted text-sm mb-0 text-truncate">
                                <i class="ti ti-map-pin me-1"></i>{{ $tenant->address }}
                            </p>
                        @endif
                    </div>

                    <hr class="my-2">

                    {{-- ===== Details Grid ===== --}}
                    <div class="row g-2">
                        <div class="col-6">
                            <p class="mb-0 text-muted" style="font-size:11px;">{{ __('Phone') }}</p>
                            <p class="mb-0 fw-semibold" style="font-size:12px;">
                                {{ $tenant->user->phone_number ?? '-' }}
                            </p>
                        </div>
                        <div class="col-6">
                            <p class="mb-0 text-muted" style="font-size:11px;">{{ __('Family Member') }}</p>
                            <p class="mb-0 fw-semibold" style="font-size:12px;">
                                {{ $tenant->family_member ?? '-' }}
                            </p>
                        </div>
                        <div class="col-6">
                            <p class="mb-0 text-muted" style="font-size:11px;">{{ __('Property') }}</p>
                            <p class="mb-0 fw-semibold" style="font-size:12px;">
                                {{ $tenant->properties->name ?? '-' }}
                            </p>
                        </div>
                        <div class="col-6">
                            <p class="mb-0 text-muted" style="font-size:11px;">{{ __('Unit') }}</p>
                            <p class="mb-0 fw-semibold" style="font-size:12px;">
                                {{ $tenant->units->name ?? '-' }}
                            </p>
                        </div>
                        <div class="col-6">
                            <p class="mb-0 text-muted" style="font-size:11px;">{{ __('Lease Start') }}</p>
                            <p class="mb-0 fw-semibold" style="font-size:12px;">
                                {{ !empty($tenant->lease_start_date) ? dateFormat($tenant->lease_start_date) : '-' }}
                            </p>
                        </div>
                        <div class="col-6">
                            <p class="mb-0 text-muted" style="font-size:11px;">{{ __('Lease End') }}</p>
                            <p class="mb-0 fw-semibold" style="font-size:12px;">
                                {{ !empty($tenant->lease_end_date) ? dateFormat($tenant->lease_end_date) : '-' }}
                            </p>
                        </div>
                    </div>



                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="ti ti-users-off" style="font-size:48px;color:#ccc;"></i>
            <p class="text-muted mt-2">{{ __('No Tenants Found') }}</p>
        </div>
    @endforelse
</div>

{{-- ===== Pagination ===== --}}
@if ($tenants->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        <small class="text-muted">
            {{ __('Showing') }} {{ $tenants->firstItem() }} {{ __('to') }}
            {{ $tenants->lastItem() }}
            {{ __('of') }} {{ $tenants->total() }} {{ __('results') }}
        </small>
        <div class="pagination-wrapper detail-pagination">
            {{ $tenants->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endif
