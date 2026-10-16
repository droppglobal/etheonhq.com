@php $embed = \App\Support\WebsiteSite::videoEmbed($c['video_url'] ?? ''); @endphp
@if ($embed)
    <section class="site-section" id="{{ $sectionId }}">
        <div class="container wow fadeInUp">
            @include('theme.sections._heading')
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="ratio ratio-16x9 site-video">
                        <iframe src="{{ $embed }}" title="{{ $c['title'] ?? 'Video' }}" loading="lazy" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
