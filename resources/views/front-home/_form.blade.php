{{--
    Editor for one website section: its fields and, when it has them, a list of repeatable items.
    $section (row), $def (definition), $c (content), $contentLang ('' = default text), $tr (translation for $contentLang)
--}}
@php
    $isChrome = $section->section === \App\Support\WebsiteSections::CHROME;
    $translating = $contentLang !== '';
    $items = $c['items'] ?? [];
    $itemDef = $def['items'] ?? null;
    $aiType = 'website_' . str_replace(' ', '_', strtolower($section->section));
    $groups = collect($def['fields'])->groupBy(fn($f) => $f['group'] ?? '');
@endphp
<form method="POST" action="{{ route('front-home.update', $section->id) }}" enctype="multipart/form-data" class="fh-form">
    @csrf
    @method('PUT')
    <input type="hidden" name="content_lang" value="{{ $contentLang }}">

    @if ($translating)
        <div class="alert alert-info py-2 small">
            <i class="ti ti-language"></i>
            {{ __('You are writing the :language text of this section. Leave a field empty to show the default text. Images, links and settings are shared by all languages.', ['language' => $languages[$contentLang] ?? $contentLang]) }}
        </div>
    @else
        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label class="form-label">{{ __('Name in this list') }}</label>
                <input type="text" class="form-control" name="content_value[name]" value="{{ $c['name'] ?? $def['title'] }}" maxlength="60">
            </div>
            @unless ($isChrome)
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label d-block">{{ __('Show on the website') }}</label>
                    <div class="form-check form-switch">
                        <input type="hidden" name="content_value[section_enabled]" value="deactive">
                        <input class="form-check-input fh-enabled-input" type="checkbox" role="switch" name="content_value[section_enabled]" value="active" {{ \App\Support\WebsiteSections::enabled($c) ? 'checked' : '' }}>
                    </div>
                </div>
            @endunless
        </div>
        @if (!empty($def['help']))
            <p class="text-muted small"><i class="ti ti-info-circle"></i> {{ __($def['help']) }}</p>
        @endif
        @if (\Gate::check('edit front home page'))
            @include('ai.writer', ['type' => $aiType])
        @endif
    @endif

    @foreach ($groups as $group => $fields)
        @if ($group !== '')
            <h6 class="mt-3 mb-2 text-muted text-uppercase small">{{ __($group) }}</h6>
        @endif
        <div class="row">
            @foreach ($fields as $field)
                @if ($translating)
                    @if (in_array($field['type'], ['text', 'textarea']))
                        @include('front-home._field', ['field' => $field, 'name' => 'content_value[' . $field['key'] . ']',
                            'value' => $tr[$field['key']] ?? '', 'placeholder' => $c[$field['key']] ?? ''])
                    @endif
                @else
                    @include('front-home._field', ['field' => $field, 'name' => 'content_value[' . $field['key'] . ']',
                        'value' => $c[$field['key']] ?? null, 'path' => $c[$field['key'] . '_path'] ?? '',
                        'removeName' => 'content_value[' . $field['key'] . '_remove]'])
                @endif
            @endforeach
        </div>
    @endforeach

    @if ($itemDef)
        <h6 class="mt-3 mb-2">{{ __($itemDef['plural'] ?? $itemDef['label']) }} <span class="badge bg-light-secondary fh-count">{{ count($items) }}</span></h6>
        @if ($translating)
            @forelse ($items as $i => $item)
                <div class="border rounded p-3 mb-2 bg-light">
                    <div class="small text-muted mb-2">{{ __($itemDef['label']) }} {{ $i + 1 }}</div>
                    <div class="row">
                        @foreach ($itemDef['fields'] as $field)
                            @if (in_array($field['type'], ['text', 'textarea']))
                                @include('front-home._field', ['field' => $field, 'name' => 'content_value[items][' . ($item['id'] ?? $i) . '][' . $field['key'] . ']',
                                    'value' => $tr['items'][$item['id'] ?? $i][$field['key']] ?? '', 'placeholder' => $item[$field['key']] ?? ''])
                            @endif
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-muted small">{{ __('Add items in the default language first.') }}</p>
            @endforelse
        @else
            <div class="fh-items" data-label="{{ __($itemDef['label']) }}">
                @foreach ($items as $i => $item)
                    @include('front-home._item', ['itemDef' => $itemDef, 'index' => $i, 'item' => $item])
                @endforeach
            </div>
            <template class="fh-item-template">
                @include('front-home._item', ['itemDef' => $itemDef, 'index' => '__INDEX__', 'item' => []])
            </template>
            <button type="button" class="btn btn-sm btn-light-secondary fh-add-item"><i class="ti ti-plus"></i> {{ __('Add') }} {{ __($itemDef['label']) }}</button>
            <small class="text-muted ms-2">{{ __('Drag the handle to change the order.') }}</small>
        @endif
    @endif

    @if (\Gate::check('edit front home page'))
        <div class="text-end mt-4">
            <button type="submit" class="btn btn-secondary"><i class="ti ti-device-floppy"></i> {{ __('Save') }}</button>
        </div>
    @endif
</form>
