@php
    $siteName = !empty($settings['app_name']) ? $settings['app_name'] : env('APP_NAME');

    // reuse the footer "About" text the admin already edits on the landing page, if there is one
    $aboutSection = App\Models\HomePage::where('section', 'Section 10')->first();
    $aboutValues = !empty($aboutSection->content_value) ? json_decode($aboutSection->content_value, true) : [];
    $aboutTitle = !empty($aboutValues['Sec10_title']) ? $aboutValues['Sec10_title'] : __('About :name', ['name' => $siteName]);
    $aboutInfo = !empty($aboutValues['Sec10_info']) ? $aboutValues['Sec10_info'] : null;
@endphp

<h3 class="mb-3">{{ $aboutTitle }}</h3>

@if ($aboutInfo)
    <div class="mb-4">{!! $aboutInfo !!}</div>
@else
    <p class="mb-4">
        {{ __(':name helps property owners and managers run their rentals from one place, wherever they are.', ['name' => $siteName]) }}
    </p>
@endif

<h5 class="mb-3">{{ __('What you can do with :name', ['name' => $siteName]) }}</h5>
<div class="row g-3 mb-4">
    @foreach ([
        __('Manage properties') => __('Keep your properties and units organised in one place.'),
        __('Look after tenants') => __('Keep tenant details, agreements and documents together.'),
        __('Invoices and payments') => __('Create invoices, record payments and see what is still due.'),
        __('Maintenance') => __('Receive maintenance requests and follow them through to completion.'),
        __('Expenses and reports') => __('Record expenses and review the financial picture of your portfolio.'),
        __('Stay informed') => __('Share notices and keep everyone up to date.'),
    ] as $heading => $text)
        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <h6 class="mb-1">{{ $heading }}</h6>
                <p class="mb-0 text-muted">{{ $text }}</p>
            </div>
        </div>
    @endforeach
</div>

<p class="mb-0">
    {{ __('Have a question or want to know more?') }}
    <a href="{{ route('info.contact') }}">{{ __('Contact us') }}</a>
    {{ __('or visit') }}
    <a href="{{ route('info.support') }}">{{ __('Support and Documentation') }}</a>.
</p>
