@php
    $profile = asset(Storage::url('upload/profile'));
@endphp
<div class="dt-responsive table-responsive">
    <table class="table table-hover advance-datatable">
        <thead>
            <tr>
                <th>{{ __('Tenant') }}</th>
                <th>{{ __('Email') }}</th>
                <th>{{ __('Contact') }}</th>
                <th>{{ __('Property') }}</th>
                <th>{{ __('Unit') }}</th>
                <th>{{ __('Total Amount') }}</th>
                <th>{{ __('Paid Amount') }}</th>
                <th>{{ __('Due Amount') }}</th>
                <th>{{ __('Status') }}</th>

            </tr>
        </thead>
        <tbody>
            @foreach ($tenants as $tenant)
                <tr>

                    <td class="table-user">
                        <img src="{{ !empty($tenant->user->profile) ? fetch_file($tenant->user->profile, 'upload/profile/') : $profile }}"
                            alt="" class="mr-2 avatar-sm rounded-circle user-avatar">
                        <a href="#"
                            class="text-body font-weight-semibold">{{ ucfirst(!empty($tenant->user) ? $tenant->user->first_name : '') . ' ' . ucfirst(!empty($tenant->user) ? $tenant->user->last_name : '') }}</a>
                    </td>


                    <td>{{ !empty($tenant->user) ? $tenant->user->email : '-' }}</td>
                    <td>{{ !empty($tenant->user) ? $tenant->user->phone_number : '-' }}</td>
                    <td> {{ !empty($tenant->properties) ? $tenant->properties->name : '-' }}</td>
                    <td> {{ !empty($tenant->units) ? $tenant->units->name : '-' }}</td>
                    @php
                        $invoice = \App\Models\Tenant::invoiceDetail($tenant->id);
                    @endphp

                    <td>{{ priceformat($invoice['total'] ?? 0) }}</td>
                    <td>{{ priceformat($invoice['paid'] ?? 0) }}</td>
                    <td>{{ priceformat($invoice['due'] ?? 0) }}</td>
                    @if ( !empty($tenant->units) ? $tenant->units->is_occupied == 1 : '-')
                        <td class="text-success">
                            <i class="fas fa-circle f-10 m-r-10"></i>
                            {{ __('Active') }}
                        </td>
                    @else
                        <td class="text-danger">
                            <i class="fas fa-circle f-10 m-r-10"></i>
                            {{ __('Exit') }}
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
