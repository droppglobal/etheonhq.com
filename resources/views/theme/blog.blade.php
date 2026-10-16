@extends('theme.main')
@section('page-title', __('Blog') . ' – ' . \App\Support\WebsiteSite::for($user)->siteName)
@section('content')
    <section class="our-blog pt-0">
        <div class="container">
            <div class="row">

            </div>
        </div>
    </section>

    @php
        $Section_0 = App\Models\Additional::where('section', 'Section 0')->where('parent_id', $user->id)->first();
        $Section_0_content_value = !empty($Section_0->content_value)
            ? json_decode($Section_0->content_value, true)
            : [];
    @endphp
    @if (empty($Section_0_content_value['section_enabled']) || $Section_0_content_value['section_enabled'] == 'active')
        <section class="breadcumb-section pt-0">

            <div class="cta-service-v6 cta-banner mx-auto maxw1700 pt120 pt60-sm pb120 pb60-sm bdrs16 position-relative d-flex align-items-center"
                style="background-image: url('{{ asset(Storage::url($Section_0_content_value['banner_image1_path'])) }}'); background-position: bottom;">
                {{-- <img class="service-v3-vector d-none d-lg-block" src="images/about/about-4.png" alt=""> --}}
                <div class="container">
                    <div class="row wow fadeInUp">
                        <div class="col-xl-7">
                            <div class="position-relative">
                                <h2 class="text-dark">{{ $Section_0_content_value['title'] }}</h2>
                                <p class="text mb30 text-dark">{{ $Section_0_content_value['sub_title'] }}</p>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="our-blog pt40">
        <div class="container">
            <div class=" wow fadeInUp" data-wow-delay="300ms">
                <div class="col-xl-12">
                    <div class="row" id="blog-wrapper">
                        @include('theme.blogbox')
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('script-page')
    <script>
        $(document).on('click', '.mbp_pagination .page-link', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');

            $.ajax({
                url: url,
                type: 'GET',
                beforeSend: function() {
                    $('#blog-wrapper').html('<div class="text-center py-5">Loading...</div>');
                },
                success: function(data) {
                    $('#blog-wrapper').html(data);
                    window.history.pushState(null, null, url);
                },
                error: function() {
                    alert('Something went wrong.');
                }
            });
        });
    </script>
@endpush
