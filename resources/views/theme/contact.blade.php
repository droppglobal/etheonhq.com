@extends('theme.main')
@section('page-title', __('Contact') . ' – ' . \App\Support\WebsiteSite::for($user)->siteName)

@section('content')
    @php
        $site = \App\Support\WebsiteSite::for($user);
        $c = $site->chrome;
        $wa = $site->whatsappNumber();
        $hasDetails = !empty($c['footer_address']) || !empty($c['footer_phone']) || !empty($c['footer_email']) || !empty($c['footer_hours']) || $wa;
    @endphp
    <section class="our-login contact-background site-contact-page">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 m-auto wow fadeInUp" data-wow-delay="300ms">
                    <div class="main-title text-center">
                        <h1 class="title h2">{{ __('Get in Touch') }}</h1>
                        <p class="paragraph">
                            {{ __('We’re here to help—reach out to us anytime with your questions or feedback.') }}</p>
                    </div>
                </div>
            </div>

            <div class="row g-4 justify-content-center wow fadeInUp" data-wow-delay="300ms">
                @if ($hasDetails)
                    <div class="col-lg-4">
                        <div class="site-card site-contact-card h-100 p-4">
                            <ul class="list-unstyled site-contact-list mb-0">
                                @if (!empty($c['footer_address']))<li><i class="fas fa-map-marker-alt"></i><span style="white-space: pre-line">{{ $c['footer_address'] }}</span></li>@endif
                                @if (!empty($c['footer_phone']))<li><i class="fas fa-phone-alt"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $c['footer_phone']) }}">{{ $c['footer_phone'] }}</a></li>@endif
                                @if (!empty($c['footer_email']))<li><i class="fas fa-envelope"></i><a href="mailto:{{ $c['footer_email'] }}">{{ $c['footer_email'] }}</a></li>@endif
                                @if (!empty($c['footer_hours']))<li><i class="fas fa-clock"></i><span style="white-space: pre-line">{{ $c['footer_hours'] }}</span></li>@endif
                            </ul>
                            @if ($wa)
                                <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="ud-btn site-wa-btn w-100 mt-3"><i class="fab fa-whatsapp me-2"></i>{{ __('Chat on WhatsApp') }}</a>
                            @endif
                        </div>
                    </div>
                @endif
                <div class="{{ $hasDetails ? 'col-lg-7 col-xl-6' : 'col-xl-6' }}">
                    {{ Form::open(['route' => ['contact-us', 'code' => $user->code], 'method' => 'post']) }}

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                    @endif

                    <div class="log-reg-form search-modal form-style1 bgc-white p40 p30-sm default-box-shadow1 bdrs12">
                        <div class="mb20">
                            <div class="form-group  col-md-12">
                                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                {{ Form::text('name', null, ['class' => 'form-control', 'autocomplete' => 'name', 'placeholder' => __('Enter contact name'), 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="mb20">
                            <div class="form-group  col-md-12">
                                {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                                {{ Form::email('email', null, ['class' => 'form-control', 'autocomplete' => 'email', 'placeholder' => __('Enter contact email'), 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="mb20">
                            <div class="form-group  col-md-12">
                                {{ Form::label('contact_number', __('Contact Number'), ['class' => 'form-label']) }}
                                {{ Form::tel('contact_number', null, ['class' => 'form-control', 'autocomplete' => 'tel', 'placeholder' => __('Enter contact number')]) }}
                            </div>
                        </div>
                        <div class="mb20">
                            <div class="form-group  col-md-12">
                                {{ Form::label('subject', __('Subject'), ['class' => 'form-label']) }}
                                {{ Form::text('subject', null, ['class' => 'form-control', 'placeholder' => __('Enter contact subject'), 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="mb15">
                            <div class="form-group col-md-12">
                                {{ Form::label('message', __('Message'), ['class' => 'form-label']) }}
                                {{ Form::textarea('message', null, [
                                    'class' => 'form-control',
                                    'rows' => 5,
                                    'required' => 'required',
                                    'placeholder' => __('Enter message'),
                                    'style' => 'height:auto; min-height:100px;',
                                ]) }}
                            </div>
                        </div>

                        <div class="d-grid">
                            {{ Form::submit(__('Send Messages'), ['class' => 'ud-btn btn-thm2']) }}
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </section>
@endsection
