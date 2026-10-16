@extends('layouts.app')
@section('page-title')
    {{ __('Tenant Edit') }}
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
    <script src="{{ asset('assets/js/vendors/dropzone/dropzone.js') }}"></script>

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

        var existingFiles = @json($tenant->documents ?? []);

        var removedExistingIds = [];

        existingFiles.forEach(function(image) {
            var ext = image.document.split('.').pop().toLowerCase();
            var imageExts = ['jpg', 'jpeg', 'png'];

            var mockFile = {
                name: image.document,
                size: 12345,
                id: image.id,
                existing: true,
                accepted: true,
            };

            dropzone.emit("addedfile", mockFile);

            if (imageExts.includes(ext)) {
                var fileUrl = '{{ Storage::url('upload/tenant/') }}' + image.document;
                dropzone.emit("thumbnail", mockFile, fileUrl);
            } else {
                dropzone.emit("thumbnail", mockFile, '');
            }

            dropzone.emit("complete", mockFile);

            if (mockFile.previewElement) {
                mockFile.previewElement.classList.add("dz-image-preview");
                mockFile.previewElement.classList.remove("dz-processing", "dz-error");
                mockFile.previewElement.setAttribute("data-existing-id", image.id);

                var nameEl = mockFile.previewElement.querySelector("[data-dz-name]");
                if (nameEl) nameEl.textContent = image.document;
            }
        });
        dropzone.on("removedfile", function(file) {
            if (file.existing && file.id) {
                removedExistingIds.push(file.id);
            }
        });

        function switchTab(target) {
            var tab = document.querySelector('[href="' + target + '"]');
            if (tab) {
                var bsTab = new bootstrap.Tab(tab);
                bsTab.show();
            }
        }

        $('#tenant-submit').on('click', function() {
            "use strict";
            $('#tenant-submit').attr('disabled', true);
            var fd = new FormData();

            var file = document.getElementById('profile').files[0];
            fd.append('profile', file ?? '');

            var newFiles = dropzone.getAcceptedFiles().filter(function(f) {
                return !f.existing;
            });
            $.each(newFiles, function(key, file) {
                fd.append('tenant_images[' + key + ']', file);
            });

            fd.append('dropzone_loaded', 1);

            var keptIds = existingFiles
                .map(function(img) {
                    return img.id;
                })
                .filter(function(id) {
                    return !removedExistingIds.includes(id);
                });

            $.each(keptIds, function(key, id) {
                fd.append('existing_images[' + key + ']', id);
            });

            var other_data = $('#tenant_form').serializeArray();
            $.each(other_data, function(key, input) {
                fd.append(input.name, input.value);
            });

            $.ajax({
                url: "{{ route('tenant.update', $tenant->id) }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: fd,
                contentType: false,
                processData: false,
                type: 'POST',
                success: function(data) {
                    if (data.status == "success" || data.flag == 1) {
                        toastrs('Success', data.msg, 'success');
                        var url = '{{ route('tenant.index') }}';
                        setTimeout(() => {
                            window.location.href = url;
                        }, 1000);

                    } else {
                        toastrs(
                            'Error',
                            data.msg ? data.msg : 'Something went wrong',
                            'error'
                        );
                        $('#tenant-submit').attr('disabled', false);
                    }
                },
                error: function(data) {
                    $('#tenant-submit').attr('disabled', false);
                    toastrs('Error', data.error ? data.error : data, 'error');
                },
            });
        });

        // ===== Property Change =====
        $('#property').on('change', function() {
            let property_id = $(this).val();
            let selectedUnitId = $('#edit_unit').val();
            let url = '{{ route('tenant.unit', ':id') }}'.replace(':id', property_id);

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    property_id: property_id
                },
                success: function(data) {
                    $('#unit-detail-section').addClass('d-none');
                    $('#unit-fields').html('');
                    $('#unit_name').text('');

                    let $unit = $('#unit');
                    if ($unit.hasClass('select2-hidden-accessible')) {
                        $unit.select2('destroy');
                    }

                    $unit.empty().append('<option value="">{{ __('Select Unit') }}</option>');

                    $.each(data, function(key, value) {
                        let statusDot = value.is_occupied == 1 ? '🔴' : '🟢';
                        let selected = key == selectedUnitId ? 'selected' : '';
                        $unit.append('<option value="' + key + '" ' + selected + '>' + value
                            .name + ' ' + statusDot + '</option>');
                    });

                    $unit.select2({
                        width: '100%'
                    });
                    $unit.trigger('change');
                }
            });
        });

        $('#property').trigger('change');

        // ===== Unit Change → Load Unit Details =====
        function renderUnitField(label, value, id) {
            if (!value || value === 'null') return '';
            return `
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="border rounded p-2">
                        <small class="text-muted">${label}</small>
                        <p class="mb-0 fw-bold" id="${id}">${value}</p>
                    </div>
                </div>`;
        }

        function renderRentFields(data) {
            if (data.unit_rent_type === 'custom') {
                return `
            ${renderUnitField(`{{ __('Start Date') }}`, data.start_date ?? '-', 'start_date')}
            ${renderUnitField(`{{ __('End Date') }}`, data.end_date ?? '-', 'end_date')}
            ${renderUnitField(`{{ __('Payment Due Date') }}`, data.payment_due_date ?? '-', 'payment_due_date')}
        `;
            } else {
                return `
            ${renderUnitField(`{{ __('Rent Duration') }}`, data.rent_duration ?? '-', 'rent_duration')}
        `;
            }
        }

        $(document).on('change', '#unit', function() {
            var unit_id = $(this).val();
            if (unit_id) {
                var url = '{{ route('tenant.unit.details', ':id') }}';
                url = url.replace(':id', unit_id);
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(data) {
                        let html = '';

                        $('#unit_name').text(data.name);

                        html += renderUnitField(`{{ __('Status') }}`,
                            data.is_occupied == 1 ?
                            '<span class="text-danger">Occupied 🔴</span>' :
                            '<span class="text-success">Vacant 🟢</span>',
                            'unit_status');
                        html += renderUnitField(`{{ __('Bedroom') }}`, data.bedroom, 'unit_bedroom');
                        html += renderUnitField(`{{ __('Kitchen') }}`, data.kitchen, 'unit_kitchen');
                        html += renderUnitField(`{{ __('Bath') }}`, data.baths, 'unit_baths');
                        html += renderUnitField(`{{ __('Rent Type') }}`, data.rent_type,
                            'unit_rent_type');
                        html += renderUnitField(`{{ __('Rent') }}`, data.rent_formatted,
                            'unit_rent');

                        html += renderRentFields(data);

                        html += renderUnitField(`{{ __('Deposit Type') }}`, data.deposit_type,
                            'unit_deposit_type');
                        html += renderUnitField(`{{ __('Deposit Amount') }}`, data
                            .deposit_amount_formatted, 'unit_deposit_amount');
                        html += renderUnitField(`{{ __('Late Fee Type') }}`, data.late_fee_type,
                            'unit_late_fee_type');
                        html += renderUnitField(`{{ __('Late Fee Amount') }}`, data
                            .late_fee_amount_formatted, 'unit_late_fee_amount');
                        html += renderUnitField(`{{ __('Incident Receipt') }}`, data
                            .incident_receipt_amount_formatted, 'unit_incident_receipt_amount');
                        html += renderUnitField(`{{ __('Notes') }}`, data.notes ?? 'N/A',
                            'unit_notes');

                        $('#unit-fields').html(html);
                        $('#unit-detail-section').removeClass('d-none');
                    },
                    error: function() {
                        $('#unit-detail-section').addClass('d-none');
                    }
                });
            } else {
                $('#unit-detail-section').addClass('d-none');
            }

        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tenant.index') }}">{{ __('Tenant') }}</a></li>
    <li class="breadcrumb-item active"><a href="#">{{ __('Edit') }}</a></li>
@endsection

@section('content')
    <div class="row">
        {{ Form::model($tenant, ['route' => ['tenant.update', $tenant->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'id' => 'tenant_form']) }}

        <div id="basicwizard" class="form-wizard row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-3">
                        <ul class="nav nav-pills nav-justified">

                            <li class="nav-item">
                                <a class="nav-link active" id="tab-1" data-bs-toggle="tab" href="#profile-1"
                                    role="tab">
                                    <i data-feather="user" style="width:18px;height:18px;" class="me-1"></i>
                                    {{ __('Personal') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-2" data-bs-toggle="tab" href="#profile-2" role="tab">
                                    <i data-feather="map-pin" style="width:18px;height:18px;" class="me-1"></i>
                                    {{ __('Address') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-3" data-bs-toggle="tab" href="#profile-3" role="tab">
                                    <i data-feather="home" style="width:18px;height:18px;" class="me-1"></i>
                                    {{ __('Property & Unit') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-4" data-bs-toggle="tab" href="#profile-4" role="tab">
                                    <i data-feather="upload" style="width:18px;height:18px;" class="me-1"></i>
                                    {{ __('Documents') }}
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

                            <div class="tab-pane active" id="profile-1" role="tabpanel">

                                <div class="row">
                                    <div class="form-group col-lg-6 col-md-6">
                                        {{ Form::label('first_name', __('First Name'), ['class' => 'form-label']) }}
                                        {{ Form::text('first_name', $user->first_name ?? null, ['class' => 'form-control', 'placeholder' => __('Enter First Name'),'required'=>'required']) }}
                                    </div>
                                    <div class="form-group col-lg-6 col-md-6">
                                        {{ Form::label('last_name', __('Last Name'), ['class' => 'form-label']) }}
                                        {{ Form::text('last_name', $user->last_name ?? null, ['class' => 'form-control', 'placeholder' => __('Enter Last Name'),'required'=>'required']) }}
                                    </div>
                                    <div class="form-group col-lg-6 col-md-6">
                                        {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                                        {{ Form::email('email', $user->email ?? null, ['class' => 'form-control', 'placeholder' => __('Enter Email'),'required'=>'required']) }}
                                    </div>
                                    <div class="form-group col-lg-6 col-md-6">
                                        {{ Form::label('phone_number', __('Phone Number'), ['class' => 'form-label']) }}
                                        {{ Form::text('phone_number', $user->phone_number ?? null, ['class' => 'form-control', 'placeholder' => __('Enter Phone Number'),'required'=>'required']) }}
                                        <small class="form-text text-muted">
                                            {{ __('Please enter the number with country code. e.g., +91XXXXXXXXXX') }}
                                        </small>
                                    </div>
                                    <div class="form-group col-lg-6 col-md-6">
                                        {{ Form::label('family_member', __('Total Family Member'), ['class' => 'form-label']) }}
                                        {{ Form::number('family_member', null, ['class' => 'form-control', 'placeholder' => __('Enter Total Family Member'),'required'=>'required']) }}
                                    </div>
                                    <div class="form-group col-lg-6 col-md-6">
                                        {{ Form::label('profile', __('Profile Photo'), ['class' => 'form-label']) }}
                                        {{ Form::file('profile', ['class' => 'form-control', 'id' => 'profile']) }}
                                    </div>
                                </div>

                            </div>

                            <div class="tab-pane" id="profile-2" role="tabpanel">
                                <div class="row">
                                    <div class="form-group col-lg-6 col-md-6">
                                        {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}
                                        {{ Form::text('country', null, ['class' => 'form-control', 'placeholder' => __('Enter Country'),'required'=>'required']) }}
                                    </div>
                                    <div class="form-group col-lg-6 col-md-6">
                                        {{ Form::label('state', __('State'), ['class' => 'form-label']) }}
                                        {{ Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('Enter State'),'required'=>'required']) }}
                                    </div>
                                    <div class="form-group col-lg-6 col-md-6">
                                        {{ Form::label('city', __('City'), ['class' => 'form-label']) }}
                                        {{ Form::text('city', null, ['class' => 'form-control', 'placeholder' => __('Enter City'),'required'=>'required']) }}
                                    </div>
                                    <div class="form-group col-lg-6 col-md-6">
                                        {{ Form::label('zip_code', __('Zip Code'), ['class' => 'form-label']) }}
                                        {{ Form::text('zip_code', null, ['class' => 'form-control', 'placeholder' => __('Enter Zip Code'),'required'=>'required']) }}
                                    </div>
                                    <div class="form-group col-lg-12">
                                        {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}
                                        {{ Form::textarea('address', null, ['class' => 'form-control', 'rows' => 4, 'placeholder' => __('Enter Address'),'required'=>'required']) }}
                                    </div>
                                </div>

                            </div>

                            <div class="tab-pane" id="profile-3" role="tabpanel">

                                <div class="row">
                                    <div class="form-group col-lg-6 col-md-6">
                                        {{ Form::label('property', __('Property'), ['class' => 'form-label']) }}
                                        {{ Form::select('property', $property, null, ['class' => 'form-control select2', 'id' => 'property']) }}
                                    </div>
                                    <div class="form-group col-lg-6 col-md-6">
                                        {{ Form::label('unit', __('Unit'), ['class' => 'form-label']) }}
                                        <input type="hidden" id="edit_unit" value="{{ $tenant->unit }}">
                                        <div class="unit_div">
                                            <select class="form-control select2 unit" id="unit" name="unit">
                                                <option value="">{{ __('Select Unit') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-lg-6 col-md-6">
                                        {{ Form::label('lease_start_date', __('Lease Start Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('lease_start_date', null, ['class' => 'form-control']) }}
                                    </div>
                                    <div class="form-group col-lg-6 col-md-6">
                                        {{ Form::label('lease_end_date', __('Lease End Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('lease_end_date', null, ['class' => 'form-control']) }}
                                    </div>
                                </div>


                                <div id="unit-detail-section" class="card border mt-3 d-none">
                                    <div class="card-header">
                                        <h5><i data-feather="layers" style="width:16px;height:16px;" class="me-1"></i>
                                            <span id="unit_name"></span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="unit-fields" class="row"></div>
                                    </div>
                                </div>


                            </div>

                            <div class="tab-pane" id="profile-4" role="tabpanel">
                                <div class="preview-dropzon" style="display:none;">
                                    <div class="dz-preview dz-file-preview"
                                        style="display:inline-block; width:150px; margin:10px; vertical-align:top; text-align:center;">

                                        <div class="dz-image"
                                            style="width:150px; height:130px; border-radius:10px; overflow:hidden; background:#f0f0f0; display:flex; align-items:center; justify-content:center; border:1px solid #e0e0e0; position:relative;">

                                            {{-- Shown for image files --}}
                                            <img data-dz-thumbnail alt=""
                                                style="width:100%; height:100%; object-fit:cover; position:absolute; top:0; left:0;">

                                            {{-- Shown for non-image files (PDF etc) --}}
                                            <div class="dz-file-icon"
                                                style="display:flex; flex-direction:column; align-items:center;  justify-content:center; width:100%; height:100%; position:absolute;  top:0; left:0; background:#f8f9fa;">
                                                <i class="fa fa-file-pdf-o fa-3x" style="color:#e74c3c;"></i>
                                                <div data-dz-name
                                                    style="font-size:10px; color:#555; margin-top:6px; word-break:break-all; overflow:hidden; max-width:130px; max-height:40px; padding:0 4px;">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="margin-top:6px;">
                                            <span data-dz-remove
                                                style="font-size:12px; color:#e74c3c; cursor:pointer; font-weight:600;">
                                                ✕ Remove
                                            </span>
                                        </div>

                                        <div class="dz-error-message">
                                            <span data-dz-errormessage style="font-size:10px; color:red;"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropzone needsclick" id="demo-upload" action="#"
                                    style="min-height: 220px; border: 2px dashed #ccc; border-radius: 8px;">
                                    <div class="dz-message needsclick text-center py-4">
                                        <i class="fa fa-cloud-upload fa-3x text-muted mb-2"></i>
                                        <h5 class="text-muted">{{ __('Drop files here or click to upload.') }}</h5>
                                        <small class="text-muted">{{ __('Allowed: JPG, JPEG, PNG, PDF') }}</small>
                                    </div>
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
                                    <a href="#" class="">
                                        {{ Form::submit(__('Finish'), ['class' => 'btn btn-secondary ','id' => 'tenant-submit']) }}
                                    </a>
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
