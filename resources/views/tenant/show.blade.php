@extends('layouts.app')
@section('page-title')
    {{ __('Tenant Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
    </li>
    <li class="breadcrumb-item" aria-current="page"><a href="{{ route('tenant.index') }}"> {{ __('Tenant') }}</a></li>
    <li class="breadcrumb-item active">
        <a href="#">{{ __('Details') }}</a>
    </li>
@endsection
@push('css-page')
    <style>
        .form-wizard .nav-pills .nav-link.active,
        .form-wizard .nav-pills .show>.nav-link {
            background: var(--bs-secondary) !important;
        }
    </style>
@endpush

@push('script-page')
    <script src="{{ asset('assets/js/plugins/wizard.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            new Wizard('#basicwizard', {
                validate: true,
                progress: true
            });
        });
    </script>
@endpush
@php
    $profile = asset(Storage::url('upload/profile/avatar.png'));
@endphp
@section('content')

    <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-sm-12">

            <div id="basicwizard" class="form-wizard row justify-content-center">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-3">
                            <ul class="nav nav-pills nav-justified">
                                <li class="nav-item">
                                    <a class="nav-link active" id="profile-tab-1" data-bs-toggle="tab" href="#profile-1"
                                        role="tab">
                                        <i class="ph-duotone ph-user"></i>
                                        {{ __('Personal Information') }}
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" id="profile-tab-2" data-bs-toggle="tab" href="#profile-2"
                                        role="tab">
                                        <i class="ph-duotone ph-folder-notch"></i>
                                        {{ __('Document') }}
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" id="profile-tab-3" data-bs-toggle="tab" href="#profile-3"
                                        role="tab">
                                        <i class="ph-duotone ph-clipboard"></i>
                                        {{ __('Agreement') }}
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" id="profile-tab-4" data-bs-toggle="tab" href="#profile-4"
                                        role="tab">

                                        <i class="ph-duotone ph-receipt"></i>
                                        {{ __('Invoice') }}
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" id="profile-tab-5" data-bs-toggle="tab" href="#profile-5"
                                        role="tab">
                                        <i class="ph-duotone ph-map-trifold"></i>
                                        {{ __('Maintenance Request') }}
                                    </a>
                                </li>

                                @if (Gate::check('edit tenant'))
                                    <li class="nav-item">
                                        <a class="nav-link" id="profile-tab-movein" data-bs-toggle="tab" href="#movein" role="tab">
                                            <i class="ph-duotone ph-list-checks"></i> {{ __('Move-in') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="profile-tab-rent" data-bs-toggle="tab" href="#rent" role="tab">
                                            <i class="ph-duotone ph-trend-up"></i> {{ __('Rent') }}
                                        </a>
                                    </li>
                                @endif
                                @can('manage deposit')
                                    <li class="nav-item">
                                        <a class="nav-link" id="profile-tab-6" data-bs-toggle="tab" href="#profile-6"
                                            role="tab">
                                            <i class="ph-duotone ph-vault"></i>
                                            {{ __('Deposit') }}
                                        </a>
                                    </li>
                                @endcan

                            </ul>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="tab-content">
                                <div id="bar" class="progress mb-3" style="height: 7px">
                                    <div class="bar progress-bar progress-bar-striped progress-bar-animated bg-secondary">
                                    </div>
                                </div>

                                <div class="tab-pane show active" id="profile-1" role="tabpanel"
                                    aria-labelledby="profile-tab-1">
                                    <div class="row">
                                        <div class="col-lg-4 col-xxl-3">
                                            <div class="card border">
                                                <div class="card-header">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0">
                                                            <img class="img-radius img-fluid wid-80"
                                                                src= "{{ !empty($tenant->user->profile) ? fetch_file($tenant->user->profile, 'upload/profile/') : $profile }}"
                                                                alt="User image" />
                                                        </div>
                                                        <div class="flex-grow-1 mx-3">
                                                            <h5 class="mb-1">
                                                                {{ ucfirst(!empty($tenant->user) ? $tenant->user->first_name : '') . ' ' . ucfirst(!empty($tenant->user) ? $tenant->user->last_name : '') }}
                                                            </h5>
                                                            <h6 class="mb-0 text-secondary">{!! $tenant->LeaseLeftDay() !!}</h6>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="card-body px-2 pb-0">
                                                    <div class="list-group list-group-flush">
                                                        <a href="#" class="list-group-item list-group-item-action">
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-shrink-0">
                                                                    <i class="material-icons-two-tone f-20">email</i>
                                                                </div>
                                                                <div class="flex-grow-1 mx-3">
                                                                    <h5 class="m-0">{{ __('Email') }}</h5>
                                                                </div>
                                                                <div class="flex-shrink-0">
                                                                    <small>{{ !empty($tenant->user) ? $tenant->user->email : '-' }}</small>
                                                                </div>
                                                            </div>
                                                        </a>
                                                        <a href="#" class="list-group-item list-group-item-action">
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-shrink-0">
                                                                    <i
                                                                        class="material-icons-two-tone f-20">phonelink_ring</i>
                                                                </div>
                                                                <div class="flex-grow-1 mx-3">
                                                                    <h5 class="m-0">{{ __('Phone') }}</h5>
                                                                </div>
                                                                <div class="flex-shrink-0">
                                                                    <small>{{ !empty($tenant->user) ? $tenant->user->phone_number : '-' }}
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        {{-- @dd($tenant->units) --}}
                                        <div class="col-lg-8 col-xxl-9">
                                            <div class="card border">
                                                <div class="card-header">
                                                    <div class="row align-items-center g-2">
                                                        <div class="col">
                                                            <h5>{{ __('Additional Information') }}</h5>
                                                            @if ($tenant->autopay_enabled)
                                                                <span class="badge bg-light-success"><i class="ti ti-credit-card"></i> {{ __('Autopay on') }}: {{ ucfirst($tenant->card_brand) }} **** {{ $tenant->card_last4 }}</span>
                                                            @endif
                                                        </div>

                                                        @if (\Auth::user()->type == 'owner' && $tenant->units && $tenant->units->is_occupied == 1)
                                                            <div class="col-auto">
                                                                <a class="btn btn-light-danger customModal" href="#"
                                                                    data-url="{{ route('tenant.exit', $tenant->id) }}"
                                                                    data-title="{{ __('Exit Tenant') }}">
                                                                    {{ __('Exit Tenant') }}
                                                                </a>

                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="card-body">

                                                    <div class="row g-3">

                                                        <div class="col-md-6">
                                                            <small
                                                                class="text-muted">{{ __('Total Family Member') }}</small>
                                                            <div class="fw-semibold">{{ $tenant->family_member ?? '-' }}
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <small class="text-muted">{{ __('Country') }}</small>
                                                            <div class="fw-semibold">{{ $tenant->country ?? '-' }}</div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <small class="text-muted">{{ __('State') }}</small>
                                                            <div class="fw-semibold">{{ $tenant->state ?? '-' }}</div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <small class="text-muted">{{ __('City') }}</small>
                                                            <div class="fw-semibold">{{ $tenant->city ?? '-' }}</div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <small class="text-muted">{{ __('Zip Code') }}</small>
                                                            <div class="fw-semibold">{{ $tenant->zip_code ?? '-' }}</div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <small class="text-muted">{{ __('Property') }}</small>
                                                            <div class="fw-semibold">
                                                                {{ $tenant->properties->name ?? '-' }}</div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <small class="text-muted">{{ __('Unit') }}</small>
                                                            <div class="fw-semibold">{{ $tenant->units->name ?? '-' }}
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <small class="text-muted">{{ __('Lease Start Date') }}</small>
                                                            <div class="fw-semibold">
                                                                {{ dateFormat($tenant->lease_start_date) }}
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <small class="text-muted">{{ __('Lease End Date') }}</small>
                                                            <div class="fw-semibold">
                                                                {{ dateFormat($tenant->lease_end_date) }}
                                                            </div>
                                                        </div>

                                                        <div class="col-12">
                                                            <small class="text-muted">{{ __('Address') }}</small>
                                                            <div class="fw-semibold">{{ $tenant->address ?? '-' }}</div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="profile-2" role="tabpanel" aria-labelledby="profile-tab-2">
                                    @can('edit tenant')
                                        <div class="text-end mb-3">
                                            <a href="#" class="btn btn-secondary btn-sm customModal" data-size="lg"
                                                data-url="{{ route('tenant.document.create', $tenant->id) }}"
                                                data-title="{{ __('Upload Document') }}">
                                                <i class="ti ti-upload"></i> {{ __('Upload Document') }}</a>
                                        </div>
                                    @endcan
                                    <div class="row">
                                        @if (!empty($tenant->documents) && $tenant->documents->count())
                                            <div class="col-sm-12">

                                                <div class="row">
                                                    @foreach ($tenant->documents as $doc)
                                                        @php
                                                            $folder = 'upload/tenant/';
                                                            $filename = $doc->document;
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
                                                                <div class="px-2 pt-2 small">
                                                                    <div class="fw-semibold text-truncate" title="{{ $doc->title }}">{{ $doc->title ?: __('Document') }}</div>
                                                                    @if (!empty($doc->document_type))
                                                                        <div class="text-muted">{{ \App\Models\TenantDocument::documentTypes()[$doc->document_type] ?? '' }}</div>
                                                                    @endif
                                                                    @if (!empty($doc->expiry_date))
                                                                        <span class="badge {{ $doc->isExpired() ? 'bg-light-danger' : 'bg-light-success' }}">
                                                                            {{ $doc->isExpired() ? __('Expired') : __('Expires') }} {{ dateFormat($doc->expiry_date) }}</span>
                                                                    @endif
                                                                </div>
                                                                <hr>
                                                                <div class="d-flex justify-content-center gap-2">
                                                                    @can('edit tenant')
                                                                        <a href="#" class="avtar btn-link-secondary text-secondary p-0 customModal" data-size="lg"
                                                                            data-url="{{ route('tenant.document.edit', $doc->id) }}"
                                                                            data-title="{{ __('Edit Document') }}" title="{{ __('Edit') }}">
                                                                            <i class="ti ti-edit"></i></a>
                                                                    @endcan
                                                                    <a href="{{ $fileUrl }}" download
                                                                        title="Download"
                                                                        class="avtar btn-link-success text-success p-0">
                                                                        <i class="ti ti-download "></i>
                                                                    </a>

                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['tenant.document.delete', $doc->id],
                                                                        'id' => 'doc-' . $doc->id,
                                                                    ]) !!}
                                                                    <a class="avtar  btn-link-danger text-danger confirm_dialog p-0 confirm_dialog"
                                                                        href="#"><i
                                                                            class="ti ti-trash text-danger"></i>
                                                                    </a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="tab-pane" id="profile-3" role="tabpanel" aria-labelledby="profile-tab-3">
                                    <div class="card-body pt-0">
                                        <div class="dt-responsive table-responsive">
                                            <table class="table table-hover ">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Agreement') }}</th>
                                                        <th>{{ __('Property') }}</th>
                                                        <th>{{ __('Unit') }}</th>
                                                        <th>{{ __('Date') }}</th>
                                                        <th>{{ __('Status') }}</th>
                                                        @if (Gate::check('show agreement'))
                                                            <th class="text-right">{{ __('Action') }}</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($agreements as $agreement)
                                                        <tr>
                                                            <td>{{ agreementPrefix() . $agreement->agreement_id }} </td>
                                                            <td>{{ !empty($agreement->properties) ? $agreement->properties->name : '-' }}
                                                            </td>
                                                            <td>{{ !empty($agreement->units) ? $agreement->units->name : '-' }}
                                                            </td>
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

                                                            @if (Gate::check('show agreement'))
                                                                <td>
                                                                    <div class="cart-action">
                                                                        @can('show agreement')
                                                                            <a class="avtar avtar-xs btn-link-warning text-warning"
                                                                                href="{{ route('agreement.show', \Crypt::encrypt($agreement->id)) }}"
                                                                                data-bs-toggle="tooltip"
                                                                                data-bs-original-title="{{ __('View') }}">
                                                                                <i data-feather="eye"></i></a>
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
                                </div>

                                <div class="tab-pane" id="profile-4" role="tabpanel" aria-labelledby="profile-tab-4">
                                    <div class="col-sm-12">
                                        <div class="card table-card">

                                            <div class="card-body pt-0">
                                                <div class="dt-responsive table-responsive">
                                                    <table class="table table-hover ">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('Invoice') }}</th>
                                                                <th>{{ __('Property') }}</th>
                                                                <th>{{ __('Unit') }}</th>
                                                                <th>{{ __('Invoice Month') }}</th>
                                                                <th>{{ __('End Date') }}</th>
                                                                <th>{{ __('Amount') }}</th>
                                                                <th>{{ __('Status') }}</th>
                                                                @if (Gate::check('show invoice'))
                                                                    <th class="text-right">{{ __('Action') }}</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($invoices as $invoice)
                                                                <tr>
                                                                    <td>{{ invoicePrefix() . $invoice->invoice_id }} </td>
                                                                    <td>{{ !empty($invoice->properties) ? $invoice->properties->name : '-' }}
                                                                    </td>
                                                                    <td>{{ !empty($invoice->units) ? $invoice->units->name : '-' }}
                                                                    </td>
                                                                    <td>{{ date('F Y', strtotime($invoice->invoice_month)) }}
                                                                    </td>
                                                                    <td>{{ dateFormat($invoice->end_date) }} </td>
                                                                    <td>{{ priceFormat($invoice->getInvoiceSubTotalAmount()) }}
                                                                    </td>
                                                                    <td>
                                                                        @if ($invoice->status == 'unpaid')
                                                                            <span
                                                                                class="badge bg-danger">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                                                                        @elseif($invoice->status == 'paid')
                                                                            <span
                                                                                class="badge bg-success">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                                                                        @elseif($invoice->status == 'partial_paid')
                                                                            <span
                                                                                class="badge bg-warning">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                                                                        @endif
                                                                    </td>
                                                                    @if (Gate::check('show invoice'))
                                                                        <td>
                                                                            <div class="cart-action">
                                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.destroy', $invoice->id]]) !!}

                                                                                @can('show invoice')
                                                                                    <a class="avtar avtar-xs btn-link-warning text-warning"
                                                                                        href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                                                        data-bs-toggle="tooltip"
                                                                                        data-bs-original-title="{{ __('View') }}">
                                                                                        <i data-feather="eye"></i></a>
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

                                <div class="tab-pane" id="profile-5" role="tabpanel" aria-labelledby="profile-tab-6">

                                    <div class="card-body pt-0">
                                        <div class="dt-responsive table-responsive">
                                            <table class="table table-hover ">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Property') }}</th>
                                                        <th>{{ __('Unit') }}</th>
                                                        <th>{{ __('Issue') }}</th>
                                                        <th>{{ __('Maintainer') }}</th>
                                                        <th>{{ __('Request Date') }}</th>
                                                        <th>{{ __('Status') }}</th>
                                                        <th>{{ __('Attachment') }}</th>
                                                        @if (Gate::check('show maintenance request'))
                                                            <th class="text-right">{{ __('Action') }}</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($maintenanceRequests as $request)
                                                        <tr role="row">
                                                            <td> {{ !empty($request->properties) ? $request->properties->name : '-' }}
                                                            </td>
                                                            <td> {{ !empty($request->units) ? $request->units->name : '-' }}
                                                            </td>
                                                            <td> {{ !empty($request->types) ? $request->types->title : '-' }}
                                                            </td>
                                                            <td> {{ !empty($request->maintainers) ? $request->maintainers->name : '-' }}
                                                            </td>
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
                                                                        download="download"><i
                                                                            class="ti ti-download"></i></a>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            @if (Gate::check('show maintenance request'))
                                                                <td class="text-right">
                                                                    <div class="cart-action">
                                                                        @can('show maintenance request')
                                                                            <a class="avtar avtar-xs btn-link-warning text-warning"
                                                                                href="{{ route('maintenance-request.show', \Crypt::encrypt($request->id)) }}"
                                                                                data-title="{{ __('Maintenance Request Details') }}">
                                                                                <i data-feather="eye"></i></a>
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
                                </div>
                                @can('manage deposit')
                                    @php
                                        $deposits = $tenant->deposits;
                                        $depositReceived = $deposits->where('type', 'received')->sum('amount');
                                        $depositDeducted = $deposits->where('type', 'deduction')->sum('amount');
                                        $depositRefunded = $deposits->where('type', 'refund')->sum('amount');
                                        $depositTypes = \App\Models\DepositTransaction::types();
                                    @endphp
                                    <div class="tab-pane" id="profile-6" role="tabpanel" aria-labelledby="profile-tab-6">
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-3 col-6">
                                                <div class="border rounded p-3"><div class="text-muted small">{{ __('Received') }}</div><h5 class="mb-0">{{ priceFormat($depositReceived) }}</h5></div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="border rounded p-3"><div class="text-muted small">{{ __('Deducted') }}</div><h5 class="mb-0">{{ priceFormat($depositDeducted) }}</h5></div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="border rounded p-3"><div class="text-muted small">{{ __('Refunded') }}</div><h5 class="mb-0">{{ priceFormat($depositRefunded) }}</h5></div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="border rounded p-3 bg-light-secondary"><div class="text-muted small">{{ __('Held') }}</div><h5 class="mb-0">{{ priceFormat($depositReceived - $depositDeducted - $depositRefunded) }}</h5></div>
                                            </div>
                                        </div>
                                        @can('create deposit')
                                            <div class="text-end mb-2">
                                                <a href="#" class="btn btn-secondary btn-sm customModal" data-size="md"
                                                    data-url="{{ route('tenant.deposit.create', $tenant->id) }}"
                                                    data-title="{{ __('Add Deposit Entry') }}">
                                                    <i class="ti ti-plus"></i> {{ __('Add Entry') }}</a>
                                            </div>
                                        @endcan
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Date') }}</th>
                                                        <th>{{ __('Type') }}</th>
                                                        <th>{{ __('Amount') }}</th>
                                                        <th>{{ __('Notes') }}</th>
                                                        @can('delete deposit')
                                                            <th class="text-right">{{ __('Action') }}</th>
                                                        @endcan
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($deposits as $deposit)
                                                        <tr>
                                                            <td>{{ dateFormat($deposit->date) }}</td>
                                                            <td>
                                                                <span class="badge {{ $deposit->type == 'received' ? 'bg-light-success' : ($deposit->type == 'refund' ? 'bg-light-info' : 'bg-light-warning') }}">
                                                                    {{ $depositTypes[$deposit->type] ?? $deposit->type }}</span>
                                                            </td>
                                                            <td>{{ $deposit->type == 'received' ? '' : '-' }}{{ priceFormat($deposit->amount) }}</td>
                                                            <td>{{ $deposit->notes }}</td>
                                                            @can('delete deposit')
                                                                <td class="text-right">
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['tenant.deposit.destroy', $deposit->id]]) !!}
                                                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#"
                                                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}">
                                                                        <i data-feather="trash-2"></i></a>
                                                                    {!! Form::close() !!}
                                                                </td>
                                                            @endcan
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted">{{ __('No deposit entries yet.') }}</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endcan
                                @if (Gate::check('edit tenant'))
                                    <div class="tab-pane" id="movein" role="tabpanel">@include('tenant._movein')</div>
                                    <div class="tab-pane" id="rent" role="tabpanel">@include('tenant._rent')</div>
                                @endif
                                <div class="d-flex wizard justify-content-between flex-wrap gap-2 mt-3">
                                    <div class="first">
                                        <a href="#" class="btn btn-secondary">{{ __('First') }}</a>
                                    </div>
                                    <div class="d-flex">
                                        <div class="previous me-2">
                                            <a href="#" class="btn btn-secondary">{{ __('Back To Previous') }}</a>
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


@endsection

@push('script-page')
    <script>
        // open the tab named in the URL (#movein, #rent) after an action redirects back here
        $(function() {
            if (location.hash && $('a[data-bs-toggle="tab"][href="' + location.hash + '"]').length) {
                bootstrap.Tab.getOrCreateInstance($('a[data-bs-toggle="tab"][href="' + location.hash + '"]')[0]).show();
            }
        });
    </script>
@endpush
