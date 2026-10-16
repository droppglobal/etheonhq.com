<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #1f2937; }
        .head { width: 100%; border-bottom: 2px solid #1f2937; margin-bottom: 10px; }
        .company { font-size: 13px; font-weight: bold; }
        h2 { margin: 0; font-size: 15px; }
        .muted { color: #6b7280; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #f3f4f6; text-align: left; padding: 5px 6px; border-bottom: 1px solid #d1d5db; font-size: 9px; text-transform: uppercase; }
        table.data td { padding: 4px 6px; border-bottom: 1px solid #eef0f3; vertical-align: top; }
        table.data tr.total td { font-weight: bold; border-top: 2px solid #1f2937; background: #fafafa; }
        .r { text-align: right; }
        table.summary { margin-bottom: 10px; border-collapse: collapse; }
        table.summary td { border: 1px solid #e5e7eb; padding: 4px 10px; }
        .foot { margin-top: 10px; font-size: 8px; color: #6b7280; }
    </style>
</head>
<body>
    <table class="head">
        <tr>
            <td><div class="company">{{ $settings['company_name'] ?? '' }}</div><div class="muted">{{ $settings['company_address'] ?? '' }}</div></td>
            <td style="text-align:right"><h2>{{ $title }}</h2>@if ($subtitle)<div class="muted">{{ $subtitle }}</div>@endif</td>
        </tr>
    </table>
    @if ($summary)
        <table class="summary"><tr>
            @foreach ($summary as $label => $value)<td><span class="muted">{{ $label }}</span><br><strong>{{ $value }}</strong></td>@endforeach
        </tr></table>
    @endif
    <table class="data">
        <thead><tr>@foreach ($header as $i => $h)<th class="{{ ($align[$i] ?? '') == 'right' ? 'r' : '' }}">{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>@foreach (array_values((array) $row) as $i => $cell)<td class="{{ ($align[$i] ?? '') == 'right' ? 'r' : '' }}">{{ $cell }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($header) }}" class="muted" style="text-align:center;padding:14px">{{ __('No data.') }}</td></tr>
            @endforelse
            @if ($totals)
                <tr class="total">@foreach (array_values($totals) as $i => $cell)<td class="{{ ($align[$i] ?? '') == 'right' ? 'r' : '' }}">{{ $cell }}</td>@endforeach</tr>
            @endif
        </tbody>
    </table>
    <div class="foot">{{ __('Generated on :date', ['date' => dateFormat(now()) . ' ' . now()->format('H:i')]) }}</div>
</body>
</html>
