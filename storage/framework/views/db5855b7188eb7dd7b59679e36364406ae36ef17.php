<?php $__env->startSection('page-title'); ?>
    <?php echo e($title); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item" aria-current="page"><?php echo e($title); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-2 col-md-6">
                            <?php echo e(Form::select('property', $properties, request('property'), [
                                'class' => 'form-control select2 property',
                                'id' => 'property_id',
                            ])); ?>

                        </div>

                        <div class="col-lg-2 col-md-6">
                            <?php echo e(Form::select('unit', [], request('unit'), [
                                'class' => 'form-control select2 unit',
                                'id' => 'unit_id',
                                'placeholder' => __('Select Unit'),
                            ])); ?>

                        </div>
                        <?php if(auth()->user()->type != 'maintainer'): ?>
                            <div class="col-lg-2 col-md-6">
                                <?php echo Form::select('maintainer', $maintainers, request('maintainer'), [
                                    'class' => 'form-control select2 maintainer',
                                ]); ?>

                            </div>
                        <?php endif; ?>

                        <div class="col-lg-2 col-md-6">
                            <?php echo e(Form::text('date', request('date'), [
                                'class' => 'form-control  date',
                                'placeholder' => __('Select request date range'),
                                'id' => 'demo',
                                'autocomplete' => 'off',
                            ])); ?>

                        </div>

                        <div class="col-lg-1 col-md-6">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary subbtn" id="btn_search"
                                    data-bs-toggle="tooltip" title="<?php echo e(__('Search')); ?>">
                                    <i class="ti ti-search"></i>
                                </button>
                                <button type="button" class="btn btn-primary" id="btn_reset" data-bs-toggle="tooltip"
                                    title="<?php echo e(__('Reset')); ?>" onclick="window.location.reload();">
                                    <i class="ti ti-refresh"></i>
                                </button>
                            </div>
                        </div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create maintenance request')): ?>
                            <div class="col-lg-auto col-md-12 ms-lg-auto">
                                <a href="#" class="btn btn-secondary  w-100 w-lg-auto  customModal" data-size="lg"
                                    data-url="<?php echo e(route('maintenance-request.create')); ?>"
                                    data-title="<?php echo e(__('Create Maintenance Request')); ?>"> <i
                                        class="ti ti-circle-plus align-text-bottom"></i>
                                    <?php echo e(__('Create Maintenance Request')); ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body pt-0 filter-section">

                </div>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('script-page'); ?>
    <script>
        $('#demo').daterangepicker({
            autoApply: true,
            autoUpdateInput: false,
            locale: {
                format: 'MM/DD/YYYY'
            }
        }, function(start, end) {
            $('#demo').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
        });
        $(document).ready(function() {
            $('.subbtn').trigger('click');
        });

        $(document).on('click', '.subbtn', function() {
            $.ajax({
                type: 'GET',
                url: window.location.href,
                data: {
                    property: $('.property').val(),
                    unit: $('.unit').val(),
                    maintainer: $('.maintainer').val(),
                    date: $('#demo').val()
                },
                success: function(response) {
                    $('.filter-section').html(response.html);
                    feather.replace();
                    datatable();
                    $('[data-bs-toggle="tooltip"]').tooltip();
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/maintenance_request/type.blade.php ENDPATH**/ ?>