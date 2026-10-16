@extends('layouts.app')
@section('page-title')
    {{ __('Website Stats') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('front-home.index') }}">{{ __('Website') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Stats') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-12 mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <p class="text-muted mb-0">{{ __('Property page visits on your website and what they led to. Search engines and your own team are not counted.') }}</p>
            <div class="btn-group btn-group-sm">
                @foreach (\App\Support\WebsiteStats::periods() as $value => $label)
                    <a href="{{ route('website-stats', ['days' => $value]) }}" class="btn {{ $days === $value ? 'btn-secondary' : 'btn-light-secondary' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
        @foreach ([['views', __('Property views'), 'ti-eye', 'primary'], ['enquiries', __('Website enquiries'), 'ti-mail', 'success'], ['viewings', __('Viewings booked'), 'ti-calendar-event', 'warning'], ['applications', __('Applications'), 'ti-file-text', 'info']] as [$key, $label, $icon, $color])
            <div class="col-6 col-xl-3">
                <div class="card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="avtar avtar-l bg-light-{{ $color }}"><i class="ti {{ $icon }} f-24"></i></div>
                        <div>
                            <p class="mb-1 text-muted">{{ $label }}</p>
                            <h4 class="mb-0">{{ number_format($report['totals'][$key]) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h5>{{ __('Views per day') }}</h5></div>
                <div class="card-body"><div id="views-chart" style="min-height: 260px"></div></div>
            </div>
        </div>
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header"><h5>{{ __('By property') }}</h5></div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Property') }}</th>
                                    <th class="text-end">{{ __('Views') }}</th>
                                    <th class="text-end">{{ __('Enquiries') }}</th>
                                    <th class="text-end">{{ __('Viewings') }}</th>
                                    <th class="text-end">{{ __('Applications') }}</th>
                                    <th class="text-end" title="{{ __('Enquiries per 100 views') }}">{{ __('Enquiry rate') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($report['rows'] as $row)
                                    <tr>
                                        <td>
                                            @if ($row['property']->is_active)
                                                <a href="{{ \App\Support\WebsiteSite::propertyUrl($owner, $row['property']) }}" target="_blank">{{ $row['property']->name }} <i class="ti ti-external-link"></i></a>
                                            @else
                                                {{ $row['property']->name }} <span class="badge bg-light-secondary">{{ __('Hidden') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ number_format($row['views']) }}</td>
                                        <td class="text-end">{{ number_format($row['enquiries']) }}</td>
                                        <td class="text-end">{{ number_format($row['viewings']) }}</td>
                                        <td class="text-end">{{ number_format($row['applications']) }}</td>
                                        <td class="text-end">{{ $row['rate'] === null ? '–' : $row['rate'] . '%' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No properties yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script-page')
    <script>
        (function() {
            var data = @json($report['chart']);
            new ApexCharts(document.querySelector('#views-chart'), {
                chart: { type: 'bar', height: 260, toolbar: { show: false } },
                series: [{ name: @json(__('Views')), data: Object.values(data) }],
                xaxis: { categories: Object.keys(data), labels: { rotate: -45, hideOverlappingLabels: true } },
                yaxis: { labels: { formatter: function(v) { return Math.round(v); } } },
                dataLabels: { enabled: false },
                plotOptions: { bar: { borderRadius: 3, columnWidth: '60%' } },
                colors: ['#2ca58d']
            }).render();
        })();
    </script>
@endpush
