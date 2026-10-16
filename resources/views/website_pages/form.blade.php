@extends('layouts.app')
@php
    $editing = $page->exists;
    $translating = $editing && $contentLang !== '';
    $t = $translating ? (json_decode((string) $page->translations, true)[$contentLang] ?? []) : [];
@endphp
@section('page-title')
    {{ $editing ? __('Edit Page') : __('Create Page') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('front-home.index') }}">{{ __('Website') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('website-pages.index') }}">{{ __('Pages') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ $editing ? $page->title : __('Create Page') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-10">
            <div class="card">
                <div class="card-body">
                    @if ($editing && count($languages) > 1)
                        <form method="GET" class="d-flex align-items-center gap-2 mb-3">
                            <label class="small text-muted mb-0"><i class="ti ti-language"></i> {{ __('Text language') }}</label>
                            <select name="content_lang" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                <option value="">{{ __('Default') }}</option>
                                @foreach ($languages as $code => $name)
                                    <option value="{{ $code }}" {{ $contentLang === $code ? 'selected' : '' }}>{{ \App\Support\WebsiteSite::languageName($code) }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                    <form method="POST" action="{{ $editing ? route('website-pages.update', $page->id) : route('website-pages.store') }}">
                        @csrf
                        @if ($editing) @method('PUT') @endif
                        <input type="hidden" name="content_lang" value="{{ $contentLang }}">
                        @if ($translating)
                            <div class="alert alert-info py-2 small">{{ __('You are writing the :language version of this page. Leave it empty to show the default text.', ['language' => \App\Support\WebsiteSite::languageName($contentLang)]) }}</div>
                        @else
                            @include('ai.writer', ['type' => 'custom_page'])
                        @endif
                        <div class="row">
                            <div class="col-md-8 form-group mb-3">
                                <label class="form-label">{{ __('Title') }}</label>
                                <input type="text" class="form-control" name="title" maxlength="120" value="{{ old('title', $translating ? ($t['title'] ?? '') : $page->title) }}" placeholder="{{ $translating ? $page->title : __('e.g. About Us') }}" {{ $translating ? '' : 'required' }}>
                            </div>
                            @unless ($translating)
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-label">{{ __('Address') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text small">/page/</span>
                                        <input type="text" class="form-control" name="slug" maxlength="120" value="{{ old('slug', $page->slug) }}" placeholder="{{ __('made from the title') }}">
                                    </div>
                                </div>
                            @endunless
                            <div class="col-12 form-group mb-3">
                                <label class="form-label">{{ __('Content') }}</label>
                                <textarea class="form-control classic-editor" name="content" rows="12">{{ old('content', $translating ? ($t['content'] ?? '') : $page->content) }}</textarea>
                            </div>
                            @unless ($translating)
                                <div class="col-md-6 form-group mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="show_in_menu" name="show_in_menu" value="1" {{ old('show_in_menu', $page->show_in_menu) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_in_menu">{{ __('Show in the website menu') }}</label>
                                    </div>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $page->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">{{ __('Published') }}</label>
                                    </div>
                                </div>
                                <div class="col-12"><h6 class="mt-2">{{ __('Search engines (optional)') }}</h6></div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-label">{{ __('Title in Google') }}</label>
                                    <input type="text" class="form-control" name="meta_title" maxlength="120" value="{{ old('meta_title', $page->meta_title) }}">
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-label">{{ __('Description in Google') }}</label>
                                    <input type="text" class="form-control" name="meta_description" maxlength="300" value="{{ old('meta_description', $page->meta_description) }}">
                                </div>
                            @endunless
                        </div>
                        <div class="text-end">
                            <a href="{{ route('website-pages.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-secondary"><i class="ti ti-device-floppy"></i> {{ __('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script-page')
    <script>
        $(function() { if (typeof ckediter === 'function') { ckediter('.classic-editor'); } });
    </script>
@endpush
