<div class="dt-responsive table-responsive">
    <table class="table table-hover advance-datatable">
        <thead>
            <tr>

                <th>{{ __('Month') }}</th>
                <th>{{ __('Income') }}</th>
                <th>{{ __('Expense') }}</th>
                <th>{{ __('Profit & Loss') }}</th>
            </tr>
        </thead>
        <tbody>
            {{-- @dd($report) --}}
            @foreach ($report as $data)
                <tr>
                    <td>{{ $data->month }}</td>
                    <td>{{ priceFormat($data->income) }}</td>
                    <td>{{ priceFormat($data->expense) }}</td>
                    @php
                        $profitClass =
                            $data->profit < 0 ? 'text-danger' : ($data->profit > 0 ? 'text-success' : 'text-muted');
                    @endphp

                    <td class="{{ $profitClass }}">
                        {{ priceFormat($data->profit) }}
                    </td>
                </tr>
            @endforeach

        </tbody>
    </table>
</div>
