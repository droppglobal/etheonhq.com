<div class="dt-responsive table-responsive">
    <table class="table table-hover advance-datatable">
        <thead>
            <tr>
                <th>{{ __('Invoice') }}</th>
                <th>{{ __('Property') }}</th>
                <th>{{ __('Tenant') }}</th>
                <th>{{ __('Unit') }}</th>
                <th>{{ __('Invoice Month') }}</th>
                <th>{{ __('End Date') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('Status') }}</th>
                @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                    <th class="text-right">{{ __('Action') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($invoices as $invoice)
                <tr>
                    <td>
                        <a href="{{ route('invoice.show', encrypt($invoice->id)) }}">{{ invoicePrefix() . $invoice->invoice_id }}
                        </a>
                    </td>
                    <td>{{ !empty($invoice->properties) ? $invoice->properties->name : '-' }} </td>
                    <td>{{ !empty($invoice->tenants->user) ? $invoice->tenants->user->full_name : '-' }}</td>
                    <td>{{ !empty($invoice->units) ? $invoice->units->name : '-' }} </td>
                    <td>{{ date('F Y', strtotime($invoice->invoice_month)) }} </td>
                    <td>{{ dateFormat($invoice->end_date) }} </td>
                    <td>{{ priceFormat($invoice->getInvoiceSubTotalAmount()) }}</td>
                    <td>
                        @if ($invoice->status == 'unpaid')
                            <span class="badge bg-danger">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                        @elseif($invoice->status == 'paid')
                            <span class="badge bg-success">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                        @elseif($invoice->status == 'partial_paid')
                            <span class="badge bg-warning">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                        @endif
                    </td>
                    @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                        <td>
                            <div class="cart-action">
                                {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.destroy', $invoice->id]]) !!}

                                @can('show invoice')
                                    <a class="avtar avtar-xs btn-link-warning text-warning"
                                        href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}"> <i
                                            data-feather="eye"></i></a>
                                @endcan
                                @can('edit invoice')
                                    <a class="avtar avtar-xs btn-link-secondary text-secondary"
                                        data-bs-original-title="{{ __('Edit') }}"
                                        href="{{ route('invoice.edit', \Crypt::encrypt($invoice->id)) }}"
                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"> <i
                                            data-feather="edit"></i></a>
                                @endcan
                                @can('delete invoice')
                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Detete') }}"
                                        href="#"> <i data-feather="trash-2"></i></a>
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
