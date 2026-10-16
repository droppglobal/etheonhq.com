{{-- Website menu items, shared by the desktop header and the mobile menu. $site, $user, $routeName, $linkClass --}}
@php
    $c = $site->chrome;
    $items = [];
    if ($site->menuEnabled('show_home')) $items[] = [route('web.page', $user->code), __('Home'), ['web.page']];
    if ($site->menuEnabled('show_properties')) $items[] = [route('property.home', ['code' => $user->code]), __('Properties'), ['property.home', 'property.detail', 'search.filter']];
    foreach ($site->menuPages as $p) $items[] = [route('web.custom.page', [$user->code, $p['slug']]), $p['title'], [], request()->route('slug') === $p['slug']];
    if ($site->menuEnabled('show_blog')) $items[] = [route('blog.home', ['code' => $user->code]), __('Blog'), ['blog.home', 'blog.detail']];
    foreach ((array) ($c['items'] ?? []) as $link) {
        if (!empty($link['label']) && !empty($link['link'])) $items[] = [$link['link'], $link['label'], [], false, true];
    }
    if ($site->menuEnabled('show_contact')) $items[] = [route('contact.home', ['code' => $user->code]), __('Contact'), ['contact.home']];
@endphp
@foreach ($items as $item)
    @php $active = in_array($routeName, $item[2]) || !empty($item[3]); @endphp
    <li>
        <a class="{{ $linkClass ?? '' }} {{ $active ? 'active custom-active-style' : '' }}" href="{{ $item[0] }}"
            @if (!empty($item[4]) && \Illuminate\Support\Str::startsWith($item[0], 'http')) target="_blank" rel="noopener" @endif>{{ $item[1] }}</a>
    </li>
@endforeach
