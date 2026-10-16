@extends('theme.main')

@php \App\Support\WebsiteSite::for($user)->setMeta(['noindex' => 1]); @endphp
@section('content')
    <section class="our-login contact-background">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto wow fadeInUp" data-wow-delay="300ms">
                    <div class="main-title text-center">
                        <h2 class="title">{{ __('Rental Application') }}</h2>
                        <p class="paragraph">{{ $property->name }} &middot; {{ $property->address }}, {{ $property->city }}</p>
                    </div>
                </div>
            </div>

            <div class="row wow fadeInRight" data-wow-delay="300ms">
                <div class="col-xl-8 mx-auto">
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                    @endif

                    @if ($units->isEmpty())
                        <div class="alert alert-warning">{{ __('There are no units available to apply for right now.') }}</div>
                    @else
                        {{ Form::open(['route' => ['web.apply.store', 'code' => $code], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                        {{ Form::hidden('property_id', $property->id) }}
                        <div style="position:absolute;left:-9999px" aria-hidden="true">
                            <input type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="log-reg-form search-modal form-style1 bgc-white p50 p30-sm default-box-shadow1 bdrs12">
                            <div class="row">
                                <div class="col-md-12 mb20">
                                    {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
                                    {{ Form::select('unit_id', $units, old('unit_id'), ['class' => 'form-control', 'required' => 'required']) }}
                                </div>
                                <div class="col-md-6 mb20">
                                    {{ Form::label('first_name', __('First Name'), ['class' => 'form-label']) }}
                                    {{ Form::text('first_name', old('first_name'), ['class' => 'form-control', 'required' => 'required']) }}
                                </div>
                                <div class="col-md-6 mb20">
                                    {{ Form::label('last_name', __('Last Name'), ['class' => 'form-label']) }}
                                    {{ Form::text('last_name', old('last_name'), ['class' => 'form-control']) }}
                                </div>
                                <div class="col-md-6 mb20">
                                    {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                                    {{ Form::email('email', old('email'), ['class' => 'form-control', 'required' => 'required']) }}
                                </div>
                                <div class="col-md-6 mb20">
                                    {{ Form::label('phone', __('Phone'), ['class' => 'form-label']) }}
                                    {{ Form::text('phone', old('phone'), ['class' => 'form-control', 'required' => 'required']) }}
                                </div>
                                <div class="col-md-12 mb20">
                                    {{ Form::label('current_address', __('Current Address'), ['class' => 'form-label']) }}
                                    {{ Form::textarea('current_address', old('current_address'), ['class' => 'form-control', 'rows' => 2, 'style' => 'height:auto']) }}
                                </div>
                                <div class="col-md-6 mb20">
                                    {{ Form::label('employer', __('Employer'), ['class' => 'form-label']) }}
                                    {{ Form::text('employer', old('employer'), ['class' => 'form-control']) }}
                                </div>
                                <div class="col-md-6 mb20">
                                    {{ Form::label('monthly_income', __('Monthly Income'), ['class' => 'form-label']) }}
                                    {{ Form::number('monthly_income', old('monthly_income'), ['class' => 'form-control', 'min' => 0, 'step' => '0.01']) }}
                                </div>
                                <div class="col-md-6 mb20">
                                    {{ Form::label('family_member', __('People Moving In'), ['class' => 'form-label']) }}
                                    {{ Form::number('family_member', old('family_member', 1), ['class' => 'form-control', 'min' => 1]) }}
                                </div>
                                <div class="col-md-6 mb20">
                                    {{ Form::label('move_in_date', __('Preferred Move-in Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('move_in_date', old('move_in_date'), ['class' => 'form-control', 'min' => date('Y-m-d')]) }}
                                </div>
                                <div class="col-md-6 mb20">
                                    {{ Form::label('id_document', __('ID Proof (required)'), ['class' => 'form-label']) }}
                                    {{ Form::file('id_document', ['class' => 'form-control', 'required' => 'required', 'accept' => '.jpg,.jpeg,.png,.pdf']) }}
                                </div>
                                <div class="col-md-6 mb20">
                                    {{ Form::label('income_document', __('Income Proof (optional)'), ['class' => 'form-label']) }}
                                    {{ Form::file('income_document', ['class' => 'form-control', 'accept' => '.jpg,.jpeg,.png,.pdf']) }}
                                </div>
                                <div class="col-md-12 mb20">
                                    {{ Form::label('message', __('Anything else we should know?'), ['class' => 'form-label']) }}
                                    {{ Form::textarea('message', old('message'), ['class' => 'form-control', 'rows' => 3, 'style' => 'height:auto']) }}
                                </div>
                                <div class="col-md-12 mb20">
                                    <label class="d-flex gap-2 align-items-start">
                                        <input type="checkbox" name="consent" value="1" required class="mt-1">
                                        <span>{{ __('I confirm the information is correct and agree that it may be used to review my application.') }}</span>
                                    </label>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="ud-btn btn-thm">{{ __('Submit Application') }}</button>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
