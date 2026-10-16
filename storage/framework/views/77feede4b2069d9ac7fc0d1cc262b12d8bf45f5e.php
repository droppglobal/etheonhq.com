<?php echo e(Form::model($agreement, ['route' => ['agreement.update', $agreement->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data'])); ?>


<div class="modal-body">
    <?php echo $__env->make('ai.writer', ['type' => 'agreement'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="row">

        <div class="form-group col-md-6 col-lg-6">
            <?php echo e(Form::label('property_id', __('Property'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::select('property_id', $property, null, ['class' => 'form-control select2', 'id' => 'property_id'])); ?>

        </div>
        <div class="form-group col-lg-6 col-md-6">
            <?php echo e(Form::label('unit_id', __('Unit'), ['class' => 'form-label'])); ?>

            <div class="unit_div">
                <select class="form-control select2 unit" id="unit_id" name="unit_id">
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
            <?php echo e(Form::label('date', __('Date'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::date('date', null, ['class' => 'form-control'])); ?>

        </div>
        <div class="form-group col-md-6 col-lg-6">
            <?php echo e(Form::label('agreement_start_date', __('Agreement Start Date'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::date('agreement_start_date', null, ['class' => 'form-control'])); ?>

        </div>
        <div class="form-group col-md-6 col-lg-6">
            <?php echo e(Form::label('agreement_end_date', __('Agreement End Date'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::date('agreement_end_date', null, ['class' => 'form-control'])); ?>

        </div>
        <div class="form-group col-md-6 col-lg-6">
            <?php echo e(Form::label('status', __('Status'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::select('status', $status, null, ['class' => 'form-control hidesearch'])); ?>

        </div>
        <div class="form-group  col-md-12">
            <?php echo e(Form::label('terms_condition', __('Terms & Condition'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::textarea('terms_condition', null, ['class' => 'form-control classic-editor'])); ?>

        </div>
        <div class="form-group  col-md-12">
            <?php echo e(Form::label('description', __('Description'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::textarea('description', null, ['class' => 'form-control classic-editor'])); ?>

        </div>

    </div>
</div>
<div class="modal-footer">
    <?php echo e(Form::submit(__('Update'), ['class' => 'btn btn-secondary btn-rounded'])); ?>

</div>
<?php echo e(Form::close()); ?>



<script>
    var selectedUnit = "<?php echo e($agreement->unit_id); ?>";
    var selectedTenant = "<?php echo e($agreement->tenant_id); ?>";

    $(document).on('change', '#property_id', function() {
        let property_id = $(this).val();

        if (!property_id) return;

        $.ajax({
            url: '<?php echo e(route('property.unit', ':id')); ?>'.replace(':id', property_id),
            type: 'GET',
            success: function(data) {

                let $unit = $('#unit_id');
                $unit.empty().append('<option value=""><?php echo e(__('Select Unit')); ?></option>');

                $.each(data, function(key, value) {
                    let selected = (key == selectedUnit) ? 'selected' : '';
                    $unit.append('<option value="' + key + '" ' + selected + '>' + value +
                        '</option>');
                });

                $unit.trigger('change');
            }
        });
    });

    $(document).on('change', '#unit_id', function() {
        let unit_id = $(this).val();

        if (!unit_id) return;

        $.ajax({
            url: '<?php echo e(route('unit.by.tenant', ':id')); ?>'.replace(':id', unit_id),
            type: 'GET',
            success: function(data) {

                let $tenant = $('#tenant');
                $tenant.empty().append('<option value=""><?php echo e(__('Select Tenant')); ?></option>');

                $.each(data, function(key, value) {
                    let selected = (key == selectedTenant) ? 'selected' : '';
                    $tenant.append('<option value="' + key + '" ' + selected + '>' + value +
                        '</option>');
                });

                $tenant.trigger('change');
            }
        });
    });

    $(document).ready(function() {
        if ($('#property_id').val()) {
            $('#property_id').trigger('change');
        }
    });
</script>
<?php /**PATH /home/etheonbe/public_html/resources/views/agreement/edit.blade.php ENDPATH**/ ?>