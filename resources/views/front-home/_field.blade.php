{{--
    One field of a website section. $field (definition), $name (input name), $value, $properties (for type "properties").
    Images: $pathName (hidden input keeping the earlier upload, for list items) or $removeName (checkbox, for single images).
--}}
@php
    $label = __($field['label']);
    $id = 'f_' . substr(md5($name), 0, 10);
@endphp
<div class="form-group mb-3 {{ $field['type'] === 'textarea' || $field['type'] === 'properties' ? 'col-12' : 'col-md-6' }}">
    @if ($field['type'] === 'toggle')
        <div class="form-check form-switch mt-2">
            <input type="hidden" name="{{ $name }}" value="deactive">
            <input class="form-check-input" type="checkbox" role="switch" id="{{ $id }}" name="{{ $name }}" value="active" {{ ($value ?? 'active') === 'active' ? 'checked' : '' }}>
            <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
        </div>
    @else
        <label class="form-label" for="{{ $id }}">{{ $label }}</label>
        @switch($field['type'])
            @case('textarea')
                <textarea class="form-control" id="{{ $id }}" name="{{ $name }}" rows="3" placeholder="{{ $placeholder ?? '' }}">{{ $value }}</textarea>
                @break
            @case('number')
                <input type="number" min="0" max="1000" class="form-control" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}">
                @break
            @case('select')
                <select class="form-select" id="{{ $id }}" name="{{ $name }}">
                    @foreach ($field['options'] as $optValue => $optLabel)
                        <option value="{{ $optValue }}" {{ (string) $value === (string) $optValue ? 'selected' : '' }}>{{ __($optLabel) }}</option>
                    @endforeach
                </select>
                @break
            @case('page')
                <select class="form-select" id="{{ $id }}" name="{{ $name }}">
                    <option value="0">{{ __('None') }}</option>
                    @foreach (\App\Models\WebsitePage::where('parent_id', parentId())->orderBy('title')->pluck('title', 'id') as $pageId => $pageTitle)
                        <option value="{{ $pageId }}" {{ (int) $value === (int) $pageId ? 'selected' : '' }}>{{ $pageTitle }}</option>
                    @endforeach
                </select>
                <small class="text-muted">{{ __('Create the page under Frontend Manager > Pages.') }}</small>
                @break
            @case('properties')
                <select class="form-select" id="{{ $id }}" name="{{ $name }}[]" multiple size="6">
                    @foreach ($properties as $propId => $propName)
                        <option value="{{ $propId }}" {{ in_array($propId, (array) $value) ? 'selected' : '' }}>{{ $propName }}</option>
                    @endforeach
                </select>
                <small class="text-muted">{{ __('Hold Ctrl (Cmd on Mac) to pick several.') }}</small>
                @break
            @case('image')
                <div class="d-flex align-items-center gap-2 fh-image">
                    <img src="{{ !empty($path) ? asset(Storage::url($path)) : '' }}" class="rounded border fh-thumb {{ empty($path) ? 'd-none' : '' }}" alt="">
                    <input type="file" accept="image/*" class="form-control" id="{{ $id }}" name="{{ $name }}">
                    @isset($pathName)
                        <input type="hidden" class="fh-path" name="{{ $pathName }}" value="{{ $path ?? '' }}">
                        <button type="button" class="btn btn-sm btn-light-danger fh-clear-image {{ empty($path) ? 'd-none' : '' }}" title="{{ __('Remove') }}"><i class="ti ti-x"></i></button>
                    @endisset
                </div>
                @if (isset($removeName) && !empty($path))
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" name="{{ $removeName }}" value="1" id="{{ $id }}_rm">
                        <label class="form-check-label small" for="{{ $id }}_rm">{{ __('Remove this image') }}</label>
                    </div>
                @endif
                @break
            @default
                <input type="text" class="form-control" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder ?? ($field['type'] === 'link' ? 'https://' : '') }}">
        @endswitch
    @endif
</div>
