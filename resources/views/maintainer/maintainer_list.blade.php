<div class="row g-3">
    @forelse ($maintainers as $maintainer)
        <div class="col-xxl-3 col-xl-4 col-md-6">
            <div class="card h-100 shadow-sm border">
                <div class="card-body p-3">

                    {{-- ===== Avatar + Dropdown ===== --}}
                    <div class="d-flex justify-content-between align-items-start mb-3">

                        {{-- Avatar --}}
                        <div class="flex-grow-1 text-center">
                            <img class="rounded-circle"
                                style="width:70px;height:70px;object-fit:cover;border:2px solid #e2e8f0;"
                                src="{{ !empty($maintainer->user->profile) ? fetch_file($maintainer->user->profile, 'upload/profile/') : $profile }}"
                                alt="Profile">
                        </div>

                        {{-- Dropdown --}}
                        @if (Gate::check('edit maintainer') || Gate::check('delete maintainer'))
                            <div class="flex-shrink-0">
                                <div class="dropdown">
                                    <a class="dropdown-toggle text-primary opacity-50 arrow-none" href="#"
                                        data-bs-toggle="dropdown">
                                        <i class="ti ti-dots f-16"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item"
                                            href="{{ route('maintainer.show', encrypt($maintainer->id)) }}"
                                            data-title="{{ __('Show Maintainer') }}" data-size="lg">
                                            <i class="material-icons-two-tone me-1">visibility</i>
                                            {{ __('Show Maintainer') }}
                                        </a>
                                        @can('edit maintainer')
                                            <a class="dropdown-item customModal" href="#"
                                                data-url="{{ route('maintainer.edit', $maintainer->id) }}"
                                                data-title="{{ __('Edit Maintainer') }}" data-size="lg">
                                                <i class="material-icons-two-tone me-1">edit</i>
                                                {{ __('Edit Maintainer') }}
                                            </a>
                                        @endcan
                                        @can('delete maintainer')
                                            {!! Form::open([
                                                'method' => 'DELETE',
                                                'route' => ['maintainer.destroy', $maintainer->id],
                                                'id' => 'maintainer-' . $maintainer->id,
                                            ]) !!}
                                            <a class="dropdown-item confirm_dialog" href="#">
                                                <i class="material-icons-two-tone me-1">delete</i>
                                                {{ __('Delete Maintainer') }}
                                            </a>
                                            {!! Form::close() !!}
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- ===== Name & Email ===== --}}
                    <div class="text-center mb-3">
                        {{-- @can('edit maintainer') --}}
                        <a class="dropdown-item" href="{{ route('maintainer.show', encrypt($maintainer->id)) }}"
                            data-title="{{ __('Show Maintainer') }}" data-size="lg">
                            <h5 class="mb-1">
                                {{ ucfirst($maintainer->user->first_name ?? '') . ' ' . ucfirst($maintainer->user->last_name ?? '') }}
                            </h5>
                        </a>
                        {{-- @else
                            <h5 class="mb-1">
                                {{ ucfirst($maintainer->user->first_name ?? '') . ' ' . ucfirst($maintainer->user->last_name ?? '') }}
                            </h5>
                        @endcan --}}
                        <p class="text-muted text-sm mb-0 text-truncate">
                            {{ $maintainer->user->email ?? '-' }}
                        </p>
                    </div>

                    <hr class="my-2">

                    {{-- ===== Details Grid ===== --}}
                    <div class="row g-2">

                        @if (!empty($maintainer->user->phone_number))
                            <div class="col-6">
                                <p class="mb-0 text-muted" style="font-size:11px;">{{ __('Phone') }}</p>
                                <p class="mb-0 fw-semibold" style="font-size:12px;">
                                    {{ $maintainer->user->phone_number }}
                                </p>
                            </div>
                        @endif

                        <div class="col-6">
                            <p class="mb-0 text-muted" style="font-size:11px;">{{ __('Type') }}</p>
                            <p class="mb-0 fw-semibold" style="font-size:12px;">
                                {{ $maintainer->types->title ?? '-' }}
                            </p>
                        </div>

                        <div class="col-6">
                            <p class="mb-0 text-muted" style="font-size:11px;">{{ __('Created Date') }}</p>
                            <p class="mb-0 fw-semibold" style="font-size:12px;">
                                {{ dateFormat($maintainer->created_at) }}
                            </p>
                        </div>

                    </div>

                    {{-- ===== Properties ===== --}}
                    @php $properties = $maintainer->properties(); @endphp
                    @if (!empty($properties) && count($properties) > 0)
                        <hr class="my-2">
                        <p class="mb-1 text-muted" style="font-size:11px;">{{ __('Properties') }}</p>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach ($properties as $property)
                                <span class="badge bg-light-secondary text-secondary" style="font-size:11px;">
                                    <i class="ti ti-building me-1"></i>{{ $property->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="ti ti-tool-off" style="font-size:48px;color:#ccc;"></i>
            <p class="text-muted mt-2">{{ __('No Maintainers Found') }}</p>
        </div>
    @endforelse
    @if ($maintainers->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <small class="text-muted">
                {{ __('Showing') }} {{ $maintainers->firstItem() }} {{ __('to') }}
                {{ $maintainers->lastItem() }}
                {{ __('of') }} {{ $maintainers->total() }} {{ __('results') }}
            </small>
            <div class="pagination-wrapper detail-pagination">
                {{ $maintainers->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>
