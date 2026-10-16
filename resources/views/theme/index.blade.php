@extends('theme.main')
@section('content')
    {{-- sections in the owner's order (Frontend Manager > Home Page), only the ones switched on --}}
    @foreach ($sections as $section)
        @include('theme.sections.' . $section['def']['view'], ['c' => $section['c'], 'sectionId' => 'section-' . $section['row']->id])
    @endforeach
@endsection
