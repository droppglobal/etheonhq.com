<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Book a Facility')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item" aria-current="page"><?php echo e(__('Book a Facility')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-lg-7">
            <?php $__empty_1 = true; $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1"><?php echo e($facility->name); ?></h5>
                                <div class="text-muted small"><?php echo e($facility->open_time); ?> - <?php echo e($facility->close_time); ?> &middot; <?php echo e($facility->slot_minutes); ?> <?php echo e(__('min slots')); ?>

                                    <?php if($facility->max_bookings_per_week): ?> &middot; <?php echo e(__('max :n per week', ['n' => $facility->max_bookings_per_week])); ?> <?php endif; ?>
                                    <?php if($facility->requires_approval): ?> &middot; <?php echo e(__('needs approval')); ?> <?php endif; ?>
                                </div>
                                <?php if($facility->description): ?><p class="small mt-2 mb-0"><?php echo nl2br(e($facility->description)); ?></p><?php endif; ?>
                            </div>
                        </div>
                        <?php echo e(Form::open(['route' => ['facility.book', $facility->id], 'method' => 'post', 'class' => 'mt-3 facility-book', 'data-slots' => route('facility.slots', $facility->id)])); ?>

                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small mb-0"><?php echo e(__('Date')); ?></label>
                                <input type="date" name="booking_date" class="form-control form-control-sm fb-date" value="<?php echo e(date('Y-m-d')); ?>" min="<?php echo e(date('Y-m-d')); ?>" max="<?php echo e(date('Y-m-d', strtotime('+60 days'))); ?>" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small mb-0"><?php echo e(__('Time')); ?></label>
                                <div class="fb-slots d-flex flex-wrap gap-1"><span class="text-muted small"><?php echo e(__('Loading...')); ?></span></div>
                                <input type="hidden" name="start_time" class="fb-start" required>
                            </div>
                            <div class="col-md-9"><input type="text" name="notes" class="form-control form-control-sm" maxlength="500" placeholder="<?php echo e(__('Note (optional)')); ?>"></div>
                            <div class="col-md-3"><button type="submit" class="btn btn-secondary btn-sm w-100 fb-submit" disabled><?php echo e(__('Book')); ?></button></div>
                        </div>
                        <?php echo e(Form::close()); ?>

                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="card"><div class="card-body text-muted"><?php echo e(__('There are no facilities to book at your property.')); ?></div></div>
            <?php endif; ?>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><?php echo e(__('My bookings')); ?></h5></div>
                <div class="card-body pt-0">
                    <table class="table mb-0">
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $myBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><div class="fw-semibold"><?php echo e(optional($booking->facility)->name); ?></div>
                                        <small><?php echo e(dateFormat($booking->booking_date)); ?> <?php echo e($booking->start_time); ?>-<?php echo e($booking->end_time); ?></small></td>
                                    <td><span class="badge bg-light-<?php echo e(\App\Models\FacilityBooking::statusColor($booking->status)); ?>"><?php echo e(\App\Models\FacilityBooking::statuses()[$booking->status] ?? $booking->status); ?></span></td>
                                    <td class="text-end">
                                        <?php if(in_array($booking->status, ['pending', 'approved']) && $booking->booking_date >= date('Y-m-d')): ?>
                                            <?php echo e(Form::open(['route' => ['facility.booking.cancel', $booking->id], 'method' => 'post'])); ?>

                                            <button class="btn btn-sm btn-light-danger"><?php echo e(__('Cancel')); ?></button>
                                            <?php echo e(Form::close()); ?>

                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td class="text-muted"><?php echo e(__('No bookings yet.')); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script>
        $('.facility-book').each(function() {
            var $form = $(this), $slots = $form.find('.fb-slots'), $start = $form.find('.fb-start'), $submit = $form.find('.fb-submit');
            function load() {
                $start.val(''); $submit.prop('disabled', true);
                $.get($form.data('slots'), { date: $form.find('.fb-date').val() }, function(res) {
                    $slots.empty();
                    var any = false;
                    $.each(res.slots, function(_, s) {
                        var $b = $('<button type="button" class="btn btn-sm">').text(s.start)
                            .addClass(s.available ? 'btn-light-secondary' : 'btn-light disabled text-decoration-line-through').prop('disabled', !s.available);
                        if (s.available) { any = true; $b.on('click', function() { $slots.find('.btn-secondary').removeClass('btn-secondary').addClass('btn-light-secondary'); $b.removeClass('btn-light-secondary').addClass('btn-secondary'); $start.val(s.start); $submit.prop('disabled', false); }); }
                        $slots.append($b);
                    });
                    if (!any) $slots.append('<span class="text-muted small"><?php echo e(__('No free slots on this day.')); ?></span>');
                });
            }
            $form.find('.fb-date').on('change', load);
            load();
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/facility/tenant.blade.php ENDPATH**/ ?>