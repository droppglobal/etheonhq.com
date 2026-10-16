<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Maintenance Request Details')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a>
    </li>
    
    <li class="breadcrumb-item active">
        <a href="#"><?php echo e(__('Maintenance Request Details')); ?></a>
    </li>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('script-page'); ?>
    <script src=<?php echo e(asset('assets/js/plugins/wizard.min.js')); ?>></script>
    <script>
        new Wizard('#basicwizard', {
            validate: true,
            progress: true
        });
    </script>
<?php $__env->stopPush(); ?>
<?php $__env->startPush('css-page'); ?>
    <style>
        .form-wizard .nav-pills .nav-link.active,
        .form-wizard .nav-pills .show>.nav-link {
            background: var(--bs-secondary) !important;
        }
    </style>
<?php $__env->stopPush(); ?>
<?php
    $profile = asset(Storage::url('upload/profile/avatar.png'));
?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">

            <div id="basicwizard" class="form-wizard row justify-content-center">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-3">
                            <ul class="nav nav-pills nav-justified">
                                <li class="nav-item" data-target-form="#basicDetailForm">
                                    <a href="#basicDetail" data-bs-toggle="tab" data-toggle="tab" class="nav-link active">
                                        <i class="ph-duotone ph-user-circle"></i>
                                        <span class="d-none d-sm-inline"><?php echo e(__('Basic Details')); ?></span>
                                    </a>
                                </li>
                                <li class="nav-item" data-target-form="#productDetailForm">
                                    <a href="#productDetail" data-bs-toggle="tab" data-toggle="tab"
                                        class="nav-link icon-btn">
                                        <i class="ph-duotone ph-circles-three-plus"></i>
                                        <span class="d-none d-sm-inline"><?php echo e(__('Add Comment')); ?></span>
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="tab-content">
                                <div id="bar" class="progress mb-3" style="height: 7px">
                                    <div class="bar progress-bar progress-bar-striped progress-bar-animated bg-secondary">
                                    </div>
                                </div>

                                <div class="tab-pane show active" id="basicDetail">
                                    <div id='basicDetailForm'>
                                        <div class="row">
                                            <div class="col-lg-4 col-xxl-3">
                                                <div class="card border">
                                                    <div class="card-header">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <img class="img-radius img-fluid wid-80"
                                                                    src= "<?php echo e(!empty($maintainer->user->profile) ? fetch_file($maintainer->user->profile, 'upload/profile/') : $profile); ?>"
                                                                    alt="User image" />
                                                            </div>
                                                            <div class="flex-grow-1 mx-3">
                                                                <h4 class="mb-2">
                                                                    <?php echo e(ucfirst(!empty($maintainer->user) ? $maintainer->user->first_name : '') . ' ' . ucfirst(!empty($maintainer->user) ? $maintainer->user->last_name : '')); ?>

                                                                </h4>
                                                                <h5 class="mt-1"><span
                                                                        class="badge bg-light-secondary"><?php echo e(ucfirst(!empty($maintainer->user) ? $maintainer->user->type : '')); ?></span>
                                                                </h5>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <div class="card-body px-2 pb-0">
                                                        <div class="list-group list-group-flush">
                                                            <a href="#"
                                                                class="list-group-item list-group-item-action">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0">
                                                                        <i class="material-icons-two-tone f-20">email</i>
                                                                    </div>
                                                                    <div class="flex-grow-1 mx-3">
                                                                        <h5 class="m-0"><?php echo e(__('Email')); ?></h5>
                                                                    </div>
                                                                    <div class="flex-shrink-0">
                                                                        <small><?php echo e(!empty($maintainer->user) ? $maintainer->user->email : '-'); ?></small>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                            <a href="#"
                                                                class="list-group-item list-group-item-action">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0">
                                                                        <i
                                                                            class="material-icons-two-tone f-20">phonelink_ring</i>
                                                                    </div>
                                                                    <div class="flex-grow-1 mx-3">
                                                                        <h5 class="m-0"><?php echo e(__('Phone')); ?></h5>
                                                                    </div>
                                                                    <div class="flex-shrink-0">
                                                                        <small><?php echo e(!empty($maintainer->user) ? $maintainer->user->phone_number : '-'); ?>

                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </a>


                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-lg-8 col-xxl-9">
                                                <div class="card border">
                                                    <div class="card-header">
                                                        <h5><?php echo e(__('Additional Information')); ?></h5>
                                                    </div>
                                                    <div class="card-body">

                                                        <div class="table-responsive">
                                                            <table class="table table-borderless">
                                                                <tbody>
                                                                    <tr>
                                                                        <td><b class="text-header"><?php echo e(__('Property')); ?></b>
                                                                        </td>
                                                                        <td>:</td>
                                                                        <td><?php echo e(!empty($maintenanceRequest->properties) ? $maintenanceRequest->properties->name : '-'); ?>

                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><b class="text-header"><?php echo e(__('Unit')); ?></b>
                                                                        </td>
                                                                        <td>:</td>
                                                                        <td> <?php echo e(!empty($maintenanceRequest->units) ? $maintenanceRequest->units->name : '-'); ?>

                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><b class="text-header"><?php echo e(__('Issue')); ?></b>
                                                                        </td>
                                                                        <td>:</td>
                                                                        <td><?php echo e(!empty($maintenanceRequest->types) ? $maintenanceRequest->types->title : '-'); ?>

                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td><b
                                                                                class="text-header"><?php echo e(__('Request Date')); ?></b>
                                                                        </td>
                                                                        <td>:</td>
                                                                        <td><?php echo e(dateFormat($maintenanceRequest->request_date)); ?>

                                                                        </td>
                                                                    </tr>
                                                                    <?php if(!empty($maintenanceRequest->fixed_date)): ?>
                                                                        <tr>
                                                                            <td><b
                                                                                    class="text-header"><?php echo e(__('Fixed Date')); ?></b>
                                                                            </td>
                                                                            <td>:</td>
                                                                            <td><?php echo e(dateFormat($maintenanceRequest->fixed_date)); ?>

                                                                            </td>
                                                                        </tr>
                                                                    <?php endif; ?>
                                                                    <?php if($maintenanceRequest->amount != 0): ?>
                                                                        <tr>
                                                                            <td><b
                                                                                    class="text-header"><?php echo e(__('Amount')); ?></b>
                                                                            </td>
                                                                            <td>:</td>
                                                                            <td><?php echo e(priceFormat($maintenanceRequest->amount)); ?>

                                                                            </td>
                                                                        </tr>
                                                                    <?php endif; ?>

                                                                    <tr>
                                                                        <td><b class="text-header"><?php echo e(__('Status')); ?></b>
                                                                        </td>
                                                                        <td>:</td>
                                                                        <td>
                                                                            <?php if($maintenanceRequest->status == 'pending'): ?>
                                                                                <span class="badge bg-light-warning">
                                                                                    <?php echo e(\App\Models\MaintenanceRequest::status()[$maintenanceRequest->status]); ?></span>
                                                                            <?php elseif($maintenanceRequest->status == 'in_progress'): ?>
                                                                                <span class="badge bg-light-info">
                                                                                    <?php echo e(\App\Models\MaintenanceRequest::status()[$maintenanceRequest->status]); ?></span>
                                                                            <?php else: ?>
                                                                                <span class="badge bg-light-primary">
                                                                                    <?php echo e(\App\Models\MaintenanceRequest::status()[$maintenanceRequest->status]); ?></span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>

                                                                    <?php if($maintenanceRequest->preferredVisitText()): ?>
                                                                        <tr>
                                                                            <td><b class="text-header"><?php echo e(__('Preferred visit')); ?></b></td>
                                                                            <td>:</td>
                                                                            <td><?php echo e($maintenanceRequest->preferredVisitText()); ?></td>
                                                                        </tr>
                                                                    <?php endif; ?>
                                                                    <?php if($maintenanceRequest->on_the_way_at || ($maintenanceRequest->maintainer_id == \Auth::id() && in_array($maintenanceRequest->status, ['pending', 'in_progress']))): ?>
                                                                        <tr>
                                                                            <td><b class="text-header"><?php echo e(__('Visit')); ?></b></td>
                                                                            <td>:</td>
                                                                            <td class="d-flex flex-wrap align-items-center gap-2">
                                                                                <?php if($maintenanceRequest->on_the_way_at): ?>
                                                                                    <span class="badge bg-light-info"><i class="ti ti-truck"></i> <?php echo e(__('On the way since')); ?> <?php echo e(dateFormat($maintenanceRequest->on_the_way_at)); ?> <?php echo e(\Carbon\Carbon::parse($maintenanceRequest->on_the_way_at)->format('H:i')); ?></span>
                                                                                <?php endif; ?>
                                                                                <?php if($maintenanceRequest->maintainer_id == \Auth::id() && in_array($maintenanceRequest->status, ['pending', 'in_progress'])): ?>
                                                                                    <?php echo e(Form::open(['route' => ['maintenance-request.on-the-way', $maintenanceRequest->id], 'method' => 'post', 'class' => 'd-inline'])); ?>

                                                                                    <button class="btn btn-sm btn-secondary"><i class="ti ti-truck"></i> <?php echo e($maintenanceRequest->on_the_way_at ? __('Tell tenant again') : __("I'm on my way")); ?></button>
                                                                                    <?php echo e(Form::close()); ?>

                                                                                <?php endif; ?>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endif; ?>

                                                                    <?php if(!empty($maintenanceRequest->invoice)): ?>
                                                                        <tr>
                                                                            <td><b
                                                                                    class="text-header"><?php echo e(__('Invoice')); ?></b>
                                                                            </td>
                                                                            <td>:</td>
                                                                            <td><a href="<?php echo e(!empty($maintenanceRequest->invoice) ? fetch_file($maintenanceRequest->invoice, 'upload/invoice/') : '#'); ?>"
                                                                                    download="download"><i
                                                                                        class="fa fa-download"></i></a></td>
                                                                        </tr>
                                                                    <?php endif; ?>

                                                                    <tr>
                                                                        <td><b
                                                                                class="text-header"><?php echo e(__('Attachment')); ?></b>
                                                                        </td>
                                                                        <td>:</td>
                                                                        <td>
                                                                            <?php if(!empty($maintenanceRequest->issue_attachment)): ?>
                                                                                <a href="<?php echo e(!empty($maintenanceRequest->issue_attachment) ? fetch_file($maintenanceRequest->issue_attachment, 'upload/issue_attachment/') : '#'); ?>"
                                                                                    download="download"><i
                                                                                        class="fa fa-download"></i></a>
                                                                            <?php else: ?>
                                                                                -
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>

                                                                    <?php if(!empty($maintenanceRequest->ai_priority)): ?>
                                                                        <tr>
                                                                            <td><b class="text-header"><?php echo e(__('Priority')); ?></b></td>
                                                                            <td>:</td>
                                                                            <td><span class="badge bg-light-<?php echo e(\App\Support\MaintenanceTriage::color($maintenanceRequest->ai_priority)); ?>"><?php echo e(\App\Support\MaintenanceTriage::priorities()[$maintenanceRequest->ai_priority] ?? ''); ?></span>
                                                                                <?php echo e($maintenanceRequest->ai_summary); ?>

                                                                                <?php if($maintenanceRequest->source != 'web'): ?><small class="text-muted">(<?php echo e(__('via')); ?> <?php echo e($maintenanceRequest->source); ?>)</small><?php endif; ?></td>
                                                                        </tr>
                                                                    <?php endif; ?>
                                                                    <?php if(!empty($maintenanceRequest->vendor)): ?>
                                                                        <tr>
                                                                            <td><b class="text-header"><?php echo e(__('Outside Vendor')); ?></b></td>
                                                                            <td>:</td>
                                                                            <td><?php echo e($maintenanceRequest->vendor->name); ?> <?php echo e($maintenanceRequest->vendor->phone ? '(' . $maintenanceRequest->vendor->phone . ')' : ''); ?></td>
                                                                        </tr>
                                                                    <?php endif; ?>
                                                                    <?php if($maintenanceRequest->labor_hours > 0): ?>
                                                                        <tr>
                                                                            <td><b class="text-header"><?php echo e(__('Hours Worked')); ?></b></td>
                                                                            <td>:</td>
                                                                            <td><?php echo e((float) $maintenanceRequest->labor_hours); ?></td>
                                                                        </tr>
                                                                    <?php endif; ?>
                                                                    <?php if($maintenanceRequest->tenant_rating > 0): ?>
                                                                        <tr>
                                                                            <td><b class="text-header"><?php echo e(__('Tenant Rating')); ?></b></td>
                                                                            <td>:</td>
                                                                            <td><span class="text-warning"><?php echo e(str_repeat('★', $maintenanceRequest->tenant_rating)); ?></span><span class="text-muted"><?php echo e(str_repeat('★', 5 - $maintenanceRequest->tenant_rating)); ?></span>
                                                                                <?php echo e($maintenanceRequest->tenant_feedback); ?></td>
                                                                        </tr>
                                                                    <?php elseif(\Auth::user()->type == 'tenant' && $maintenanceRequest->status == 'completed'): ?>
                                                                        <tr>
                                                                            <td><b class="text-header"><?php echo e(__('Rate this repair')); ?></b></td>
                                                                            <td>:</td>
                                                                            <td>
                                                                                <?php echo e(Form::open(['route' => ['maintenance-request.rate', $maintenanceRequest->id], 'method' => 'post', 'class' => 'd-flex flex-wrap gap-2 align-items-center'])); ?>

                                                                                <?php echo e(Form::select('tenant_rating', [5 => '★★★★★', 4 => '★★★★', 3 => '★★★', 2 => '★★', 1 => '★'], 5, ['class' => 'form-control form-control-sm', 'style' => 'width:auto'])); ?>

                                                                                <?php echo e(Form::text('tenant_feedback', null, ['class' => 'form-control form-control-sm', 'style' => 'width:auto', 'placeholder' => __('Comment (optional)')])); ?>

                                                                                <button type="submit" class="btn btn-secondary btn-sm"><?php echo e(__('Submit')); ?></button>
                                                                                <?php echo e(Form::close()); ?>

                                                                            </td>
                                                                        </tr>
                                                                    <?php endif; ?>

                                                                    <tr>
                                                                        <td><b class="text-header"><?php echo e(__('Notes')); ?></b>
                                                                        </td>
                                                                        <td>:</td>
                                                                        <td> <?php echo e($maintenanceRequest->notes); ?></td>
                                                                    </tr>

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                                <div class="tab-pane" id="productDetail">
                                    <div id='productDetailForm'>

                                        <?php if($maintenanceRequest->comments->count() > 0): ?>
                                            <?php $__currentLoopData = $maintenanceRequest->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $user = App\Models\User::find($comment->user_id);
                                                ?>

                                                <div class="bg-light rounded p-2 mb-3 position-relative">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="flex-shrink-0">
                                                            <img class="img-radius img-fluid wid-40"
                                                                src="<?php echo e(!empty($user->profile)
                                                                    ? asset(Storage::url('upload/profile/' . $user->profile))
                                                                    : asset(Storage::url('upload/profile/avatar.png'))); ?>"
                                                                alt="profile" />
                                                        </div>

                                                        <div class="flex-grow-1 mx-3">
                                                            <div class="d-flex align-items-center">
                                                                <h5 class="mb-0 me-3"><?php echo e($user->name ?? '-'); ?>

                                                                </h5>
                                                                <span
                                                                    class="text-body text-opacity-50 d-flex align-items-center">
                                                                    <i class="fas fa-circle f-8 me-2"></i>
                                                                    <?php echo e(dateFormat($comment->created_at)); ?>

                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <p class="text-header"><?php echo e($comment->comment); ?></p>

                                                    <?php if(\Auth::user()->id === $comment->user_id || \Auth::user()->type === 'owner'): ?>
                                                        <div class="position-absolute top-0 end-0 m-2">
                                                            <?php echo Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['maintenance-request.comment.destroy', $comment->id],
                                                                'id' => 'delete-comment-' . $comment->id,
                                                            ]); ?>

                                                            <button type="button"
                                                                class="btn btn-sm btn-light text-danger border-0 p-1 confirm_dialog"
                                                                data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                            <?php echo Form::close(); ?>

                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php endif; ?>


                                        <?php echo e(Form::open(['route' => ['maintenance-request.comment', $maintenanceRequest->id], 'method' => 'post', 'enctype' => 'multipart/form-data'])); ?>

                                        <div class="d-flex align-items-center mt-3">
                                            <div class="flex-shrink-0">
                                                <img class="img-radius d-none d-sm-inline-flex me-3 img-fluid wid-35"
                                                    src="<?php echo e(!empty($maintainer->user) && !empty($maintainer->user->profile)
                                                        ? asset(Storage::url('upload/profile/' . $maintainer->user->profile))
                                                        : asset('assets/images/avatar.png')); ?>"
                                                    alt="<?php echo e($maintainer->user->first_name ?? 'User'); ?>" />
                                            </div>
                                            <div class="flex-grow-1 me-3">
                                                <textarea class="form-control" rows="1" name="comment" placeholder="<?php echo e(__('Write a comment...')); ?>"></textarea>

                                            </div>

                                            <div class="flex-shrink-0">
                                                <button type="submit"
                                                    class="btn btn-secondary"><?php echo e(__('Send')); ?></button>
                                            </div>
                                        </div>
                                        <?php echo e(Form::close()); ?>


                                    </div>
                                </div>

                                <div class="d-flex wizard justify-content-between flex-wrap gap-2 mt-3">
                                    <div class="first">
                                        <a href="#" class="btn btn-secondary"><?php echo e(__('First')); ?></a>
                                    </div>
                                    <div class="d-flex">
                                        <div class="previous me-2">
                                            <a href="#" class="btn btn-secondary"><?php echo e(__('Back To Previous')); ?></a>
                                        </div>
                                        <div class="next">
                                            <a href="#" class="btn btn-secondary"><?php echo e(__('Next Step')); ?></a>
                                        </div>
                                    </div>
                                    <div class="last">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
    <?php
        $isImage = fn($file) => !empty($file) && preg_match('/\.(jpe?g|png|gif|webp)$/i', $file);
        $beforeImg = $isImage($maintenanceRequest->issue_attachment) ? fetch_file($maintenanceRequest->issue_attachment, 'upload/issue_attachment/') : null;
        $afterImg = $isImage($maintenanceRequest->after_photo) ? fetch_file($maintenanceRequest->after_photo, 'upload/issue_attachment/') : null;
    ?>
    <?php if($beforeImg || $afterImg): ?>
        <div class="row">
            <div class="col-xl-6 col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?php echo e($beforeImg && $afterImg ? __('Before & After') : ($afterImg ? __('After the repair') : __('Reported problem'))); ?></h5>
                        <?php if($beforeImg && $afterImg): ?><small class="text-muted"><?php echo e(__('Drag the slider to compare')); ?></small><?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if($beforeImg && $afterImg): ?>
                            <div class="ba-compare" id="ba-compare">
                                <img src="<?php echo e($afterImg); ?>" alt="<?php echo e(__('After')); ?>">
                                <img src="<?php echo e($beforeImg); ?>" alt="<?php echo e(__('Before')); ?>" class="ba-before">
                                <span class="ba-label ba-label-before"><?php echo e(__('Before')); ?></span>
                                <span class="ba-label ba-label-after"><?php echo e(__('After')); ?></span>
                                <div class="ba-handle"></div>
                                <input type="range" min="0" max="100" value="50" aria-label="<?php echo e(__('Compare before and after')); ?>">
                            </div>
                        <?php else: ?>
                            <a href="<?php echo e($beforeImg ?: $afterImg); ?>" target="_blank"><img src="<?php echo e($beforeImg ?: $afterImg); ?>" class="img-fluid rounded" style="max-height:420px" alt=""></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css-page'); ?>
    <style>
        .ba-compare { position: relative; overflow: hidden; border-radius: 10px; aspect-ratio: 4 / 3; background: #f1f3f6; user-select: none; }
        .ba-compare img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .ba-compare .ba-before { clip-path: inset(0 50% 0 0); }
        .ba-compare .ba-handle { position: absolute; top: 0; bottom: 0; left: 50%; width: 3px; background: #fff; box-shadow: 0 0 6px rgba(0,0,0,.4); pointer-events: none; }
        .ba-compare input[type=range] { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: ew-resize; margin: 0; }
        .ba-label { position: absolute; top: 10px; padding: 2px 10px; border-radius: 12px; background: rgba(0,0,0,.55); color: #fff; font-size: 12px; pointer-events: none; }
        .ba-label-before { left: 10px; } .ba-label-after { right: 10px; }
    </style>
<?php $__env->stopPush(); ?>
<?php $__env->startPush('script-page'); ?>
    <script>
        (function() {
            var box = document.getElementById('ba-compare');
            if (!box) return;
            var range = box.querySelector('input'), before = box.querySelector('.ba-before'), handle = box.querySelector('.ba-handle');
            range.addEventListener('input', function() {
                before.style.clipPath = 'inset(0 ' + (100 - this.value) + '% 0 0)';
                handle.style.left = this.value + '%';
            });
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/maintenance_request/show.blade.php ENDPATH**/ ?>