<div class="card-body pt-0">
    <div class="dt-responsive table-responsive">
        <table class="table table-hover advance-datatable">
            <thead>
                <tr>
                    <th>{{ __('Property') }}</th>
                    <th>{{ __('Vacant Unit') }}</th>
                    <th>{{ __('Occupied Unit') }}</th>
                    <th>{{ __('Total Unit') }}</th>

                </tr>
            </thead>
            <tbody>

                @foreach ($proUnits as $unitGroup)
                    <tr>
                        <td>{{ $unitGroup->property->name ?? '-' }}</td>
                        <td>{{ $unitGroup->vacant }}</td>
                        <td>{{ $unitGroup->occupied }}</td>
                        <td>{{ $unitGroup->total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
