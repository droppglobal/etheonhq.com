@if (!empty($lead))
    {{ Form::model($lead, ['route' => ['lead.update', $lead->id], 'method' => 'PUT']) }}
@else
    {{ Form::open(['route' => 'lead.store', 'method' => 'post']) }}
@endif
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter name')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
            {{ Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('Enter email')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('phone', __('Phone'), ['class' => 'form-label']) }}
            {{ Form::text('phone', null, ['class' => 'form-control', 'placeholder' => __('Enter phone')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('source', __('Source'), ['class' => 'form-label']) }}
            {{ Form::select('source', $sources, null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
            {{ Form::select('property_id', $properties, null, ['class' => 'form-control', 'id' => 'lead_property']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
            <select name="unit_id" id="lead_unit" class="form-control" data-selected="{{ optional($lead)->unit_id }}">
                <option value="">{{ __('Any unit') }}</option>
            </select>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
            {{ Form::select('status', $statuses, null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('follow_up_date', __('Follow-up Date'), ['class' => 'form-label']) }}
            {{ Form::date('follow_up_date', null, ['class' => 'form-control']) }}
        </div>
        @if (empty($lead))
            <div class="form-group col-md-12">
                {{ Form::label('message', __('Enquiry'), ['class' => 'form-label']) }}
                {{ Form::textarea('message', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => __('What are they looking for?')]) }}
            </div>
        @endif
        <div class="form-group col-md-12">
            {{ Form::label('notes', __('Internal Notes'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(!empty($lead) ? __('Update') : __('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
<script>
    (function() {
        function loadUnits() {
            var propertyId = $('#lead_property').val();
            var $unit = $('#lead_unit');
            var selected = String($unit.data('selected') || '');
            $unit.html('<option value="">{{ __('Any unit') }}</option>');
            if (!propertyId) return;
            $.get('{{ route('property.unit', ':id') }}'.replace(':id', propertyId), function(data) {
                $.each(data, function(id, name) {
                    $unit.append($('<option>').val(id).text(name).prop('selected', String(id) === selected));
                });
            });
        }
        $('#lead_property').on('change', function() { $('#lead_unit').data('selected', ''); loadUnits(); });
        loadUnits();
    })();
</script>
