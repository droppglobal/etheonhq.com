<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Property')); ?>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item" aria-current="page"> <?php echo e(__('Property')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row g-3 align-items-end">

                        <div class="col-lg-4 col-md-6">
                            <input type="text" id="filter_search" class="form-control "
                                placeholder="<?php echo e(__('Property name...')); ?>">
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <?php echo e(Form::select('type', $types, request('type'), [
                                'class' => 'form-control hidesearch',
                                'id' => 'filter_type',
                            ])); ?>

                        </div>
                        <div class="col-lg-1 col-md-6">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary" id="btn_search" data-bs-toggle="tooltip"
                                    title="<?php echo e(__('Search')); ?>">
                                    <i class="ti ti-search"></i>
                                </button>
                                <button type="button" class="btn btn-primary" id="btn_reset" data-bs-toggle="tooltip" onclick="window.location.reload();"
                                    title="<?php echo e(__('Reset')); ?>">
                                    <i class="ti ti-refresh"></i>
                                </button>
                            </div>
                        </div>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create property')): ?>
                            <div class="col-lg-auto col-md-12 ms-lg-auto">
                                <a class="btn btn-secondary w-100 w-lg-auto" href="<?php echo e(route('property.create')); ?>">
                                    <i class="ti ti-circle-plus me-1"></i> <?php echo e(__('Create Property')); ?>

                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body" id="property-list-wrapper">
                    <?php echo $__env->make('property.property_list', ['properties' => $properties], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>

            </div>

        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('script-page'); ?>
    <script>
        var baseUrl = "<?php echo e(route('property.index')); ?>";

        function loadPropertys(page) {
            page = page || 1;

            var search = $('#filter_search').val();
            var type = $('#filter_type').val();
            var unit = $('#filter_unit').val();

            $.ajax({
                url: baseUrl,
                type: 'GET',
                data: {
                    page: page,
                    search: search,
                    type: type,
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(data) {
                    $('#property-list-wrapper').html(data.html);
                    if (typeof feather !== 'undefined') feather.replace();
                },
                error: function() {
                    $('#property-list-wrapper').html(`
                <div class="text-center py-5">
                    <i class="ti ti-alert-circle text-danger" style="font-size:48px;"></i>
                    <p class="text-danger mt-2">Something went wrong. Please try again.</p>
                </div>
            `);
                }
            });
        }

        // Pagination
        $(document).on('click', '#property-list-wrapper .pagination a', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            if (!href || href === '#') return;
            var page = new URL(href, window.location.origin).searchParams.get('page') || 1;
            loadPropertys(page);
        });

        // Search
        $('#btn_search').on('click', function() {
            loadPropertys(1);
        });

        // Enter key
        $('#filter_search').on('keydown', function(e) {
            if (e.key === 'Enter') loadPropertys(1);
        });

        // Reset
        $('#btn_reset').on('click', function() {
            $('#filter_search').val('');
            $('#filter_type').val('').trigger('change');
            loadPropertys(1);
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/property/index.blade.php ENDPATH**/ ?>