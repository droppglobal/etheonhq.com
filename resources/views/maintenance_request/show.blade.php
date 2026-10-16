@extends('layouts.app')
@section('page-title')
    {{ __('Maintenance Request Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
    </li>
    {{-- <li class="breadcrumb-item" aria-current="page"><a href="{{ route('maintenance-request.pending') }}"> {{ __('Maintenance Request') }}</a></li> --}}
    <li class="breadcrumb-item active">
        <a href="#">{{ __('Maintenance Request Details') }}</a>
    </li>
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
@php
    $profile = asset(Storage::url('upload/profile/avatar.png'));
@endphp
@section('content')
    <div class="row">
        <div class="col-sm-12">

            <div id="basicwizard" class="form-wizard row justify-content-center">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-3">
                            <ul class="nav nav-pills nav-justified">
                                <li class="nav-item" data-target-form="#basicDetailForm">
                                    <a href="#basicDetail" data-bs-toggle="tab" data-toggle="tab" class="nav-link active">
                                        <i class="ph-duotone ph-user-circle"></i>
                                        <span class="d-none d-sm-inline">{{ __('Basic Details') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item" data-target-form="#productDetailForm">
                                    <a href="#productDetail" data-bs-toggle="tab" data-toggle="tab"
                                        class="nav-link icon-btn">
                                        <i class="ph-duotone ph-circles-three-plus"></i>
                                        <span class="d-none d-sm-inline">{{ __('Add Comment') }}</span>
                                    </a>
                                </li>

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

                                <div class="tab-pane show active" id="basicDetail">
                                    <div id='basicDetailForm'>
                                        <div class="row">
                                            <div class="col-lg-4 col-xxl-3">
                                                <div class="card border">
                                                    <div class="card-header">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <img class="img-radius img-fluid wid-80"
                                                                    src= "{{ !empty($maintainer->user->profile) ? fetch_file($maintainer->user->profile, 'upload/profile/') : $profile }}"
                                                                    alt="User image" />
                                                            </div>
                                                            <div class="flex-grow-1 mx-3">
                                                                <h4 class="mb-2">
                                                                    {{ ucfirst(!empty($maintainer->user) ? $maintainer->user->first_name : '') . ' ' . ucfirst(!empty($maintainer->user) ? $maintainer->user->last_name : '') }}
                                                                </h4>
                                                                <h5 class="mt-1"><span
                                                                        class="badge bg-light-secondary">{{ ucfirst(!empty($maintainer->user) ? $maintainer->user->type : '') }}</span>
                                                                </h5>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <div class="card-body px-2 pb-0">
                                                        <div class="list-group list-group-flush">
                                                            <a href="#"
                                                                class="list-group-item list-group-item-action">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0">
                                                                        <i class="material-icons-two-tone f-20">email</i>
                                                                    </div>
                                                                    <div class="flex-grow-1 mx-3">
                                                                        <h5 class="m-0">{{ __('Email') }}</h5>
                                                                    </div>
                                                                    <div class="flex-shrink-0">
                                                                        <small>{{ !empty($maintainer->user) ? $maintainer->user->email : '-' }}</small>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                            <a href="#"
                                                                class="list-group-item list-group-item-action">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0">
                                                                        <i
                                                                            class="material-icons-two-tone f-20">phonelink_ring</i>
                                                                    </div>
                                                                    <div class="flex-grow-1 mx-3">
                                                                        <h5 class="m-0">{{ __('Phone') }}</h5>
                                                                    </div>
                                                                    <div class="flex-shrink-0">
                                                                        <small>{{ !empty($maintainer->user) ? $maintainer->user->phone_number : '-' }}
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </a>


                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-lg-8 col-xxl-9">
                                                <div class="card border">
                                                    <div class="card-header">
                                                        <h5>{{ __('Additional Information') }}</h5>
                                                    </div>
                                                    <div class="card-body">

                                                        <div class="table-responsive">
                                                            <table class="table table-borderless">
                                                                <tbody>
                                                                    <tr>
                                                                        <td><b class="text-header">{{ __('Property') }}</b>
                                                                        </td>
                                                                        <td>:</td>
                                                                        <td>{{ !empty($maintenanceRequest->properties) ? $maintenanceRequest->properties->name : '-' }}
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><b class="text-header">{{ __('Unit') }}</b>
                                                                        </td>
                                                                        <td>:</td>
                                                                        <td> {{ !empty($maintenanceRequest->units) ? $maintenanceRequest->units->name : '-' }}
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><b class="text-header">{{ __('Issue') }}</b>
                                                                        </td>
                                                                        <td>:</td>
                                                                        <td>{{ !empty($maintenanceRequest->types) ? $maintenanceRequest->types->title : '-' }}
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td><b
                                                                                class="text-header">{{ __('Request Date') }}</b>
                                                                        </td>
                                                                        <td>:</td>
                                                                        <td>{{ dateFormat($maintenanceRequest->request_date) }}
                                                                        </td>
                                                                    </tr>
                                                                    @if (!empty($maintenanceRequest->fixed_date))
                                                                        <tr>
                                                                            <td><b
                                                                                    class="text-header">{{ __('Fixed Date') }}</b>
                                                                            </td>
                                                                            <td>:</td>
                                                                            <td>{{ dateFormat($maintenanceRequest->fixed_date) }}
                                                                            </td>
                                                                        </tr>
                                                                    @endif
                                                                    @if ($maintenanceRequest->amount != 0)
                                                                        <tr>
                                                                            <td><b
                                                                                    class="text-header">{{ __('Amount') }}</b>
                                                                            </td>
                                                                            <td>:</td>
                                                                            <td>{{ priceFormat($maintenanceRequest->amount) }}
                                                                            </td>
                                                                        </tr>
                                                                    @endif

                                                                    <tr>
                                                                        <td><b class="text-header">{{ __('Status') }}</b>
                                                                        </td>
                                                                        <td>:</td>
                                                                        <td>
                                                                            @if ($maintenanceRequest->status == 'pending')
                                                                                <span class="badge bg-light-warning">
                                                                                    {{ \App\Models\MaintenanceRequest::status()[$maintenanceRequest->status] }}</span>
                                                                            @elseif($maintenanceRequest->status == 'in_progress')
                                                                                <span class="badge bg-light-info">
                                                                                    {{ \App\Models\MaintenanceRequest::status()[$maintenanceRequest->status] }}</span>
                                                                            @else
                                                                                <span class="badge bg-light-primary">
                                                                                    {{ \App\Models\MaintenanceRequest::status()[$maintenanceRequest->status] }}</span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>

                                                                    @if ($maintenanceRequest->preferredVisitText())
                                                                        <tr>
                                                                            <td><b class="text-header">{{ __('Preferred visit') }}</b></td>
                                                                            <td>:</td>
                                                                            <td>{{ $maintenanceRequest->preferredVisitText() }}</td>
                                                                        </tr>
                                                                    @endif
                                                                    @if ($maintenanceRequest->on_the_way_at || ($maintenanceRequest->maintainer_id == \Auth::id() && in_array($maintenanceRequest->status, ['pending', 'in_progress'])))
                                                                        <tr>
                                                                            <td><b class="text-header">{{ __('Visit') }}</b></td>
                                                                            <td>:</td>
                                                                            <td class="d-flex flex-wrap align-items-center gap-2">
                                                                                @if ($maintenanceRequest->on_the_way_at)
                                                                                    <span class="badge bg-light-info"><i class="ti ti-truck"></i> {{ __('On the way since') }} {{ dateFormat($maintenanceRequest->on_the_way_at) }} {{ \Carbon\Carbon::parse($maintenanceRequest->on_the_way_at)->format('H:i') }}</span>
                                                                                @endif
                                                                                @if ($maintenanceRequest->maintainer_id == \Auth::id() && in_array($maintenanceRequest->status, ['pending', 'in_progress']))
                                                                                    {{ Form::open(['route' => ['maintenance-request.on-the-way', $maintenanceRequest->id], 'method' => 'post', 'class' => 'd-inline']) }}
                                                                                    <button class="btn btn-sm btn-secondary"><i class="ti ti-truck"></i> {{ $maintenanceRequest->on_the_way_at ? __('Tell tenant again') : __("I'm on my way") }}</button>
                                                                                    {{ Form::close() }}
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endif

                                                                    @if (!empty($maintenanceRequest->invoice))
                                                                        <tr>
                                                                            <td><b
                                                                                    class="text-header">{{ __('Invoice') }}</b>
                                                                            </td>
                                                                            <td>:</td>
                                                                            <td><a href="{{ !empty($maintenanceRequest->invoice) ? fetch_file($maintenanceRequest->invoice, 'upload/invoice/') : '#' }}"
                                                                                    download="download"><i
                                                                                        class="fa fa-download"></i></a></td>
                                                                        </tr>
                                                                    @endif

                                                                    <tr>
                                                                        <td><b
                                                                                class="text-header">{{ __('Attachment') }}</b>
                                                                        </td>
                                                                        <td>:</td>
                                                                        <td>
                                                                            @if (!empty($maintenanceRequest->issue_attachment))
                                                                                <a href="{{ !empty($maintenanceRequest->issue_attachment) ? fetch_file($maintenanceRequest->issue_attachment, 'upload/issue_attachment/') : '#' }}"
                                                                                    download="download"><i
                                                                                        class="fa fa-download"></i></a>
                                                                            @else
                                                                                -
                                                                            @endif
                                                                        </td>
                                                                    </tr>

                                                                    @if (!empty($maintenanceRequest->ai_priority))
                                                                        <tr>
                                                                            <td><b class="text-header">{{ __('Priority') }}</b></td>
                                                                            <td>:</td>
                                                                            <td><span class="badge bg-light-{{ \App\Support\MaintenanceTriage::color($maintenanceRequest->ai_priority) }}">{{ \App\Support\MaintenanceTriage::priorities()[$maintenanceRequest->ai_priority] ?? '' }}</span>
                                                                                {{ $maintenanceRequest->ai_summary }}
                                                                                @if ($maintenanceRequest->source != 'web')<small class="text-muted">({{ __('via') }} {{ $maintenanceRequest->source }})</small>@endif</td>
                                                                        </tr>
                                                                    @endif
                                                                    @if (!empty($maintenanceRequest->vendor))
                                                                        <tr>
                                                                            <td><b class="text-header">{{ __('Outside Vendor') }}</b></td>
                                                                            <td>:</td>
                                                                            <td>{{ $maintenanceRequest->vendor->name }} {{ $maintenanceRequest->vendor->phone ? '(' . $maintenanceRequest->vendor->phone . ')' : '' }}</td>
                                                                        </tr>
                                                                    @endif
                                                                    @if ($maintenanceRequest->labor_hours > 0)
                                                                        <tr>
                                                                            <td><b class="text-header">{{ __('Hours Worked') }}</b></td>
                                                                            <td>:</td>
                                                                            <td>{{ (float) $maintenanceRequest->labor_hours }}</td>
                                                                        </tr>
                                                                    @endif
                                                                    @if ($maintenanceRequest->tenant_rating > 0)
                                                                        <tr>
                                                                            <td><b class="text-header">{{ __('Tenant Rating') }}</b></td>
                                                                            <td>:</td>
                                                                            <td><span class="text-warning">{{ str_repeat('★', $maintenanceRequest->tenant_rating) }}</span><span class="text-muted">{{ str_repeat('★', 5 - $maintenanceRequest->tenant_rating) }}</span>
                                                                                {{ $maintenanceRequest->tenant_feedback }}</td>
                                                                        </tr>
                                                                    @elseif (\Auth::user()->type == 'tenant' && $maintenanceRequest->status == 'completed')
                                                                        <tr>
                                                                            <td><b class="text-header">{{ __('Rate this repair') }}</b></td>
                                                                            <td>:</td>
                                                                            <td>
                                                                                {{ Form::open(['route' => ['maintenance-request.rate', $maintenanceRequest->id], 'method' => 'post', 'class' => 'd-flex flex-wrap gap-2 align-items-center']) }}
                                                                                {{ Form::select('tenant_rating', [5 => '★★★★★', 4 => '★★★★', 3 => '★★★', 2 => '★★', 1 => '★'], 5, ['class' => 'form-control form-control-sm', 'style' => 'width:auto']) }}
                                                                                {{ Form::text('tenant_feedback', null, ['class' => 'form-control form-control-sm', 'style' => 'width:auto', 'placeholder' => __('Comment (optional)')]) }}
                                                                                <button type="submit" class="btn btn-secondary btn-sm">{{ __('Submit') }}</button>
                                                                                {{ Form::close() }}
                                                                            </td>
                                                                        </tr>
                                                                    @endif

                                                                    <tr>
                                                                        <td><b class="text-header">{{ __('Notes') }}</b>
                                                                        </td>
                                                                        <td>:</td>
                                                                        <td> {{ $maintenanceRequest->notes }}</td>
                                                                    </tr>

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                                <div class="tab-pane" id="productDetail">
                                    <div id='productDetailForm'>

                                        @if ($maintenanceRequest->comments->count() > 0)
                                            @foreach ($maintenanceRequest->comments as $comment)
                                                @php
                                                    $user = App\Models\User::find($comment->user_id);
                                                @endphp

                                                <div class="bg-light rounded p-2 mb-3 position-relative">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="flex-shrink-0">
                                                            <img class="img-radius img-fluid wid-40"
                                                                src="{{ !empty($user->profile)
                                                                    ? asset(Storage::url('upload/profile/' . $user->profile))
                                                                    : asset(Storage::url('upload/profile/avatar.png')) }}"
                                                                alt="profile" />
                                                        </div>

                                                        <div class="flex-grow-1 mx-3">
                                                            <div class="d-flex align-items-center">
                                                                <h5 class="mb-0 me-3">{{ $user->name ?? '-' }}
                                                                </h5>
                                                                <span
                                                                    class="text-body text-opacity-50 d-flex align-items-center">
                                                                    <i class="fas fa-circle f-8 me-2"></i>
                                                                    {{ dateFormat($comment->created_at) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <p class="text-header">{{ $comment->comment }}</p>

                                                    @if (\Auth::user()->id === $comment->user_id || \Auth::user()->type === 'owner')
                                                        <div class="position-absolute top-0 end-0 m-2">
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['maintenance-request.comment.destroy', $comment->id],
                                                                'id' => 'delete-comment-' . $comment->id,
                                                            ]) !!}
                                                            <button type="button"
                                                                class="btn btn-sm btn-light text-danger border-0 p-1 confirm_dialog"
                                                                data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @endif


                                        {{ Form::open(['route' => ['maintenance-request.comment', $maintenanceRequest->id], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                                        <div class="d-flex align-items-center mt-3">
                                            <div class="flex-shrink-0">
                                                <img class="img-radius d-none d-sm-inline-flex me-3 img-fluid wid-35"
                                                    src="{{ !empty($maintainer->user) && !empty($maintainer->user->profile)
                                                        ? asset(Storage::url('upload/profile/' . $maintainer->user->profile))
                                                        : asset('assets/images/avatar.png') }}"
                                                    alt="{{ $maintainer->user->first_name ?? 'User' }}" />
                                            </div>
                                            <div class="flex-grow-1 me-3">
                                                <textarea class="form-control" rows="1" name="comment" placeholder="{{ __('Write a comment...') }}"></textarea>

                                            </div>

                                            <div class="flex-shrink-0">
                                                <button type="submit"
                                                    class="btn btn-secondary">{{ __('Send') }}</button>
                                            </div>
                                        </div>
                                        {{ Form::close() }}

                                    </div>
                                </div>

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
    @php
        $isImage = fn($file) => !empty($file) && preg_match('/\.(jpe?g|png|gif|webp)$/i', $file);
        $beforeImg = $isImage($maintenanceRequest->issue_attachment) ? fetch_file($maintenanceRequest->issue_attachment, 'upload/issue_attachment/') : null;
        $afterImg = $isImage($maintenanceRequest->after_photo) ? fetch_file($maintenanceRequest->after_photo, 'upload/issue_attachment/') : null;
    @endphp
    @if ($beforeImg || $afterImg)
        <div class="row">
            <div class="col-xl-6 col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $beforeImg && $afterImg ? __('Before & After') : ($afterImg ? __('After the repair') : __('Reported problem')) }}</h5>
                        @if ($beforeImg && $afterImg)<small class="text-muted">{{ __('Drag the slider to compare') }}</small>@endif
                    </div>
                    <div class="card-body">
                        @if ($beforeImg && $afterImg)
                            <div class="ba-compare" id="ba-compare">
                                <img src="{{ $afterImg }}" alt="{{ __('After') }}">
                                <img src="{{ $beforeImg }}" alt="{{ __('Before') }}" class="ba-before">
                                <span class="ba-label ba-label-before">{{ __('Before') }}</span>
                                <span class="ba-label ba-label-after">{{ __('After') }}</span>
                                <div class="ba-handle"></div>
                                <input type="range" min="0" max="100" value="50" aria-label="{{ __('Compare before and after') }}">
                            </div>
                        @else
                            <a href="{{ $beforeImg ?: $afterImg }}" target="_blank"><img src="{{ $beforeImg ?: $afterImg }}" class="img-fluid rounded" style="max-height:420px" alt=""></a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('css-page')
    <style>
        .ba-compare { position: relative; overflow: hidden; border-radius: 10px; aspect-ratio: 4 / 3; background: #f1f3f6; user-select: none; }
        .ba-compare img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .ba-compare .ba-before { clip-path: inset(0 50% 0 0); }
        .ba-compare .ba-handle { position: absolute; top: 0; bottom: 0; left: 50%; width: 3px; background: #fff; box-shadow: 0 0 6px rgba(0,0,0,.4); pointer-events: none; }
        .ba-compare input[type=range] { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: ew-resize; margin: 0; }
        .ba-label { position: absolute; top: 10px; padding: 2px 10px; border-radius: 12px; background: rgba(0,0,0,.55); color: #fff; font-size: 12px; pointer-events: none; }
        .ba-label-before { left: 10px; } .ba-label-after { right: 10px; }
    </style>
@endpush
@push('script-page')
    <script>
        (function() {
            var box = document.getElementById('ba-compare');
            if (!box) return;
            var range = box.querySelector('input'), before = box.querySelector('.ba-before'), handle = box.querySelector('.ba-handle');
            range.addEventListener('input', function() {
                before.style.clipPath = 'inset(0 ' + (100 - this.value) + '% 0 0)';
                handle.style.left = this.value + '%';
            });
        })();
    </script>
@endpush
