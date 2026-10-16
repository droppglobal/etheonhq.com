<div class="row g-3">
    @foreach ($properties as $property)
        {{-- @dd($property->thumbnail) --}}
        @if (!empty($property->thumbnail) && !empty($property->thumbnail->image))
            @php $thumbnail= $property->thumbnail->image; @endphp
        @else
            @php $thumbnail= 'default.jpg'; @endphp
        @endif
        <div class="col-sm-6 col-md-4 col-xxl-3">
            <div class="card h-100 shadow-sm border">

                <div class="card product-card">
                    <div class="card-img-top">
                        <img src="{{ fetch_file($thumbnail, 'upload/property/thumbnail/') }}" alt="{{ $property->name }}"
                            class="img-prod" />

                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <a href="@can('show property') {{ route('property.show', \Crypt::encrypt($property->id)) }}  @endcan"
                                class="fw-semibold mb-0 text-truncate">
                                <h4>{{ $property->name }}</h4>
                            </a>
                            @if (Gate::check('edit property') || Gate::check('delete property') || Gate::check('show property'))
                                <div class="dropdown">
                                    <a class="dropdown-toggle text-primary opacity-50 arrow-none" href="#"
                                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="ti ti-dots f-16"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        {!! Form::open([
                                            'method' => 'DELETE',
                                            'route' => ['property.destroy', $property->id],
                                            'id' => 'property-' . $property->id,
                                        ]) !!}
                                        @can('edit property')
                                            <a class="dropdown-item"
                                                href="{{ route('property.edit', \Crypt::encrypt($property->id)) }}">
                                                <i class="material-icons-two-tone">edit</i>
                                                {{ __('Edit Property') }}
                                            </a>
                                        @endcan

                                        @can('show property')
                                            <a class="dropdown-item"
                                                href="{{ route('property.show', \Crypt::encrypt($property->id)) }}">
                                                <i class="material-icons-two-tone">remove_red_eye</i>
                                                {{ __('View property') }}
                                            </a>
                                        @endcan
                                        @can('delete property')
                                            <a class="dropdown-item confirm_dialog" href="#">
                                                <i class="material-icons-two-tone">delete</i>
                                                {{ __('Delete Property') }}
                                            </a>
                                        @endcan

                                        {!! Form::close() !!}
                                    </div>
                                </div>
                            @endif

                        </div>
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-light-info">
                                    <i class="material-icons-two-tone">ad_units</i>
                                    {{ $property->totalUnit() }} {{ __('Unit') }}
                                </button>

                                <button type="button" class="btn btn-sm btn-light-success">
                                    <i class="material-icons-two-tone">ad_units</i>
                                    {{ $property->vacantUnit() }} {{ __('Vacant') }}
                                </button>

                                <button type="button" class="btn btn-sm btn-light-danger">
                                    <i class="material-icons-two-tone">ad_units</i>
                                    {{ $property->occupiedUnit() }} {{ __('Occupied ') }}
                                </button>
                            </div>
                        </div>


                        <p class="prod-content my-2 text-muted">
                            {{ Str::limit(strip_tags($property->description), 200, '...') }}
                        </p>

                        <div class="d-flex align-items-center justify-content-between mt-3">

                            <span class="badge bg-light-secondary" data-bs-toggle="tooltip"
                                data-bs-original-title="{{ __('Type') }}">{{ \App\Models\Property::types()[$property->type] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    @if ($properties->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <small class="text-muted">
                {{ __('Showing') }} {{ $properties->firstItem() }} {{ __('to') }}
                {{ $properties->lastItem() }}
                {{ __('of') }} {{ $properties->total() }} {{ __('results') }}
            </small>
            <div class="pagination-wrapper detail-pagination">
                {{ $properties->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif

</div>
