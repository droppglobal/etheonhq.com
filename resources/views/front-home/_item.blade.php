{{-- One repeatable item (highlight box, testimonial, FAQ question...). $itemDef, $index, $item --}}
<div class="fh-item border rounded p-3 mb-2 bg-white">
    <div class="d-flex align-items-center mb-2">
        <span class="fh-handle text-muted me-2" title="{{ __('Drag to reorder') }}" style="cursor: move;"><i class="ti ti-grip-vertical f-18"></i></span>
        <strong class="small fh-item-title">{{ __($itemDef['label']) }}</strong>
        <button type="button" class="btn btn-sm btn-light-danger ms-auto fh-remove-item" title="{{ __('Remove') }}"><i class="ti ti-trash"></i></button>
    </div>
    <input type="hidden" name="content_value[items][{{ $index }}][id]" value="{{ $item['id'] ?? '' }}">
    <div class="row">
        @foreach ($itemDef['fields'] as $field)
            @include('front-home._field', [
                'field' => $field,
                'name' => 'content_value[items][' . $index . '][' . $field['key'] . ']',
                'value' => $item[$field['key']] ?? ($field['type'] === 'select' ? array_key_first($field['options']) : ''),
                'path' => $item[$field['key'] . '_path'] ?? '',
                'pathName' => 'content_value[items][' . $index . '][' . $field['key'] . '_path]',
            ])
        @endforeach
    </div>
</div>
