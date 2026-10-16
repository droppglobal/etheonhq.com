<?php
    $user = \Auth::user();
    $tenant = $user->tenants;
?>

<?php echo e(Form::model($maintenanceRequest, ['route' => ['maintenance-request.update', $maintenanceRequest->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data'])); ?>

<div class="modal-body">
    <?php echo $__env->make('ai.writer', ['type' => 'maintenance_request'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="row">
        <?php if($user->type == 'tenant'): ?>
            <?php echo e(Form::hidden('property_id', !empty($tenant) ? $tenant->property : null)); ?>

            <?php echo e(Form::hidden('unit_id', !empty($tenant) ? $tenant->unit : null)); ?>

        <?php else: ?>
            
            <div class="form-group col-md-6 col-lg-6">
                <?php echo e(Form::label('property_id', __('Property'), ['class' => 'form-label'])); ?>

                <?php echo e(Form::select('property_id', $property, $maintenanceRequest->property_id, [
                    'class' => 'form-control select2',
                    'id' => 'property_id',
                    'placeholder' => __('Select Property'),
                ])); ?>

            </div>

            
            <div class="form-group col-md-6 col-lg-6">
                <?php echo e(Form::label('unit_id', __('Unit'), ['class' => 'form-label'])); ?>

                <div class="unit_div">
                    <select class="form-control select2" id="unit_id" name="unit_id">
                        <option value=""><?php echo e(__('Select Unit')); ?></option>
                    </select>
                </div>
            </div>

            
            <div class="form-group col-md-6 col-lg-6">
                <?php echo e(Form::label('tenant', __('Tenant'), ['class' => 'form-label'])); ?>

                <select class="form-control select2" id="tenant" name="tenant_id">
                    <option value=""><?php echo e(__('Select Tenant')); ?></option>
                </select>
            </div>

            
            <div class="form-group col-md-6 col-lg-6">
                <?php echo e(Form::label('request_date', __('Request Date'), ['class' => 'form-label'])); ?>

                <?php echo e(Form::date('request_date', $maintenanceRequest->request_date, ['class' => 'form-control'])); ?>

            </div>

            
            <div class="form-group col-md-6 col-lg-6">
                <?php echo e(Form::label('maintainer_id', __('Maintainer'), ['class' => 'form-label'])); ?>

                <select class="form-control select2" id="maintainer_id" name="maintainer_id">
                    <option value=""><?php echo e(__('Select Maintainer')); ?></option>
                </select>
            </div>

            
            <div class="form-group col-md-6 col-lg-6">
                <?php echo e(Form::label('vendor_id', __('Outside Vendor (optional)'), ['class' => 'form-label'])); ?>

                <?php echo e(Form::select('vendor_id', $vendors, $maintenanceRequest->vendor_id ?: null, ['class' => 'form-control', 'placeholder' => __('None')])); ?>

            </div>
        <?php endif; ?>

        
        <div class="form-group col-md-6 col-lg-6">
            <?php echo e(Form::label('issue_type', __('Issue Type'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::select('issue_type', $types, $maintenanceRequest->issue_type, ['class' => 'form-control select2'])); ?>

        </div>

        
        <div class="form-group col-md-6 col-lg-6">
            <?php echo e(Form::label('status', __('Status'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::select('status', $status, $maintenanceRequest->status, ['class' => 'form-control hidesearch'])); ?>

        </div>

        
        <div class="form-group col-md-12 col-lg-12">
            <?php echo e(Form::label('issue_attachment', __('Issue Attachment'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::file('issue_attachment', ['class' => 'form-control'])); ?>

            <?php if(!empty($maintenanceRequest->issue_attachment)): ?>
                <div class="mt-1">
                    <a href="<?php echo e(asset(Storage::url($maintenanceRequest->issue_attachment))); ?>" target="_blank">
                        <?php echo e(__('View Current Attachment')); ?>

                    </a>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="form-group col-md-12 col-lg-12">
            <?php echo e(Form::label('notes', __('Notes'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::textarea('notes', $maintenanceRequest->notes, ['class' => 'form-control', 'rows' => 3])); ?>

        </div>
    </div>
</div>

<div class="modal-footer">
    <?php echo e(Form::submit(__('Update'), ['class' => 'btn btn-secondary btn-rounded'])); ?>

</div>
<?php echo e(Form::close()); ?>


<script>
    $(document).ready(function() {

        var selected_unit = "<?php echo e($maintenanceRequest->unit_id ?? ''); ?>";
        var selected_tenant = "<?php echo e($maintenanceRequest->tenant_id ?? ''); ?>";
        var selected_maintainer = "<?php echo e($maintenanceRequest->maintainer_id ?? ''); ?>";

        // ✅ Remove previous listeners before attaching new ones
        $(document).off('change', '#property_id').on('change', '#property_id', function() {
            let property_id = $(this).val();

            $('#unit_id').html('<option value=""><?php echo e(__('Select Unit')); ?></option>').trigger('change');
            $('#tenant').html('<option value=""><?php echo e(__('Select Tenant')); ?></option>').trigger('change');
            $('#maintainer_id').html('<option value=""><?php echo e(__('Select Maintainer')); ?></option>')
                .trigger('change');

            if (!property_id) return;

            // Load Units
            $.ajax({
                url: '<?php echo e(route('property.unit', ':id')); ?>'.replace(':id', property_id),
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    $.each(data, function(key, value) {
                        let selected = (key == selected_unit) ? 'selected' : '';
                        $('#unit_id').append('<option value="' + key + '" ' +
                            selected + '>' + value + '</option>');
                    });
                    $('#unit_id').trigger('change.select2');

                    if (selected_unit) {
                        $('#unit_id').trigger('change');
                    }
                }
            });

            // Load Maintainers
            $.ajax({
                url: '<?php echo e(route('property.based.maintainer')); ?>',
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    property_id: property_id
                },
                success: function(data) {
                    $.each(data, function(key, value) {
                        let selected = (key == selected_maintainer) ? 'selected' :
                            '';
                        $('#maintainer_id').append('<option value="' + key + '" ' +
                            selected + '>' + value + '</option>');
                    });
                    $('#maintainer_id').trigger('change.select2');
                }
            });
        });

        $(document).off('change', '#unit_id').on('change', '#unit_id', function() {
            let unit_id = $(this).val();
            let property_id = $('#property_id').val();

            $('#tenant').html('<option value=""><?php echo e(__('Select Tenant')); ?></option>').trigger('change');

            if (!unit_id || !property_id) return;

            $.ajax({
                url: '<?php echo e(route('unit.by.tenant', ':id')); ?>'.replace(':id', unit_id),
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    $.each(data, function(key, value) {
                        let selected = (key == selected_tenant) ? 'selected' : '';
                        $('#tenant').append('<option value="' + key + '" ' +
                            selected + '>' + value + '</option>');
                    });
                    $('#tenant').trigger('change.select2');
                }
            });
        });

        // Auto-trigger on load
        if ($('#property_id').val()) {
            $('#property_id').trigger('change');
        }

    });
</script>
<?php /**PATH /home/etheonbe/public_html/resources/views/maintenance_request/edit.blade.php ENDPATH**/ ?>