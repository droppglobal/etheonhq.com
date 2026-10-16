{{ Form::open(['url' => 'subscriptions']) }}
<div class="modal-body">
    @include('ai.writer', ['type' => 'subscription'])
    <div class="row">
        <div class="form-group">
            {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
            {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter subscription title'), 'required' => 'required']) }}
        </div>
        <div class="form-group">
            {{ Form::label('interval', __('Interval'), ['class' => 'form-label']) }}
            {!! Form::select('interval', $intervals, null, [
                'class' => 'form-control hidesearch',
                'required' => 'required',
            ]) !!}
        </div>
        <div class="form-group">
            {{ Form::label('package_amount', __('Package Amount'), ['class' => 'form-label']) }}
            {{ Form::number('package_amount', null, ['class' => 'form-control', 'placeholder' => __('Enter package amount'), 'step' => '0.01']) }}
        </div>
        <div class="form-group">
            {{ Form::label('user_limit', __('User Limit'), ['class' => 'form-label']) }}
            {{ Form::number('user_limit', null, ['class' => 'form-control', 'placeholder' => __('Enter user limit'), 'required' => 'required']) }}
        </div>
        <div class="form-group">
            {{ Form::label('property_limit', __('Property Limit'), ['class' => 'form-label']) }}
            {{ Form::number('property_limit', null, ['class' => 'form-control', 'placeholder' => __('Enter property limit'), 'required' => 'required']) }}
        </div>
        <div class="form-group">
            {{ Form::label('tenant_limit', __('Tenant Limit'), ['class' => 'form-label']) }}
            {{ Form::number('tenant_limit', null, ['class' => 'form-control', 'placeholder' => __('Enter tenant limit'), 'required' => 'required']) }}
        </div>

        <div class="form-group col-md-6">
            <div class="form-check form-switch custom-switch-v1 mb-2">
                <input type="checkbox" class="form-check-input input-secondary" name="enabled_logged_history"
                    id="enabled_logged_history">
                {{ Form::label('enabled_logged_history', __('Show User Logged History'), ['class' => 'form-label']) }}
            </div>
        </div>
        <div class="form-group col-md-6">
            <div class="form-check form-switch custom-switch-v1 mb-2">
                <input type="checkbox" class="form-check-input input-secondary" name="enabled_openai"
                    id="enabled_openai" value="1">
                <label for="enabled_openai" class="form-label">
                    {{ __('Open Ai Support') }}
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <div class="form-check form-switch custom-switch-v1 mb-2">
                <input type="checkbox" class="form-check-input input-secondary" name="enabled_n8n" id="enabled_n8n"
                    value="1">
                <label for="enabled_n8n" class="form-label">
                    {{ __('N8n') }}
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <div class="form-check form-switch custom-switch-v1 mb-2">
                <input type="checkbox" class="form-check-input input-secondary" name="enabled_custom_domain" id="enabled_custom_domain" value="1">
                <label for="enabled_custom_domain" class="form-label">{{ __('Custom website domain') }}</label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <div class="form-check form-switch custom-switch-v1 mb-2">
                <input type="checkbox" class="form-check-input input-secondary" name="free_trial" id="freeTrail"
                    value="1">
                <label for="free_trial" class="form-label">
                    {{ __('Free Trial') }}
                </label>
            </div>
        </div>
        <div class="form-group" id="daysField" style="display: none;">
            {{ Form::label('free_trial_days', __('Fress Trail Days'), ['class' => 'form-label']) }}
            {!! Form::number('free_trial_days', null, ['class' => 'form-control', 'placeholder' => 'Enter free trail days']) !!}
        </div>

    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
<script>
    document.getElementById('freeTrail').addEventListener('change', function() {
        document.getElementById('daysField').style.display = this.checked ? 'block' : 'none';
    });

    if (document.getElementById('freeTrail').checked) {
        document.getElementById('daysField').style.display = 'block';
    }
</script>
