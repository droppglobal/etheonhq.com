 <div class="dt-responsive table-responsive">
     <table class="table table-hover advance-datatable">
         <thead>
             <tr>
                 <th>{{ __('Invoice') }}</th>
                 <th>{{ __('Property') }}</th>
                 <th>{{ __('Unit') }}</th>
                 <th>{{ __('Invoice Month') }}</th>
                 <th>{{ __('End Date') }}</th>
                 <th>{{ __('Amount') }}</th>
                 <th>{{ __('Status') }}</th>
                 @if (Gate::check('show invoice'))
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
                     <td>{{ $invoice->properties->name ?? '-' }}</td>
                     <td>{{ $invoice->units->name ?? '-' }}</td>
                     <td>{{ date('F Y', strtotime($invoice->invoice_month)) }}</td>
                     <td>{{ dateFormat($invoice->end_date) }}</td>
                     <td>{{ priceFormat($invoice->getInvoiceSubTotalAmount()) }}</td>
                     <td>
                         @if ($invoice->status == 'unpaid')
                             <span class="badge bg-danger">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                         @elseif($invoice->status == 'paid')
                             <span class="badge bg-success">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                         @elseif($invoice->status == 'paid')
                             <span class="badge bg-warning">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                         @endif
                     </td>
                     @if (Gate::check('show invoice'))
                         <td>
                             <div class="cart-action">
                                 @can('show invoice')
                                     <a class="avtar avtar-xs btn-link-warning text-warning"
                                         href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                         data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}">
                                         <i data-feather="eye"></i>
                                     </a>
                                 @endcan
                             </div>
                         </td>
                     @endif
                 </tr>
             @endforeach
         </tbody>
     </table>
 </div>
