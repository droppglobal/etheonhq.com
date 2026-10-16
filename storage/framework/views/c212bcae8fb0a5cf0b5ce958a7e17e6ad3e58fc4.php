<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Agreement')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item" aria-current="page"> <?php echo e(__('Agreement')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row g-3 align-items-end">

                        <div class="col-lg-4 col-md-6">
                            <?php echo Form::select('property', $properties, null, ['class' => 'form-control select2 property']); ?>

                        </div>

                        <div class="col-lg-4 col-md-6">
                            <?php echo Form::select('unit', [], null, ['class' => 'form-control select2 unit', 'placeholder' => __('Select Unit')]); ?>

                        </div>
                        <div class="col-lg-1 col-md-6">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary subbtn">
                                    <i class="ti ti-search align-text-bottom"></i>
                                </button>
                                <button type="button" class="btn btn-primary btn-rounded"
                                    onclick="window.location.reload();">
                                    <i class="ti ti-refresh align-text-bottom"></i>
                                </button>
                            </div>
                        </div>
                        <?php if(Gate::check('create agreement')): ?>
                            <div class="col-lg-auto col-md-12 ms-lg-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="<?php echo e(route('agreement.create')); ?>" data-title="<?php echo e(__('Create Agreement')); ?>">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    <?php echo e(__('Create Agreement')); ?>

                                </a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="filter-section"></div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('script-page'); ?>
    <script>
        $(document).ready(function() {
            $('.subbtn').trigger('click');
        });

        $(document).on('click', '.subbtn', function() {
            $.ajax({
                type: 'GET',
                url: '<?php echo e(route('agreement.index')); ?>',
                data: {
                    property: $('.property').val(),
                    unit: $('.unit').val(),
                },
                success: function(response) {
                    $('.filter-section').html(response.html);
                    feather.replace();
                    datatable();
                    $('[data-bs-toggle="tooltip"]').tooltip();
                },
                error: function() {
                    $('.filter-section').html(`
                    <div class="text-center text-danger py-3">
                        <?php echo e(__('Failed to load data.')); ?>

                    </div>
                `);
                }
            });
            return false;
        });

        $(document).on('change', '.property', function() {

            let property_id = $(this).val();

            $('.unit').html('<option value=""><?php echo e(__('Select Unit')); ?></option>').trigger('change');
            $('.tenant').html('<option value=""><?php echo e(__('Select Tenant')); ?></option>').trigger('change');

            if (!property_id) return;

            $.ajax({
                url: '<?php echo e(route('property.unit', ':id')); ?>'.replace(':id', property_id),
                type: 'GET',
                success: function(data) {

                    $.each(data, function(key, value) {
                        $('.unit').append('<option value="' + key + '">' + value +
                            '</option>');
                    });

                    $('.unit').trigger('change.select2');
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/agreement/index.blade.php ENDPATH**/ ?>