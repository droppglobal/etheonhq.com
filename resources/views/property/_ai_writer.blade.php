{{-- "Write with AI": builds the description from the details already in this form --}}
@php $aiOn = \App\Support\AiService::enabledFor(parentId()); @endphp
<div class="mt-2" id="ai-writer">
    <a class="btn btn-sm btn-light-secondary" data-bs-toggle="collapse" href="#ai-writer-panel" role="button">
        <i class="ti ti-wand"></i> {{ $aiOn ? __('Write with AI') : __('Write a draft for me') }}
    </a>
    <div class="collapse" id="ai-writer-panel">
        <div class="border rounded p-3 mt-2 bg-light">
            <div class="small text-muted mb-2">
                {{ $aiOn ? __('Uses the name, location, amenities, units and price you entered above. Only those facts are used, nothing is made up.')
                         : __('Builds a draft from the name, location, amenities, units and price you entered above. Edit it as you like.') }}
            </div>
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label small mb-0">{{ __('Tone') }}</label>
                    {{ Form::select('ai_tone', \App\Support\ListingWriter::tones(), 'professional', ['class' => 'form-control form-control-sm', 'id' => 'ai_tone']) }}
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-0">{{ __('Length') }}</label>
                    {{ Form::select('ai_length', \App\Support\ListingWriter::lengths(), 'medium', ['class' => 'form-control form-control-sm', 'id' => 'ai_length']) }}
                </div>
                @if ($aiOn)
                    <div class="col-md-2">
                        <label class="form-label small mb-0">{{ __('Language') }}</label>
                        <input type="text" id="ai_language" class="form-control form-control-sm" value="English" maxlength="30">
                    </div>
                @endif
                <div class="col-md-{{ $aiOn ? 4 : 6 }}">
                    <label class="form-label small mb-0">{{ __('Anything to highlight? (optional)') }}</label>
                    <input type="text" id="ai_highlight" class="form-control form-control-sm" maxlength="500" placeholder="{{ __('e.g. newly renovated, pet friendly') }}">
                </div>
            </div>
            <div class="mt-2 d-flex gap-2 align-items-center">
                <button type="button" class="btn btn-sm btn-secondary" id="ai-generate"><i class="ti ti-wand"></i> {{ __('Generate') }}</button>
                <span class="small text-danger" id="ai-error"></span>
            </div>
            <div id="ai-result" class="d-none mt-3">
                <div class="border rounded bg-white p-3 small" id="ai-preview"></div>
                <div class="mt-2 d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-secondary" id="ai-use">{{ __('Use this description') }}</button>
                    <button type="button" class="btn btn-sm btn-light-secondary" id="ai-again">{{ __('Try again') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
@push('script-page')
    <script>
        (function() {
            var $form = $('#ai-writer').closest('form');
            if (!$form.length) { $form = $(document); }
            function val(name) { return $form.find('[name="' + name + '"]').filter(function() { return this.type !== 'radio' || this.checked; }).val() || ''; }
            function checked(name) { return $form.find('[name="' + name + '"]:checked').map(function() { return this.value; }).get(); }
            function editor() { var el = document.querySelector('#ai-writer') && $('#ai-writer').closest('.form-group, .mb-3').find('.ck-editor__editable')[0]; return el ? el.ckeditorInstance : null; }
            function generate() {
                $('#ai-error').text(''); $('#ai-generate, #ai-again').prop('disabled', true);
                $('#ai-generate').html('<span class="spinner-border spinner-border-sm"></span> {{ __('Writing...') }}');
                var data = {
                    _token: '{{ csrf_token() }}', property_id: @json($propertyId),
                    name: val('name'), type: val('type'), listing_type: val('listing_type'), price: val('price'),
                    address: val('address'), city: val('city'), state: val('state'), country: val('country'),
                    amenities: checked('amenities[]'), advantages: checked('advantages[]'),
                    bedroom: val('bedroom'), baths: val('baths'), kitchen: val('kitchen'), rent: val('rent'), rent_type: val('rent_type'),
                    tone: $('#ai_tone').val(), length: $('#ai_length').val(), language: $('#ai_language').val() || 'English', highlight: $('#ai_highlight').val()
                };
                $.post('{{ route('property.ai.description') }}', data).done(function(res) {
                    $('#ai-preview').html(res.html);
                    $('#ai-result').removeClass('d-none');
                }).fail(function(xhr) {
                    $('#ai-error').text(xhr.status == 429 ? '{{ __('Please wait a minute before generating again.') }}' : ((xhr.responseJSON && xhr.responseJSON.error) || '{{ __('Something went wrong.') }}'));
                }).always(function() {
                    $('#ai-generate, #ai-again').prop('disabled', false);
                    $('#ai-generate').html('<i class="ti ti-wand"></i> {{ __('Generate') }}');
                });
            }
            $('#ai-generate, #ai-again').on('click', generate);
            $('#ai-use').on('click', function() {
                var ed = editor(), html = $('#ai-preview').html();
                if (ed) {
                    if ($.trim($(ed.getData()).text()) && !confirm('{{ __('Replace the current description?') }}')) return;
                    ed.setData(html);
                } else {
                    $form.find('[name="description"]').val(html);
                }
                $('#ai-writer-panel').collapse('hide');
            });
        })();
    </script>
@endpush
