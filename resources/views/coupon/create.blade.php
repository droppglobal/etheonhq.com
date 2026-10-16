{{ Form::open(['url' => 'coupons', 'method' => 'post']) }}
<div class="modal-body">
    @include('ai.writer', ['type' => 'coupon'])
    <div class="row">
        <div class="form-group  col-md-6">
            {{ Form::label('name', __('Coupon Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter coupon name')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('type', __('Coupon Type'), ['class' => 'form-label']) }}
            {{ Form::select('type', $type, null, ['class' => 'form-control hidesearch']) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('code', __('Coupon Code'), ['class' => 'form-label']) }}
            {{ Form::text('code', null, ['class' => 'form-control', 'placeholder' => __('Enter coupon code')]) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('rate', __('Discount Rate'), ['class' => 'form-label']) }}
            {{ Form::number('rate', null, ['class' => 'form-control', 'placeholder' => __('Enter coupon discount rate')]) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('valid_for', __('Valid For'), ['class' => 'form-label']) }}
            {{ Form::date('valid_for', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('use_limit', __('Number Of Times This Coupon Can Be Used'), ['class' => 'form-label']) }}
            {{ Form::number('use_limit', null, ['class' => 'form-control', 'placeholder' => __('Enter coupon use limit')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('applicable_packages', __('Applicable Packages'), ['class' => 'form-label']) }}
            {{ Form::select('applicable_packages[]', $packages, null, ['class' => 'form-control  select2', 'multiple']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
            {{ Form::select('status', $status, null, ['class' => 'form-control hidesearch']) }}
        </div>
    </div>
</div>
<div class="modal-footer">

    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
