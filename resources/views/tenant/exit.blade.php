{{ Form::model($tenant, ['route' => ['tenant.exitupdate', $tenant->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        @php $depositHeld = \App\Models\DepositTransaction::balance($tenant->id); @endphp
        @if ($depositHeld > 0)
            <div class="col-md-12 mb-3">
                <div class="alert alert-light-warning mb-0">
                    {{ __('Security deposit held') }}: <strong>{{ priceFormat($depositHeld) }}</strong>.
                    {{ __('Record deductions and the refund in the tenant Deposit tab.') }}
                </div>
            </div>
        @endif
        <div class="form-group col-md-6">
            {{ Form::label('exit_amount', __('Exit Amount'), ['class' => 'form-label']) }}
            {{ Form::number('exit_amount', null, ['class' => 'form-control', 'placeholder' => __('Enter Exit Amount')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('extra_charge', __('Extra Charge'), ['class' => 'form-label']) }}
            {{ Form::number('extra_charge', null, ['class' => 'form-control', 'placeholder' => __('Enter Extra Charge')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('exit_date', __('Exit Date'), ['class' => 'form-label']) }}
            {{ Form::date('exit_date', null, ['class' => 'form-control', 'placeholder' => __('Enter Exit Date')]) }}
        </div>
        <div class="form-group col-md-6 ">
            {{ Form::label('lease_end_date', __('Lease End Date'), ['class' => 'form-label']) }}
            {{ Form::date('lease_end_date', $tenant->lease_end_date, ['class' => 'form-control','readonly']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('reason', __('Reason'), ['class' => 'form-label']) }}
            {{ Form::textarea('reason', null, ['class' => 'form-control', 'placeholder' => __('Enter Reason'), 'rows' => 2]) }}
        </div>

    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
