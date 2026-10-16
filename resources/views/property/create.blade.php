@extends('layouts.app')
@section('page-title')
    {{ __('Property Create') }}
@endsection


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">
        <a href="{{ route('property.index') }}">{{ __('Property') }}</a>
    </li>
    <li class="breadcrumb-item active"><a href="#">{{ __('Create') }}</a>
    </li>
@endsection

@section('content')
    <div class="row">

        {{ Form::open(['url' => 'property', 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'property_form']) }}
        <div id="basicwizard" class="form-wizard row justify-content-center">
            <div class="col-12">

                <div class="card">
                   <div class="card-body p-3">
                        <ul class="nav nav-pills nav-justified">

                            <li class="nav-item" data-step="1" data-target-form="#propertyDetailForm" role="presentation">
                                <a href="#profile-1" data-bs-toggle="tab" class="nav-link active" aria-selected="true"
                                    role="tab">
                                    <i class="ph-duotone ph-house-line"></i>
                                    <span class="d-none d-sm-inline">{{ __('Property Details') }}</span>
                                </a>
                            </li>
                            <li class="nav-item" data-step="2" data-target-form="#propertyImagesForm" role="presentation">
                                <a href="#profile-2" data-bs-toggle="tab" class="nav-link icon-btn" aria-selected="false"
                                    tabindex="-1" role="tab">
                                    <i class="ph-duotone ph-image"></i>
                                    <span class="d-none d-sm-inline">{{ __('Property Images') }}</span>
                                </a>
                            </li>
                            <li class="nav-item" data-step="3" data-target-form="#unitDetailForm" role="presentation">
                                <a href="#profile-3" data-bs-toggle="tab" class="nav-link icon-btn" aria-selected="false"
                                    tabindex="-1" role="tab">
                                    <i class="ph-duotone ph-cardholder"></i>
                                    <span class="d-none d-sm-inline">{{ __('Units') }}</span>
                                </a>
                            </li>
                            <li class="nav-item" data-step="4" data-target-form="#amenitiesForm" role="presentation">
                                <a href="#profile-4" data-bs-toggle="tab" class="nav-link icon-btn" aria-selected="false"
                                    tabindex="-1" role="tab">
                                    <i class="ph-duotone ph-faders"></i>
                                    <span class="d-none d-sm-inline">{{ __('Amenities') }}</span>
                                </a>
                            </li>
                            <li class="nav-item" data-step="5" data-target-form="#advantagesForm" role="presentation">
                                <a href="#profile-5" data-bs-toggle="tab" class="nav-link icon-btn" aria-selected="false"
                                    tabindex="-1" role="tab">
                                    <i class="ph-duotone ph-thumbs-up"></i>
                                    <span class="d-none d-sm-inline">{{ __('Advantages') }}</span>
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
                            <div class="tab-pane show active" id="profile-1" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row align-items-center g-2">
                                            <div class="col">

                                            </div>
                                            <div class="col-auto">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="mb-3">
                                                    <div class="form-group ">
                                                        {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
                                                        {{ Form::select('type', $types, null, ['class' => 'form-control hidesearch', 'required' => 'required']) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="mb-3">
                                                    <div class="form-group">
                                                        {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                                        {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Property Name'), 'required' => 'required']) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="mb-3">
                                                    <div class="form-group">
                                                        {{ Form::label('thumbnail', __('Thumbnail Image'), ['class' => 'form-label']) }}
                                                        {{ Form::file('thumbnail', ['class' => 'form-control']) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="mb-3">
                                                    <div class="form-group ">
                                                        {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                                                        {{ Form::textarea('description', null, ['class' => 'form-control classic-editor', 'rows' => 4, 'placeholder' => __('Enter Property Description')]) }}
                                                        @include('property._ai_writer', ['propertyId' => null])
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="mb-3">
                                                    <div class="form-group">
                                                        {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}
                                                        {{ Form::text('country', null, ['class' => 'form-control', 'placeholder' => __('Enter Property Country'), 'required' => 'required']) }}
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="mb-3">
                                                    <div class="form-group">
                                                        {{ Form::label('state', __('State'), ['class' => 'form-label']) }}
                                                        {{ Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('Enter Property State'), 'required' => 'required']) }}
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="mb-3">
                                                    <div class="form-group">
                                                        {{ Form::label('city', __('City'), ['class' => 'form-label']) }}
                                                        {{ Form::text('city', null, ['class' => 'form-control', 'placeholder' => __('Enter Property City'), 'required' => 'required']) }}
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="mb-3">
                                                    <div class="form-group">
                                                        {{ Form::label('zip_code', __('Zip Code'), ['class' => 'form-label']) }}
                                                        {{ Form::text('zip_code', null, ['class' => 'form-control', 'placeholder' => __('Enter Property Zip Code'), 'required' => 'required']) }}
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="mb-3">
                                                    <div class="form-group ">
                                                        {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}
                                                        {{ Form::textarea('address', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter Property Address'), 'required' => 'required']) }}
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="profile-2" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row">
                                            <div class="preview-dropzon" style="display:none;">
                                                <div class="dz-preview dz-file-preview"
                                                    style="display:inline-block; width:150px; margin:10px; vertical-align:top; text-align:center;">

                                                    <div class="dz-image"
                                                        style="width:150px; height:130px; border-radius:10px; overflow:hidden;  background:#f0f0f0; display:flex; align-items:center; justify-content:center;  border:1px solid #e0e0e0; position:relative;">

                                                        <img data-dz-thumbnail alt=""
                                                            style="width:100%; height:100%; object-fit:cover; position:absolute; top:0; left:0;">

                                                        <div class="dz-file-icon"
                                                            style="display:flex; flex-direction:column; align-items:center; justify-content:center; width:100%; height:100%; position:absolute; top:0; left:0; background:#f8f9fa;">
                                                            <i class="fa fa-file-pdf-o fa-3x" style="color:#e74c3c;"></i>
                                                            <div data-dz-name
                                                                style="font-size:10px; color:#555; margin-top:6px; word-break:break-all; overflow:hidden; max-width:130px; max-height:40px; padding:0 4px;">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div style="margin-top:6px;">
                                                        <span data-dz-remove
                                                            style="font-size:12px; color:#e74c3c; cursor:pointer; font-weight:600;">
                                                            ✕ {{ __('Remove') }}
                                                        </span>
                                                    </div>

                                                    <div class="dz-error-message">
                                                        <span data-dz-errormessage
                                                            style="font-size:10px; color:red;"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="dropzone needsclick" id="demo-upload" action="#"
                                                style="min-height: 150px; border: 2px dashed #ccc; border-radius: 8px;">
                                                <div class="dz-message needsclick text-center py-4">
                                                    <i class="fa fa-cloud-upload fa-3x text-muted mb-2"></i>
                                                    <h5 class="text-muted">
                                                        {{ __('Drop files here or click to upload.') }}</h5>
                                                    <small
                                                        class="text-muted">{{ __('Allowed: JPG, JPEG, PNG, PDF') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="profile-3" role="tabpanel">
                                <div class="row align-items-center g-2">
                                    <div class="col">
                                    </div>
                                    <div class="col-auto">
                                        @include('ai.writer', ['type' => 'unit'])
                                    </div>
                                </div>
                                <div class="row unit_list">
                                    <div class="form-group col-md-4">
                                        {{ Form::label('unitname', __('Name'), ['class' => 'form-label']) }}
                                        {{ Form::text('unitname', null, ['class' => 'form-control', 'placeholder' => __('Enter unit name'), 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group col-md-2">
                                        {{ Form::label('bedroom', __('Bedroom'), ['class' => 'form-label']) }}
                                        {{ Form::number('bedroom', null, ['class' => 'form-control', 'placeholder' => __('Enter number of bedroom'), 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group col-md-2">
                                        {{ Form::label('kitchen', __('Kitchen'), ['class' => 'form-label']) }}
                                        {{ Form::number('kitchen', null, ['class' => 'form-control', 'placeholder' => __('Enter number of kitchen'), 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group col-md-2">
                                        {{ Form::label('baths', __('Bath'), ['class' => 'form-label']) }}
                                        {{ Form::number('baths', null, ['class' => 'form-control', 'placeholder' => __('Enter number of bath'), 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group col-md-2">
                                        {{ Form::label('rent', __('Rent'), ['class' => 'form-label']) }}
                                        {{ Form::number('rent', null, ['class' => 'form-control', 'placeholder' => __('Enter unit rent'), 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {{ Form::label('rent_type', __('Rent Type'), ['class' => 'form-label']) }}
                                        {{ Form::select('rent_type', $rentTypes, null, ['class' => 'form-control hidesearch', 'id' => 'rent_type', 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group col-md-6 rent_type monthly">
                                        {{ Form::label('rent_duration', __('Rent Duration'), ['class' => 'form-label']) }}
                                        {{ Form::number('rent_duration', null, ['class' => 'form-control', 'placeholder' => __('Enter day of month between 1 to 30')]) }}
                                    </div>
                                    <div class="form-group col-md-6 rent_type yearly d-none">
                                        {{ Form::label('rent_duration', __('Rent Duration'), ['class' => 'form-label']) }}
                                        {{ Form::number('rent_duration', null, ['class' => 'form-control', 'placeholder' => __('Enter month of year between 1 to 12'), 'disabled']) }}
                                    </div>
                                    <div class="form-group col-md-2 rent_type custom d-none">
                                        {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('start_date', null, ['class' => 'form-control', 'disabled']) }}
                                    </div>
                                    <div class="form-group col-md-2 rent_type custom d-none">
                                        {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('end_date', null, ['class' => 'form-control', 'disabled']) }}
                                    </div>
                                    <div class="form-group col-md-2 rent_type custom d-none">
                                        {{ Form::label('payment_due_date', __('Payment Due Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('payment_due_date', null, ['class' => 'form-control', 'disabled']) }}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {{ Form::label('deposit_type', __('Deposit Type'), ['class' => 'form-label']) }}
                                        {{ Form::select('deposit_type', $unitTypes, null, ['class' => 'form-control hidesearch', 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group col-md-3">
                                        {{ Form::label('deposit_amount', __('Deposit Amount'), ['class' => 'form-label']) }}
                                        {{ Form::number('deposit_amount', null, ['class' => 'form-control', 'placeholder' => __('Enter deposit amount'), 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group col-md-3">
                                        {{ Form::label('late_fee_type', __('Late Fee Type'), ['class' => 'form-label']) }}
                                        {{ Form::select('late_fee_type', $unitTypes, null, ['class' => 'form-control hidesearch', 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group col-md-2">
                                        {{ Form::label('late_fee_amount', __('Late Fee Amount'), ['class' => 'form-label']) }}
                                        {{ Form::number('late_fee_amount', null, ['class' => 'form-control', 'placeholder' => __('Enter late fee amount'), 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {{ Form::label('incident_receipt_amount', __('Incident Receipt Amount'), ['class' => 'form-label']) }}
                                        {{ Form::number('incident_receipt_amount', null, ['class' => 'form-control', 'placeholder' => __('Enter incident receipt amount'), 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
                                        {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter notes')]) }}
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="profile-4" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row">
                                            @foreach ($amenities as $amenity)
                                                <div class="col-md-6 col-xl-4 mb-4">
                                                    <label
                                                        class="border rounded p-3 d-flex align-items-start gap-3 shadow-sm h-100 cursor-pointer">
                                                        <input type="checkbox" name="amenities[]"
                                                            value="{{ $amenity->id }}" class="form-check-input mt-1">
                                                        <div>
                                                            <div class="d-flex align-items-center mb-2">
                                                                <img src="{{ fetch_file($amenity->image, 'upload/amenity/') }}"
                                                                    alt="Amenity Image" class="rounded me-2"
                                                                    width="40" height="40">
                                                                <strong
                                                                    class="text-dark">{{ ucfirst($amenity->name) }}</strong>
                                                            </div>
                                                            <div class="text-muted small">
                                                                {{ \Illuminate\Support\Str::limit($amenity->description, 120) }}
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="profile-5" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row">
                                            @foreach ($advantages as $advantage)
                                                <div class="col-md-6 col-xl-4 mb-4">
                                                    <label
                                                        class="border rounded p-3 d-flex align-items-start gap-3 shadow-sm h-100 cursor-pointer">
                                                        <input type="checkbox" name="advantages[]"
                                                            value="{{ $advantage->id }}" class="form-check-input mt-1">
                                                        <div>
                                                            <div class="d-flex align-items-center mb-2">

                                                                <strong
                                                                    class="text-dark">{{ ucfirst($advantage->name) }}</strong>
                                                            </div>
                                                            <div class="text-muted small">
                                                                {{ \Illuminate\Support\Str::limit($advantage->description, 120) }}
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="row">

                                            <!-- Display in listing checkbox -->
                                            <div class="col-md-12 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="display_in_listing" name="display_in_listing" value="1">
                                                    <label class="form-check-label" for="display_in_listing">
                                                        {{ __(' Property will display in listings?') }}
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Rent/Sell radio (hidden by default) -->
                                            <div class="col-md-12 mb-3" id="listing_type_container"
                                                style="display: none;">
                                                <label class="form-label d-block">{{ __('Listing Type') }}</label>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="listing_type"
                                                        id="type_rent" value="rent">
                                                    <label class="form-check-label"
                                                        for="type_rent">{{ __('Rent') }}</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="listing_type"
                                                        id="type_sell" value="sell">
                                                    <label class="form-check-label"
                                                        for="type_sell">{{ __('Sell') }}</label>
                                                </div>
                                            </div>

                                            <!-- Hidden input to hold final price -->
                                            <input type="hidden" name="price" id="final_price">

                                            <!-- Rent Price Input -->
                                            <div class="col-md-6 mb-3" id="rent_price_input" style="display: none;">
                                                <label for="rent_price" class="form-label">Monthly Rent
                                                    Price</label>
                                                <input type="number" class="form-control" id="rent_price"
                                                    placeholder="Enter monthly rent price">
                                            </div>

                                            <!-- Sell Price Input -->
                                            <div class="col-md-6 mb-3" id="sell_price_input" style="display: none;">
                                                <label for="sell_price" class="form-label">Sell Price</label>
                                                <input type="number" class="form-control" id="sell_price"
                                                    placeholder="Enter sell price">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="d-flex wizard justify-content-between flex-wrap gap-2 mt-3">
                                <div class="first">
                                    <a href="javascript:void(0);" class="btn btn-secondary">{{ __('First') }}</a>
                                </div>
                                <div class="d-flex">
                                    <div class="previous me-2">
                                        <a href="javascript:void(0);"
                                            class="btn btn-secondary">{{ __('Back To Previous') }}</a>
                                    </div>
                                    <div class="next">
                                        <a href="javascript:void(0);" class="btn btn-secondary">{{ __('Next') }}
                                            {{ __('Step') }}</a>
                                    </div>
                                </div>
                                <div class="last">
                                    <!-- Proper submit button – NO <a> wrapper -->
                                    <button type="submit" class="btn btn-secondary btn-rounded d-none"
                                        id="property-submit">
                                        {{ __('Create') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
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
    <script src="{{ asset('assets/js/vendors/dropzone/dropzone.js') }}"></script>
    <script src={{ asset('assets/js/plugins/wizard.min.js') }}></script>
    <script>
        new Wizard('#basicwizard', {
            validate: true,
            progress: true
        });
    </script>
    <script>
        var dropzone = new Dropzone('#demo-upload', {
            previewTemplate: document.querySelector('.preview-dropzon').innerHTML,
            parallelUploads: 10,
            thumbnailHeight: 120,
            thumbnailWidth: 120,
            maxFilesize: 10,
            filesizeBase: 1000,
            autoProcessQueue: false,
            addRemoveLinks: false,
            acceptedFiles: '.jpg,.jpeg,.png,.pdf',

            thumbnail: function(file, dataUrl) {
                if (!file.previewElement) return;

                var ext = file.name.split('.').pop().toLowerCase();
                var imgEl = file.previewElement.querySelector("[data-dz-thumbnail]");
                var fileIconEl = file.previewElement.querySelector(".dz-file-icon");

                if (['jpg', 'jpeg', 'png'].includes(ext)) {
                    if (imgEl) {
                        imgEl.src = dataUrl;
                        imgEl.style.display = 'block';
                    }
                    if (fileIconEl) fileIconEl.style.display = 'none';
                } else {
                    if (imgEl) imgEl.style.display = 'none';
                    if (fileIconEl) {
                        fileIconEl.style.display = 'flex';
                        var iconEl = fileIconEl.querySelector('i');
                        if (iconEl) {
                            iconEl.className = ext === 'pdf' ?
                                'fa fa-file-pdf-o fa-3x' :
                                'fa fa-file fa-3x';
                            iconEl.style.color = ext === 'pdf' ? '#e74c3c' : '#888';
                        }
                    }
                }

                file.previewElement.classList.remove("dz-file-preview");
                setTimeout(function() {
                    file.previewElement.classList.add("dz-image-preview");
                }, 1);
            }
        });
        $('#property-submit').on('click', function() {
            "use strict";
            $('#property-submit').attr('disabled', true);
            var fd = new FormData();
            var file = document.getElementById('thumbnail').files[0];

            var files = $('#demo-upload').get(0).dropzone.getAcceptedFiles();
            $.each(files, function(key, file) {
                fd.append('property_images[' + key + ']', $('#demo-upload')[0].dropzone
                    .getAcceptedFiles()[key]); // attach dropzone image element
            });
            fd.append('thumbnail', file);
            var other_data = $('#property_form').serializeArray();
            $.each(other_data, function(key, input) {
                fd.append(input.name, input.value);
            });
            fd.append('description', $('.ck-content').html());

            $.ajax({
                url: "{{ route('property.store') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: fd,
                contentType: false,
                processData: false,
                type: 'POST',
                success: function(data) {
                    if (data.status == "success") {
                        $('#property-submit').attr('disabled', true);
                        toastrs(data.status, data.msg, data.status);
                        let url = "{{ route('property.show', ':id') }}";
                        url = url.replace(':id', data.id);
                        setTimeout(() => {
                            window.location.href = url;
                        }, "1000");

                    } else {
                        toastrs('Error', data.msg, 'error');
                        $('#property-submit').attr('disabled', false);
                    }
                },
                error: function(data) {
                    $('#property-submit').attr('disabled', false);
                    if (data.error) {
                        toastrs('Error', data.error, 'error');
                    } else {
                        toastrs('Error', data, 'error');
                    }
                },
            });
        });
    </script>

    <script>
        function toggleRentFields() {
            "use strict";
            var occupancy = $('input[name="occupancy_type"]:checked').val();

            if (occupancy === 'tenant') {
                $('.rent-fields-wrapper').removeClass('d-none');
                $('.rent-fields-wrapper').find('input, select, textarea').prop('disabled', false);
                $('#rent_type').trigger('change');
            } else {
                $('.rent-fields-wrapper').addClass('d-none');
                $('.rent-fields-wrapper').find('input, select, textarea').prop('disabled', true);
            }
        }

        $('#rent_type').on('change', function() {
            "use strict";
            var type = this.value;
            $('.rent_type').addClass('d-none')
            $('.' + type).removeClass('d-none')

            var input1 = $('.rent_type').find('input');
            input1.prop('disabled', true);
            var input2 = $('.' + type).find('input');
            input2.prop('disabled', false);
        });

        $(document).ready(function() {
            toggleRentFields();
            $('input[name="occupancy_type"]').on('change', toggleRentFields);
        });
    </script>

    <script>
        $(document).ready(function() {
            function toggleRemoveServiceButton() {
                let serviceCount = $('.unit_list').length;
                $('.remove-service').toggle(serviceCount > 1);
            }

            $(document).on('click', '.add-unit', function() {
                let originalRow = $('.unit_list:first');


                let clonedRow = originalRow.clone();
                console.log(clonedRow);

                clonedRow.find('input, select').val('');
                clonedRow.find('.description').val('');

                let rowIndex = $('.unit_list').length;
                clonedRow.find('select[name^="skill"]').each(function() {
                    $(this).attr('name', 'skill[' + rowIndex + '][]');
                });

                let hrElement = $('<hr class="mt-2 mb-4 border-dark">');
                $('.unit_list_results').append(clonedRow).append(hrElement);

                originalRow.find('.select2').select2();
                clonedRow.find('.select2').select2();

                toggleRemoveServiceButton();

            });

            $(document).on('click', '.remove-service', function() {
                $(this).parent().parent().closest('.unit_list').next('hr').remove();
                $(this).parent().parent().closest('.unit_list').remove();
                toggleRemoveServiceButton();
            });

            toggleRemoveServiceButton();
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#display_in_listing').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#listing_type_container').slideDown();
                } else {
                    $('#listing_type_container').slideUp();
                    $('#rent_price_input, #sell_price_input').slideUp();
                    $('input[name="listing_type"]').prop('checked', false);
                    $('#final_price').val('');
                }
            });

            $('input[name="listing_type"]').on('change', function() {
                if ($(this).val() === 'rent') {
                    $('#rent_price_input').slideDown();
                    $('#sell_price_input').slideUp();
                    $('#final_price').val($('#rent_price').val());
                } else if ($(this).val() === 'sell') {
                    $('#sell_price_input').slideDown();
                    $('#rent_price_input').slideUp();
                    $('#final_price').val($('#sell_price').val());
                }
            });

            // Sync value to hidden field on input
            $('#rent_price').on('input', function() {
                if ($('#type_rent').is(':checked')) {
                    $('#final_price').val($(this).val());
                }
            });

            $('#sell_price').on('input', function() {
                if ($('#type_sell').is(':checked')) {
                    $('#final_price').val($(this).val());
                }
            });
        });
    </script>
@endpush
