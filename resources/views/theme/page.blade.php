@extends('theme.main')
@section('page-title', ($page->meta_title ?: $text['title']) . ' | ' . $site->siteName)
@php $site->setMeta(['description' => $page->meta_description ?: $text['content']]); @endphp
@section('content')
    <section class="site-page-hero">
        <div class="container">
            <h1 class="mb-0">{{ $text['title'] }}</h1>
        </div>
    </section>
    <section class="site-section pt-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <article class="site-page-content">{!! \App\Support\WebsiteSite::cleanHtml($text['content']) !!}</article>
                </div>
            </div>
        </div>
    </section>
@endsection
