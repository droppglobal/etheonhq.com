@extends('layouts.app')

@section('page-title')
    {{ __('Calendar') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
    </li>
    <li class="breadcrumb-item active">
        <a href="#">{{ __('Calendar') }}</a>
    </li>
@endsection

@push('css-page')
    <style>
        /* ── Legend badges ─────────────────────────────────────────── */
        .cal-legend {
            display: flex;
            gap: 1.25rem;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 1rem;
        }

        .cal-legend-item {
            display: flex;
            align-items: center;
            gap: .45rem;
            font-size: .82rem;
            font-weight: 500;
            color: #555;
        }

        .cal-legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 3px;
            display: inline-block;
        }

        .cal-filter { cursor: pointer; user-select: none; border: 1px solid #e5e7eb; border-radius: 20px; padding: 3px 10px 3px 8px; }
        .cal-filter.off { opacity: .4; }
        .cal-filter.off .cal-legend-dot { background: #cbd5e1 !important; }

        .fc-event {
            cursor: pointer !important;
        }
    </style>
@endpush

@push('script-page')
    <script>
        var eventData = {!! json_encode($eventData) !!};
        var calendarTypes = {!! json_encode($types) !!};
    </script>
    <script src="{{ asset('assets/js/plugins/index.global.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/calendar.js') }}"></script>
@endpush

@section('page-class')
    codex-calendar
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <div class="cal-legend">
                        @forelse ($types as $key => $type)
                            <span class="cal-legend-item cal-filter" data-type="{{ $key }}" title="{{ __('Click to show or hide') }}">
                                <span class="cal-legend-dot" style="background: {{ $type['color'] }}"></span>
                                {{ $type['label'] }} <span class="text-muted">({{ collect($eventData)->where('type', $key)->count() }})</span>
                            </span>
                        @empty
                            <span class="text-muted">{{ __('Nothing scheduled yet.') }}</span>
                        @endforelse
                    </div>

                    <div id="calendar" class="calendar"></div>

                </div>
            </div>
        </div>
    </div>
@endsection
