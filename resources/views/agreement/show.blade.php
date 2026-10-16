@extends('layouts.app')
@section('page-title')
    {{ agreementPrefix() . $agreement->agreement_id }}
@endsection
@php
    $main_logo = getSettingsValByName('company_logo');
    $settings = settings();
    $user = !empty($agreement->tenants->user) ? $agreement->tenants->user : '-';

@endphp
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('agreement.index') }}">{{ __('Rental Agreement') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ agreementPrefix() . $agreement->agreement_id }}</li>
@endsection
@push('css-page')
    <style>
        #invoice-print {
            box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
        }

        u .doc-header {
            text-align: center;
            padding: 2rem 0 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1.5rem
        }

        .doc-header h1 {
            font-size: 22px;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            margin-bottom: 4px
        }

        .doc-header p {
            font-size: 13px;
        }

        .intro {
            font-size: 14px;
            margin-bottom: 1.5rem
        }

        .intro span {
            font-weight: 500;
            border-bottom: 1px solid #374151;
            padding: 0 6px
        }

        .agr-section {
            margin-bottom: 1.25rem
        }

        .agr-section-label {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }



        .box-bg {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 1rem 1.25rem
        }

        .field-row {
            display: flex;
            align-items: baseline;
            gap: 12px;
            padding: 6px 0;
            border-bottom: 1px solid #f3f4f6
        }

        .field-row:last-child {
            border-bottom: none;
            padding-bottom: 0
        }

        .field-label {
            font-size: 12px;
            min-width: 130px;
            flex-shrink: 0
        }

        .field-value {
            font-size: 14px;
            font-weight: 500
        }

        .terms-box {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            font-size: 13px;
            color: #4b5563;
            line-height: 1.7
        }

        .agr-sig-section {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb
        }

        .sig-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 1rem
        }

        .sig-block {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .5rem
        }

        .sig-img-wrap {
            width: 100%;
            max-width: 220px;
            height: 100px;
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9fafb
        }

        .sig-img-wrap img {
            max-height: 90px;
            max-width: 100%;
            object-fit: contain
        }

        .agr-sig-canvas-wrap {
            width: 100%;
            max-width: 220px;
            height: 100px;
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            position: relative;
            background: #fff;
            cursor: crosshair;
            overflow: hidden
        }

        #tenantSigCanvas {
            width: 100%;
            height: 100%;
            display: block
        }

        .agr-sig-ph {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #9ca3af;
            pointer-events: none
        }

        .agr-sig-line {
            width: 100%;
            max-width: 220px;
            height: 1px;
            background: #374151;
            margin-top: 4px
        }

        .agr-sig-name {
            font-size: 13px;
            font-weight: 600;
            color: #111;
            margin-top: 4px
        }

        .agr-sig-role {
            font-size: 11px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: .05em
        }

        .agr-sig-actions {
            display: flex;
            gap: 8px;
            width: 100%;
            max-width: 220px;
            margin-top: 8px
        }

        .agr-sig-actions button {
            flex: 1;
            padding: 6px 0;
            font-size: 12px;
            font-weight: 500;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #111;
            cursor: pointer;
            transition: background .15s
        }

        .agr-sig-actions button:hover {
            background: #f9fafb
        }

        .agr-sig-actions .btn-confirm {
            background: #111;
            color: #fff;
            border-color: #111
        }

        .agr-sig-actions .btn-confirm:hover {
            background: #1f2937
        }

        .agr-sig-actions button:disabled {
            opacity: .35;
            cursor: not-allowed
        }

        #tenantSigStatus {
            font-size: 11px;
            color: #6b7280;
            min-height: 16px;
            margin-top: 4px
        }

        @media(max-width:540px) {
            .sig-grid {
                grid-template-columns: 1fr
            }

            .field-row {
                flex-direction: column;
                gap: 2px
            }

            .field-label {
                min-width: unset
            }
        }

        @media print {

            .agr-sig-actions,
            #tenantSigStatus,
            .print {
                display: none !important;
                break-before: page;
            }

            .agr-sig-canvas-wrap {
                border: none
            }

            body {
                background: #fff !important;
                color: #000 !important;
            }

            #invoice-print {
                background: #fff !important;
                color: #000 !important;
                box-shadow: none !important;
            }

            * {
                color: #000 !important;
                background: transparent !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .card {
                background: #fff !important;
                border: none !important;
            }

            img {
                filter: none !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12" style="display:flex;justify-content:center">
            <div class="card" style="width:100%;max-width:860px">
                <div class="card-header">
                    <ul class="list-inline ms-auto mb-0 d-flex justify-content-end flex-wrap">
                        <li class="list-inline-item">
                            <a href="{{ route('agreement.pdf', \Crypt::encrypt($agreement->id)) }}" class="avtar avtar-s btn-link-secondary"
                                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Download PDF') }}">
                                <i class="ph-duotone ph-file-pdf f-22"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a href="javascript:void(0);" class="avtar avtar-s btn-link-secondary print"
                                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Print') }}">
                                <i class="ph-duotone ph-printer f-22"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <div id="invoice-print">
                    <div style="padding:2rem;">
                        <h2 class="text-center">{{ __('Rental Agreement') }}</h2>
                        <p style="border: none; border-top: 1px solid #000; margin: 10px 0;"></p>
                        <p class="intro">
                            {{ __('This agreement is made on') }}
                            <span>{{ dateFormat($agreement->date) }}</span>
                            {{ __('between the Landlord and Tenant under the terms mentioned below.') }}
                        </p>

                        <div class="agr-section">
                            <div class="agr-section-label"> {{ __('Landlord details') }}</div>
                            <div class="box-bg">
                                <div class="field-row"><span class="field-label">{{ __('Name') }}</span><span
                                        class="field-value">{{ $settings['company_name'] ?? '—' }}</span></div>
                                <div class="field-row"><span class="field-label">{{ __('Company') }}</span><span
                                        class="field-value">{{ $settings['company_name'] ?? '—' }}</span></div>
                                <div class="field-row"><span class="field-label">{{ __('Phone') }}</span><span
                                        class="field-value">{{ $settings['company_phone'] ?? '—' }}</span></div>
                                <div class="field-row"><span class="field-label">{{ __('Email') }}</span><span
                                        class="field-value">{{ $settings['company_email'] ?? '—' }}</span></div>
                            </div>
                        </div>

                        <div class="agr-section">
                            <div class="agr-section-label"> {{ __('Tenant details') }}</div>
                            <div class="box-bg">
                                <div class="field-row"><span class="field-label">{{ __('Name') }}</span><span
                                        class="field-value">{{ $user->full_name ?? '—' }}</span></div>
                                <div class="field-row"><span class="field-label">{{ __('Phone') }}</span><span
                                        class="field-value">{{ $user->phone_number ?? '—' }}</span></div>
                                <div class="field-row"><span class="field-label">{{ __('Email') }}</span><span
                                        class="field-value">{{ $user->email ?? '—' }}</span></div>
                            </div>
                        </div>

                        <div class="agr-section">
                            <div class="agr-section-label"> {{ __('Property details') }}</div>
                            <div class="box-bg">
                                <div class="field-row"><span class="field-label">{{ __('Property name') }}</span><span
                                        class="field-value">{{ $agreement->properties->name ?? '—' }}</span></div>
                                <div class="field-row"><span class="field-label">{{ __('Unit name') }}</span><span
                                        class="field-value">{{ $agreement->units->name ?? '—' }}</span></div>
                                <div class="field-row"><span class="field-label">{{ __('Address') }}</span><span
                                        class="field-value">{{ $agreement->properties->address ?? '—' }}</span></div>
                                <div class="field-row">
                                    <span class="field-label">{{ __('Lease') }}</span>
                                    <span class="field-value">
                                        @php
                                            $start = $agreement->agreement_start_date;
                                            $end = $agreement->agreement_end_date;
                                        @endphp

                                        @if ($start && $end)
                                            {{ dateFormat($start) . ' - ' . dateFormat($end) }}
                                        @elseif($start)
                                            {{ dateFormat($start) . ' - —' }}
                                        @elseif($end)
                                            {{ '— - ' . dateFormat($end) }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="agr-section">
                            <div class="agr-section-label"> {{ __('Rent details') }}</div>
                            <div class="box-bg">
                                <div class="field-row">
                                    <span class="field-label">{{ __('Rent') }}</span>
                                    <span class="field-value">
                                        {{ priceFormat($agreement->units->rent) }}
                                        <span style="font-weight:400;color:#6b7280;font-size:12px"> /
                                            {{ $agreement->units->rent_type }}</span>
                                    </span>
                                </div>
                                <div class="field-row"><span class="field-label">{{ __('Security deposit') }}</span><span
                                        class="field-value">{{ priceFormat($agreement->units->deposit_amount) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="agr-section">
                            <div class="agr-section-label"> {{ __('Terms & conditions') }}</div>
                            <div class="terms-box">{!! $agreement->terms_condition !!}</div>
                        </div>

                        <div class="agr-section">
                            <div class="agr-section-label"> {{ __('Description') }}</div>
                            <div class="terms-box">{!! $agreement->description !!}</div>
                        </div>

                        <div class="agr-sig-section">
                            <div class="agr-section-label"> {{ __('Signatures') }}</div>
                            <div class="sig-grid">

                                {{-- Landlord --}}
                                <div class="sig-block">
                                    <div class="sig-img-wrap">
                                        @if (!empty($agreement->landlord_signature))
                                            <img src="{{ $agreement->landlord_signature }}" alt="Landlord Signature">
                                        @elseif (!empty($settings['landlord_signature']))
                                            <img src="{{ $settings['landlord_signature'] }}" alt="Landlord Signature">
                                        @else
                                            <span
                                                style="font-size:12px;color:#9ca3af">{{ __('Landlord signature') }}</span>
                                        @endif
                                    </div>
                                    <div class="agr-sig-line"></div>
                                    <div class="agr-sig-name">{{ $settings['company_name'] ?? '—' }}</div>
                                    <div class="agr-sig-role">{{ __('Landlord / Authorised signatory') }}</div>
                                </div>

                                {{-- Tenant --}}
                                <div class="sig-block">
                                    @if (!empty($agreement->tenant_signature))
                                        <div class="sig-img-wrap">
                                            <img src="{{ $agreement->tenant_signature }}" alt="Tenant Signature">
                                        </div>
                                    @else
                                        <div class="agr-sig-canvas-wrap" id="tenantSigBox">
                                            <canvas id="tenantSigCanvas"></canvas>
                                            <span class="agr-sig-ph"
                                                id="tenantSigPlaceholder">{{ __('Draw signature here') }}</span>
                                        </div>
                                        <div class="agr-sig-actions">
                                            <button id="tenantClearBtn" disabled>Clear</button>
                                            <button id="tenantConfirmBtn" class="btn-confirm"
                                                disabled>{{ __('Save') }}</button>
                                        </div>
                                        <div id="tenantSigStatus"></div>
                                    @endif
                                    <div class="agr-sig-line"></div>
                                    <div class="agr-sig-name">{{ $user->full_name ?? 'Tenant' }}</div>
                                    <div class="agr-sig-role">{{ __('Tenant') }}</div>
                                    <div class="agr-sig-role">
                                        {{ !empty($agreement->tenant_signature_date) ? dateFormat($agreement->tenant_signature_date) : '-' }}
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
        $(document).on('click', '.print', function() {
            $('.action').addClass('d-none');
            var printContents = document.getElementById('invoice-print').innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;

            window.print();

            document.body.innerHTML = originalContents;
            $('.action').removeClass('d-none');
        });
    </script>

    <script>
        (function() {
            const canvas = document.getElementById('tenantSigCanvas');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const box = document.getElementById('tenantSigBox');
            const placeholder = document.getElementById('tenantSigPlaceholder');
            const statusDiv = document.getElementById('tenantSigStatus');
            const clearBtn = document.getElementById('tenantClearBtn');
            const confirmBtn = document.getElementById('tenantConfirmBtn');

            let drawing = false,
                locked = false,
                hasSig = false;

            function initCtx() {
                ctx.strokeStyle = '#111827';
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
            }

            function resizeCanvas() {
                const w = box.clientWidth;
                if (!w) return;
                canvas.width = w;
                canvas.height = 90;
                initCtx();
            }
            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);

            function getPos(e) {
                const r = canvas.getBoundingClientRect();
                const src = e.touches ? e.touches[0] : e;
                return {
                    x: src.clientX - r.left,
                    y: src.clientY - r.top
                };
            }

            function startDraw(e) {
                if (locked) return;
                e.preventDefault();
                drawing = true;
                const p = getPos(e);
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
                if (!hasSig) {
                    placeholder.style.display = 'none';
                    hasSig = true;
                    clearBtn.disabled = false;
                    confirmBtn.disabled = false;
                }
            }

            function draw(e) {
                if (!drawing || locked) return;
                e.preventDefault();
                const p = getPos(e);
                ctx.lineTo(p.x, p.y);
                ctx.stroke();
            }

            function stopDraw() {
                drawing = false;
            }

            canvas.addEventListener('mousedown', startDraw);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDraw);
            canvas.addEventListener('mouseleave', stopDraw);
            canvas.addEventListener('touchstart', startDraw, {
                passive: false
            });
            canvas.addEventListener('touchmove', draw, {
                passive: false
            });
            canvas.addEventListener('touchend', stopDraw);

            clearBtn.addEventListener('click', function() {
                if (locked) return;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                hasSig = false;
                placeholder.style.display = '';
                clearBtn.disabled = true;
                confirmBtn.disabled = true;
                statusDiv.innerHTML = '';
            });

            confirmBtn.addEventListener('click', function() {
                if (!hasSig || locked) return;
                locked = true;
                canvas.style.cursor = 'not-allowed';
                clearBtn.disabled = true;
                confirmBtn.disabled = true;
                statusDiv.innerHTML = '<span class="badge bg-warning">Saving…</span>';

                const base64 = canvas.toDataURL('image/png');

                $.ajax({
                    url: '{{ route('agreement.saveTenantSignature', $agreement->id) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        tenant_signature: base64
                    },
                    success: function(response) {
                        if (response.status === 'true') {
                            statusDiv.innerHTML =
                                '<span class="badge bg-success">&#10004; Signed &amp; Saved</span>';
                            clearBtn.style.display = 'none';
                            confirmBtn.style.display = 'none';
                            window.location.reload();
                        } else {
                            statusDiv.innerHTML =
                                '<span class="badge bg-danger">' + response.message + '</span>';
                            locked = false;
                            canvas.style.cursor = 'crosshair';
                            clearBtn.disabled = false;
                            confirmBtn.disabled = false;
                        }
                    },
                    error: function(xhr) {
                        const message = (xhr.responseJSON && xhr.responseJSON.message) ?
                            xhr.responseJSON.message :
                            'Save failed — try again';

                        statusDiv.innerHTML = '<span class="badge bg-danger">' + message +
                            '</span>';
                        locked = false;
                        canvas.style.cursor = 'crosshair';
                        clearBtn.disabled = false;
                        confirmBtn.disabled = false;
                    }
                });
            });
        })();
    </script>
@endpush
