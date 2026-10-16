<div class="dt-responsive table-responsive">
    <table class="table table-hover advance-datatable">
        <thead>
            <tr>
                <th>{{ __('Expense') }}</th>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Property') }}</th>
                <th>{{ __('Unit') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('Receipt') }}</th>
                @if (Gate::check('show expense'))
                    <th class="text-right">{{ __('Action') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($expenses as $expense)
                <tr role="row">
                    <td>{{ expensePrefix() . $expense->expense_id }} </td>
                    <td> {{ $expense->title }} </td>
                    <td> {{ !empty($expense->properties) ? $expense->properties->name : '-' }} </td>
                    <td> {{ !empty($expense->units) ? $expense->units->name : '-' }} </td>
                    <td> {{ !empty($expense->types) ? $expense->types->title : '-' }} </td>
                    <td> {{ dateFormat($expense->date) }} </td>
                    <td> {{ priceFormat($expense->amount) }} </td>
                    <td>
                        @if (!empty($expense->receipt))
                            <a href="{{ !empty($expense->receipt) ? fetch_file($expense->receipt, 'upload/receipt/') : '#' }}"
                                download="download"><i data-feather="download"></i></a>
                        @else
                            -
                        @endif
                    </td>
                    @if (Gate::check('show expense'))
                        <td class="text-right">
                            <div class="cart-action">
                                {!! Form::open(['method' => 'DELETE', 'route' => ['expense.destroy', $expense->id]]) !!}
                                @can('show expense')
                                    <a class="avtar avtar-xs btn-link-warning text-warning customModal" data-size="lg"
                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}"
                                        href="#" data-url="{{ route('expense.show', $expense->id) }}"
                                        data-title="{{ __('Expense Details') }}"> <i data-feather="eye"></i></a>
                                @endcan

                                {!! Form::close() !!}
                            </div>

                        </td>
                    @endif
                </tr>
            @endforeach

        </tbody>
    </table>
</div>
