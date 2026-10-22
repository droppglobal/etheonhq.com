@php
    $siteName = !empty($settings['app_name']) ? $settings['app_name'] : env('APP_NAME');
    $supportEmail = !empty($settings['support_email']) ? $settings['support_email'] : (!empty($settings['company_email']) ? $settings['company_email'] : config('mail.from.address'));
@endphp

<h3 class="mb-3">{{ __('Support') }}</h3>
<p class="mb-4">
    {{ __('Need a hand with :name? Start with the guide and answers below. If you still need help, our team is happy to assist.', ['name' => $siteName]) }}
</p>
<div class="d-flex flex-wrap gap-2 mb-5">
    <a href="{{ route('info.contact') }}" class="btn btn-secondary">{{ __('Contact us') }}</a>
    @if (!empty($supportEmail))
        <a href="mailto:{{ $supportEmail }}" class="btn btn-outline-secondary">{{ $supportEmail }}</a>
    @endif
</div>

<h3 class="mb-3">{{ __('Documentation') }}</h3>
<p class="mb-3">{{ __('A quick guide to getting started:') }}</p>
<ol class="mb-5">
    <li class="mb-2">
        <strong>{{ __('Create your account.') }}</strong>
        {{ __('Register, or log in if you already have an account.') }}
        <a href="{{ route('register') }}">{{ __('Get Started') }}</a> ·
        <a href="{{ route('login') }}">{{ __('Login') }}</a>
    </li>
    <li class="mb-2">
        <strong>{{ __('Add your properties and units.') }}</strong>
        {{ __('Set up each property and the units inside it so you can assign tenants.') }}
    </li>
    <li class="mb-2">
        <strong>{{ __('Add your tenants.') }}</strong>
        {{ __('Record tenant details and link each tenant to a unit, with agreements and documents.') }}
    </li>
    <li class="mb-2">
        <strong>{{ __('Create invoices and record payments.') }}</strong>
        {{ __('Bill rent and other charges, then mark payments as they come in to see what is still due.') }}
    </li>
    <li class="mb-2">
        <strong>{{ __('Handle maintenance and expenses.') }}</strong>
        {{ __('Log maintenance requests, track them to completion and record what you spend.') }}
    </li>
    <li class="mb-2">
        <strong>{{ __('Review your reports.') }}</strong>
        {{ __('Use the dashboard and reports to see how your rentals are performing.') }}
    </li>
</ol>

@if (!empty($FAQs) && count($FAQs) > 0)
    <h3 class="mb-3">{{ __('Frequently Asked Questions') }}</h3>
    <div class="accordion accordion-flush" id="supportFaqs">
        @foreach ($FAQs as $faq)
            <div class="accordion-item">
                <h2 class="accordion-header" id="support-faq-h{{ $faq->id }}">
                    <button class="accordion-button collapsed text-muted" type="button" data-bs-toggle="collapse"
                        data-bs-target="#support-faq-c{{ $faq->id }}" aria-expanded="false"
                        aria-controls="support-faq-c{{ $faq->id }}">
                        <b>{{ $faq->question }}</b>
                    </button>
                </h2>
                <div id="support-faq-c{{ $faq->id }}" class="accordion-collapse collapse"
                    aria-labelledby="support-faq-h{{ $faq->id }}" data-bs-parent="#supportFaqs">
                    <div class="accordion-body">{!! $faq->description !!}</div>
                </div>
            </div>
        @endforeach
    </div>
@endif
