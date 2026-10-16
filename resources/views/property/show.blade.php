@extends('layouts.app')
@section('page-title')
    {{ __('Property Details') }}
@endsection

@push('script-page')
    <script src={{ asset('assets/js/plugins/wizard.min.js') }}></script>
    <script>
        new Wizard('#basicwizard', {
            validate: true,
            progress: true
        });
    </script>
@endpush
@push('css-page')
    <style>
        .form-wizard .nav-pills .nav-link.active,
        .form-wizard .nav-pills .show>.nav-link {
            background: var(--bs-secondary) !important;
        }
    </style>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">
        <a href="{{ route('property.index') }}">{{ __('Property') }}</a>
    </li>
    <li class="breadcrumb-item active">
        <a href="#">{{ __('Details') }}</a>
    </li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">

            <div class=" property-page mt-3">
                <div id="basicwizard" class="form-wizard row justify-content-center">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body p-3">
                                <ul class="nav nav-pills nav-justified">
                                    <li class="nav-item" data-target-form="#profile-tab-1Form">
                                        <a class="nav-link active" id="profile-tab-1" data-bs-toggle="tab" href="#profile-1"
                                            role="tab" aria-selected="true">
                                            <i class="ph-duotone ph-house-line"></i>
                                            <span class="d-none d-sm-inline">{{ __('Property Details') }}</span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link" id="profile-tab-2" data-bs-toggle="tab" href="#profile-2"
                                            role="tab" aria-selected="true">
                                            <i class="ph-duotone ph-cardholder"></i>
                                            {{ __('Property Units') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="profile-tab-3" data-bs-toggle="tab" href="#profile-3"
                                            role="tab" aria-selected="true">
                                            <i class="ph-duotone ph-faders"></i>
                                            {{ __('Amenities') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="profile-tab-4" data-bs-toggle="tab" href="#profile-4"
                                            role="tab" aria-selected="true">
                                            <i class="ph-duotone ph-thumbs-up"></i>
                                            {{ __('Advantages') }}
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link" id="profile-tab-5" data-bs-toggle="tab" href="#profile-5"
                                            role="tab">
                                            <i class="ph-duotone ph-wallet"></i>
                                            {{ __('Expense') }}
                                        </a>
                                    </li>

                                </ul>
                            </div>
                        </div>
                        <div class="card">

                            <div class="card-body">
                                <div class="tab-content">
                                    <div id="bar" class="progress mb-3" style="height: 7px">
                                        <div id="progressBar"
                                            class="progress-bar progress-bar-striped progress-bar-animated bg-secondary"
                                            style="width: 16.6%;">
                                        </div>
                                    </div>
                                    <div class="tab-pane show active" id="profile-1" role="tabpanel"
                                        aria-labelledby="profile-tab-1">
                                        <div class="row justify-content-center">
                                            <div class="col-xl-12 col-xxl-12">
                                                <div class="row">
                                                    <div class="col-md-5">
                                                        <div class="sticky-md-top product-sticky">
                                                            <div id="carouselExampleCaptions"
                                                                class="carousel slide carousel-fade"
                                                                data-bs-ride="carousel">
                                                                <div class="carousel-inner">
                                                                    @foreach ($property->propertyImages as $key => $image)
                                                                        <div
                                                                            class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                                                                            <img src="{{ fetch_file($image->image, 'upload/property/image/') }}"
                                                                                class="d-block w-100 rounded"
                                                                                alt="Product image" />
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                                <ol
                                                                    class="carousel-indicators position-relative product-carousel-indicators my-sm-3 mx-0">
                                                                    @foreach ($property->propertyImages as $key => $image)
                                                                        <li data-bs-target="#carouselExampleCaptions"
                                                                            data-bs-slide-to="{{ $key }}"
                                                                            class="{{ $key === 0 ? 'active' : '' }} w-25 h-auto">
                                                                            <img src="{{ fetch_file($image->image, 'upload/property/image/') }}"
                                                                                class="d-block wid-50 rounded"
                                                                                alt="Product image" />
                                                                        </li>
                                                                    @endforeach
                                                                </ol>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-7">

                                                        <h3 class="">
                                                            {{ ucfirst($property->name) }}

                                                        </h3>
                                                        <span class="badge bg-light-primary f-14 mt-1"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Type') }}">{{ \App\Models\Property::types()[$property->type] }}</span>
                                                        <h5 class="mt-4">{{ __('Property Details') }}</h5>
                                                        <hr class="my-3" />
                                                        <p class="text-muted">
                                                            {!! $property->description !!}
                                                        </p>

                                                        <h5>{{ __('Property Address') }}</h5>
                                                        <hr class="my-3" />
                                                        <div class="mb-1 row">
                                                            <label class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                {{ __('Address') }} :

                                                            </label>
                                                            <div
                                                                class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                {{ $property->address }}
                                                            </div>
                                                        </div>
                                                        <div class="mb-1 row">
                                                            <label class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                {{ __('Location') }} :

                                                            </label>
                                                            <div
                                                                class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                {{ $property->city . ', ' . $property->state . ', ' . $property->country }}
                                                            </div>
                                                        </div>
                                                        <div class="mb-1 row">
                                                            <label class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                {{ __('Zip Code') }} :

                                                            </label>
                                                            <div
                                                                class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                {{ $property->zip_code }}
                                                            </div>
                                                        </div>

                                                        <hr class="my-3" />

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if (!empty($property->propertyImages) && $property->propertyImages->count())
                                            <div class="row">
                                                @foreach ($property->propertyImages as $doc)
                                                    @php
                                                        $folder = 'upload/property/image/';
                                                        $filename = $doc->image;
                                                        $fileUrl = fetch_file($filename, $folder);

                                                        $fileExtension = pathinfo($filename, PATHINFO_EXTENSION); // Use filename, not URL
                                                        $isImage = in_array(strtolower($fileExtension), [
                                                            'jpg',
                                                            'jpeg',
                                                            'png',
                                                            'gif',
                                                            'webp',
                                                        ]);
                                                    @endphp

                                                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                                                        <div
                                                            class="card gallery-card shadow-sm border rounded text-center d-flex flex-column justify-content-between">
                                                            @if ($isImage)
                                                                <a href="{{ $fileUrl }}" target="_blank">
                                                                    <img src="{{ $fileUrl }}" alt="Document"
                                                                        class="img-fluid img-card-top rounded-top mt-1"
                                                                        style="height: 180px; object-fit: cover;">
                                                                </a>
                                                            @else
                                                                <a href="{{ $fileUrl }}" target="_blank"
                                                                    class="d-flex justify-content-center align-items-center bg-light"
                                                                    style="height: 180px;">
                                                                    <i class="ti ti-file-text"
                                                                        style="font-size: 48px;"></i>
                                                                </a>
                                                            @endif
                                                            <hr>
                                                            <div class="d-flex justify-content-center gap-2">
                                                                <a href="{{ $fileUrl }}" download title="Download"
                                                                    class="avtar btn-link-success text-success p-0">
                                                                    <i class="ti ti-download "></i>
                                                                </a>

                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['property.image.delete', $doc->id],
                                                                    'id' => 'doc-' . $doc->id,
                                                                ]) !!}
                                                                <a class="avtar btn-link-danger text-danger confirm_dialog p-0"
                                                                    href="#"><i class="ti ti-trash text-danger"></i>
                                                                </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="tab-pane" id="profile-2" role="tabpanel"
                                        aria-labelledby="profile-tab-2">


                                        <div class="row mb-3">
                                            <div class="col">
                                                <!-- Left content -->
                                            </div>

                                            @can('create property')
                                                <div class="col-auto ms-auto d-flex gap-2">
                                                    <a class="btn btn-light-secondary customModal" data-size="lg" href="#"
                                                        data-url="{{ route('unit.bulk.create', ['property' => $property->id]) }}"
                                                        data-title="{{ __('Add Many Units') }}"><i class="ti ti-layout-grid-add align-text-bottom"></i> {{ __('Add Many Units') }}</a>
                                                    <a class="btn btn-secondary customModal" data-size="lg" href="#"
                                                        data-url="{{ route('unit.create', $property->id) }}"
                                                        data-title="{{ __('Add Unit') }}">
                                                        <i class="ti ti-circle-plus align-text-bottom"></i>
                                                        {{ __('Add Unit') }}
                                                    </a>
                                                </div>
                                            @endcan
                                        </div>

                                        <div class="row">
                                            @if ($units->count())
                                                @foreach ($units as $unit)
                                                    <div class="col-xxl-3 col-xl-4 col-md-6">
                                                        <div class="card follower-card">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex align-items-start mb-3">
                                                                    <div class="flex-grow-1 ">
                                                                        <h2 class="mb-1 text-truncate">
                                                                            {{ ucfirst($unit->name) }}
                                                                        </h2>
                                                                    </div>
                                                                    <div class="flex-shrink-0">
                                                                        <div class="dropdown">
                                                                            <a class="dropdown-toggle text-primary opacity-50 arrow-none"
                                                                                href="#" data-bs-toggle="dropdown"
                                                                                aria-haspopup="true"
                                                                                aria-expanded="false">
                                                                                <i class="ti ti-dots f-16"></i>
                                                                            </a>
                                                                            <div class="dropdown-menu dropdown-menu-end">

                                                                                @can('edit unit')
                                                                                    <a class="dropdown-item customModal"
                                                                                        href="#"
                                                                                        data-url="{{ route('unit.edit', [$property->id, $unit->id]) }}"
                                                                                        data-title="{{ __('Edit Unit') }}"
                                                                                        data-size="lg">
                                                                                        <i
                                                                                            class="material-icons-two-tone">edit</i>{{ __('Edit Unit') }}</a>
                                                                                @endcan

                                                                                @can('delete unit')
                                                                                    {!! Form::open([
                                                                                        'method' => 'DELETE',
                                                                                        'route' => ['unit.destroy', $property->id, $unit->id],
                                                                                        'id' => 'unit-' . $unit->id,
                                                                                    ]) !!}

                                                                                    <a class="dropdown-item confirm_dialog"
                                                                                        href="#">
                                                                                        <i
                                                                                            class="material-icons-two-tone">delete</i>
                                                                                        {{ __('Delete Unit') }}

                                                                                    </a>
                                                                                    {!! Form::close() !!}
                                                                                @endcan
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <hr class="my-3" />


                                                                <div class="row">

                                                                    <p class="mb-1">{{ __('Status') }}:
                                                                        @if ($unit->is_occupied)
                                                                            <span
                                                                                class=" ms-1 text-danger">{{ __('Occupied') }}</span>
                                                                        @else
                                                                            <span
                                                                                class="text-success ms-1">{{ __('Vacant') }}</span>
                                                                        @endif
                                                                    </p>

                                                                    <p class="mb-1">{{ __('Bedroom') }} :
                                                                        <span
                                                                            class="text-muted">{{ $unit->bedroom }}</span>
                                                                    </p>
                                                                    <p class="mb-1">{{ __('Kitchen') }} :
                                                                        <span
                                                                            class="text-muted">{{ $unit->kitchen }}</span>
                                                                    </p>
                                                                    <p class="mb-1">{{ __('Bath') }} :
                                                                        <span
                                                                            class="text-muted">{{ $unit->baths }}</span>
                                                                    </p>
                                                                    <p class="mb-1">{{ __('Rent Type') }} :
                                                                        <span
                                                                            class="text-muted">{{ \App\Models\PropertyUnit::rentTypes()[$unit->rent_type] }}</span>
                                                                    </p>
                                                                    <p class="mb-1">{{ __('Rent') }} :
                                                                        <span
                                                                            class="text-muted">{{ priceFormat($unit->rent) }}</span>
                                                                    </p>
                                                                    @if ($unit->rent_type == 'custom')
                                                                        <p class="mb-1">{{ __('Start Date') }} :
                                                                            <span
                                                                                class="text-muted">{{ dateformat($unit->start_date) }}</span>
                                                                        </p>
                                                                        <p class="mb-1">{{ __('End Date') }} :
                                                                            <span
                                                                                class="text-muted">{{ dateformat($unit->end_date) }}</span>
                                                                        </p>
                                                                        <p class="mb-1">{{ __('Payment Due Date') }} :
                                                                            <span
                                                                                class="text-muted">{{ $unit->payment_due_date }}</span>
                                                                        </p>
                                                                    @else
                                                                        <p class="mb-1">{{ __('Rent Duration') }} :
                                                                            <span
                                                                                class="text-muted">{{ $unit->rent_duration }}</span>
                                                                        </p>
                                                                    @endif

                                                                    <p class="mb-1">{{ __('Deposit Type') }} :
                                                                        <span
                                                                            class="text-muted">{{ \App\Models\PropertyUnit::type()[$unit->deposit_type] }}</span>
                                                                    </p>
                                                                    <p class="mb-1">{{ __('Deposit Amount') }} :
                                                                        <span class="text-muted">
                                                                            {{ $unit->deposit_type == 'fixed' ? priceFormat($unit->deposit_amount) : $unit->deposit_amount . '%' }}
                                                                        </span>
                                                                    </p>
                                                                    <p class="mb-1">{{ __('Late Fee Type') }} :
                                                                        <span
                                                                            class="text-muted">{{ App\Models\PropertyUnit::type()[$unit->late_fee_type] }}</span>
                                                                    </p>
                                                                    <p class="mb-1">{{ __('Late Fee Amount') }} :
                                                                        <span class="text-muted">
                                                                            {{ $unit->late_fee_type == 'fixed' ? priceFormat($unit->late_fee_amount) : $unit->late_fee_amount . '%' }}
                                                                        </span>
                                                                    </p>
                                                                    <p class="mb-1">{{ __('Incident Receipt Amount') }}
                                                                        :
                                                                        <span
                                                                            class="text-muted">{{ priceFormat($unit->incident_receipt_amount) }}</span>
                                                                    </p>
                                                                </div>

                                                                <hr class="my-2" />
                                                                <p class="my-3 text-muted text-sm">
                                                                    {{ $unit->notes }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="row justify-content-center">
                                                    <div class="col-xl-12 col-xxl-12">
                                                        <div class="card border">
                                                            <div class="card-body">
                                                                <div class="col-12">
                                                                    <p class="text-muted">{{ __('No unit available') }}.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="tab-pane" id="profile-3" role="tabpanel"
                                        aria-labelledby="profile-tab-3">
                                        <div class="row justify-content-center">
                                            <div class="col-xl-12 col-xxl-12">

                                                @if ($selectedAmenities->count())
                                                    <div class="row">
                                                        @foreach ($selectedAmenities as $amenity)
                                                            <div class="col-md-6 col-xl-4 mb-3">
                                                                <div
                                                                    class="position-relative h-100 border p-3 rounded shadow-sm d-flex align-items-start gap-3">

                                                                    <i class="ti ti-circle-check text-success fs-10 position-absolute"
                                                                        style="top: 10px; right: 10px;"></i>

                                                                    @if ($amenity->image)
                                                                        <img src="{{ fetch_file('upload/amenity/' . $amenity->image) }}"
                                                                            alt="{{ $amenity->name }}"
                                                                            style="width: 40px; height: 40px; object-fit: cover;"
                                                                            class="rounded shadow-sm mt-1">
                                                                    @endif
                                                                    <div>
                                                                        <h6 class="mb-1">{{ $amenity->name }}
                                                                        </h6>
                                                                        <p class="mb-0 text-muted text-sm"
                                                                            style="font-size: 14px;">
                                                                            {{ $amenity->description }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="col-12">
                                                        <p class="text-muted">{{ __('No amenities selected') }}.
                                                        </p>
                                                    </div>
                                                @endif

                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane" id="profile-4" role="tabpanel"
                                        aria-labelledby="profile-tab-4">
                                        <div class="row justify-content-center">
                                            <div class="col-xl-12 col-xxl-12">

                                                @if ($selectedAdvantages->count())
                                                    <div class="row">
                                                        @foreach ($selectedAdvantages as $advantage)
                                                            <div class="col-md-6 col-xl-4 mb-3">
                                                                <div
                                                                    class="position-relative h-100 border p-3 rounded shadow-sm d-flex align-items-start gap-3">

                                                                    <i class="ti ti-circle-check text-success fs-10 position-absolute"
                                                                        style="top: 10px; right: 10px;"></i>

                                                                    <div>
                                                                        <h6 class="mb-1">{{ $advantage->name }}</h6>
                                                                        <p class="mb-0 text-muted text-sm"
                                                                            style="font-size: 14px;">
                                                                            {{ $advantage->description }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="col-12">
                                                        <p class="text-muted">{{ __('No advantage selected') }}.</p>
                                                    </div>
                                                @endif

                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane" id="profile-5" role="tabpanel"
                                        aria-labelledby="profile-tab-5">
                                        <div class="dt-responsive table-responsive">
                                            <table class="table table-hover ">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Expense') }}</th>
                                                        <th>{{ __('Title') }}</th>
                                                        <th>{{ __('Unit') }}</th>
                                                        <th>{{ __('Type') }}</th>
                                                        <th>{{ __('Date') }}</th>
                                                        <th>{{ __('Amount') }}</th>
                                                        <th>{{ __('Receipt') }}</th>
                                                        @if (Gate::check('show expense'))
                                                            <th class="text-right">{{ __('Action') }}</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($expenses as $expense)
                                                        <tr role="row">
                                                            <td>{{ expensePrefix() . $expense->expense_id }} </td>
                                                            <td> {{ $expense->title }} </td>

                                                            <td> {{ !empty($expense->units) ? $expense->units->name : '-' }}
                                                            </td>
                                                            <td> {{ !empty($expense->types) ? $expense->types->title : '-' }}
                                                            </td>
                                                            <td> {{ dateFormat($expense->date) }} </td>
                                                            <td> {{ priceFormat($expense->amount) }} </td>
                                                            <td>
                                                                @if (!empty($expense->receipt))
                                                                    <a href="{{ Storage::url('upload/receipt/' . $expense->receipt) }}"
                                                                        download="{{ $expense->receipt }}">
                                                                        <i data-feather="download"></i>
                                                                    </a>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            @if (Gate::check('edit expense') || Gate::check('delete expense') || Gate::check('show expense'))
                                                                <td class="text-right">
                                                                    <div class="cart-action">
                                                                        @can('show expense')
                                                                            <a class="avtar avtar-xs btn-link-warning text-warning customModal"
                                                                                data-size="lg" data-bs-toggle="tooltip"
                                                                                data-bs-original-title="{{ __('View') }}"
                                                                                href="#"
                                                                                data-url="{{ route('expense.show', $expense->id) }}"
                                                                                data-title="{{ __('Expense Details') }}"> <i
                                                                                    data-feather="eye"></i></a>
                                                                        @endcan

                                                                    </div>

                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="d-flex wizard justify-content-between flex-wrap gap-2 mt-3">
                                        <div class="first">
                                            <a href="#" class="btn btn-secondary">{{ __('First') }}</a>
                                        </div>
                                        <div class="d-flex">
                                            <div class="previous me-2">
                                                <a href="#"
                                                    class="btn btn-secondary">{{ __('Back To Previous') }}</a>
                                            </div>
                                            <div class="next">
                                                <a href="#" class="btn btn-secondary">{{ __('Next Step') }}</a>
                                            </div>
                                        </div>
                                        <div class="last">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
