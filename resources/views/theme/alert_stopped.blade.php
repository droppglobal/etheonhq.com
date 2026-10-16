@extends('theme.main')
@section('page-title', __('Property alert') . ' – ' . \App\Support\WebsiteSite::for($user)->siteName)
@php \App\Support\WebsiteSite::for($user)->setMeta(['noindex' => 1]); @endphp
@section('content')
    <section class="site-page-head">
        <div class="container">
            <div class="site-empty bg-white">
                <i class="fas fa-bell-slash"></i>
                <h1 class="h4">{{ $alert ? __('You will not get these emails any more') : __('This link is no longer valid') }}</h1>
                @if ($summary)
                    <p class="text-muted">{{ __('Search') }}: {{ $summary }}</p>
                @endif
                <a href="{{ route('property.home', ['code' => $user->code]) }}" class="ud-btn btn-thm2">{{ __('Browse properties') }}</a>
            </div>
        </div>
    </section>
@endsection
