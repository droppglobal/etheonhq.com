<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Maintenance Report')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item" aria-current="page"> <?php echo e(__('Maintenance Report')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script>
        $(function() {
            $('#property_id').on('change', function() {
                let property_id = $(this).val();

                $('#unit_id').html(`<option value=""><?php echo e(__('Select Unit')); ?></option>`);

                if (!property_id) return;

                $.get('<?php echo e(route('property.unit', ':id')); ?>'.replace(':id', property_id), function(data) {
                    let options = `<option value=""><?php echo e(__('Select Unit')); ?></option>`;
                    $.each(data, function(key, value) {
                        options += `<option value="${key}">${value}</option>`;
                    });
                    $('#unit_id').html(options).trigger('change');
                });
            });

            $('.subbtn').on('click', function(e) {
                e.preventDefault();
                loadReport();
            });

            function loadReport() {
                $.get('<?php echo e(route('report.maintenance')); ?>', {
                    property_id: $('#property_id').val(),
                    unit_id: $('#unit_id').val(),
                    tenant_id: $('#tenant_id').val(),
                    status: $('status').val()
                }, function(response) {
                    $('#maintenance-report').html(response.html);
                    if (typeof datatable === 'function') datatable();
                }).fail(function() {
                    console.error('Error loading tenant report');
                });
            }

            loadReport();
        });
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2 flex-wrap">

                        <div class="d-flex flex-wrap gap-3 align-items-end">

                            <div class="col">
                                <?php echo e(Form::select('tenant_id', $tenant_options, request('tenant_id'), [
                                    'class' => 'form-control select2',
                                    'id' => 'tenant_id',
                                ])); ?>

                            </div>
                            <div class="col">
                                <?php echo e(Form::select('property_id', $property, request('property_id'), [
                                    'class' => 'form-control select2',
                                    'id' => 'property_id',
                                ])); ?>

                            </div>

                            <div class="col">

                                <div class="unit_div">
                                    <select class="form-control select2 unit" id="unit_id" name="unit_id">
                                        <option value=""><?php echo e(__('Select Unit')); ?></option>
                                        <?php if(!empty($units)): ?>
                                            <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($id); ?>"
                                                    <?php echo e(request('unit_id') == $id ? 'selected' : ''); ?>>
                                                    <?php echo e($name); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col">
                                <?php echo e(Form::select('status', $status, request('status'), [
                                    'class' => 'form-control hidesearch',
                                    'placeholder' => __('Select Status'),
                                ])); ?>

                            </div>
                            <div>
                                <button type="button" class="btn btn-secondary subbtn">
                                    <i class="ti ti-search align-text-bottom"></i>
                                </button>
                                <button type="button" class="btn btn-primary btn-rounded"
                                    onclick="window.location.reload();">
                                    <i class="ti ti-refresh align-text-bottom"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div id="maintenance-report">

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/report/maintenance.blade.php ENDPATH**/ ?>