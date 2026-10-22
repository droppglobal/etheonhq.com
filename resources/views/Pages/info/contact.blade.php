@php
    $siteName = !empty($settings['app_name']) ? $settings['app_name'] : env('APP_NAME');
    $contactName = !empty($settings['company_name']) ? $settings['company_name'] : $siteName;
    $contactEmail = !empty($settings['support_email']) ? $settings['support_email'] : (!empty($settings['company_email']) ? $settings['company_email'] : config('mail.from.address'));
    $contactPhone = $settings['company_phone'] ?? '';
    $contactAddress = $settings['company_address'] ?? '';
@endphp

<h3 class="mb-3">{{ __('Get in touch with :name', ['name' => $contactName]) }}</h3>
<p class="mb-4">{{ __('We would be glad to hear from you. Reach us using any of the details below.') }}</p>

<div class="row g-3 mb-4">
    @if (!empty($contactEmail))
        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <h6 class="mb-1"><i class="ti ti-mail me-2"></i>{{ __('Email') }}</h6>
                <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
            </div>
        </div>
    @endif
    @if (!empty($contactPhone))
        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <h6 class="mb-1"><i class="ti ti-phone me-2"></i>{{ __('Phone') }}</h6>
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}">{{ $contactPhone }}</a>
            </div>
        </div>
    @endif
    @if (!empty($contactAddress))
        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <h6 class="mb-1"><i class="ti ti-map-pin me-2"></i>{{ __('Address') }}</h6>
                <span>{{ $contactAddress }}</span>
            </div>
        </div>
    @endif
</div>

@if (empty($contactEmail) && empty($contactPhone) && empty($contactAddress))
    <p class="text-muted mb-4">
        {{ __('Contact details have not been added yet. Please check back soon.') }}
    </p>
@endif

<p class="mb-0">
    {{ __('Looking for help using the platform?') }}
    <a href="{{ route('info.support') }}">{{ __('Support and Documentation') }}</a>
</p>
