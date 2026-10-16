@if (!empty($c['items']))
    <section class="site-section" id="{{ $sectionId }}">
        <div class="container wow fadeInUp">
            @include('theme.sections._heading')
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="accordion site-faq" id="{{ $sectionId }}-acc">
                        @foreach ($c['items'] as $i => $item)
                            @continue(empty($item['question']))
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button {{ $i ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $sectionId }}-q{{ $i }}" aria-expanded="{{ $i ? 'false' : 'true' }}">{{ $item['question'] }}</button>
                                </h3>
                                <div id="{{ $sectionId }}-q{{ $i }}" class="accordion-collapse collapse {{ $i ? '' : 'show' }}" data-bs-parent="#{{ $sectionId }}-acc">
                                    <div class="accordion-body" style="white-space: pre-line">{{ $item['answer'] ?? '' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- answers can appear in Google results --}}
    <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => collect($c['items'])->filter(fn($i) => !empty($i['question']))->map(fn($i) => ['@type' => 'Question', 'name' => $i['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $i['answer'] ?? '']])->values()], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endif
