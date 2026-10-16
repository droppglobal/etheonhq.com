<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Maintainer Details')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a>
    </li>
    <li class="breadcrumb-item" aria-current="page">
        <a href="<?php echo e(route('maintainer.index')); ?>"> <?php echo e(__('Maintainer')); ?></a>
    </li>
    <li class="breadcrumb-item active">
        <a href="#"><?php echo e(__('Details')); ?></a>
    </li>
<?php $__env->stopSection(); ?>

<?php
    $profile = asset(Storage::url('upload/profile/avatar.png'));
?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <div id="basicwizard" class="form-wizard row justify-content-center">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-3">
                             <ul class="nav nav-pills nav-justified">
                                <li class="nav-item">
                                    <a class="nav-link active" id="profile-tab-1" data-bs-toggle="tab" href="#profile-1"
                                        role="tab">
                                        <i class="ph-duotone ph-user"></i>
                                        <?php echo e(__('Personal Information')); ?>

                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="profile-tab-2" data-bs-toggle="tab" href="#profile-2"
                                        role="tab">
                                        <i class="ph-duotone ph-cpu"></i>
                                        <?php echo e(__('Maintenance Requests')); ?>

                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card">

                        <div class="card-body">
                            <div class="tab-content">

                                
                                <div class="tab-pane show active" id="profile-1" role="tabpanel"
                                    aria-labelledby="profile-tab-1">
                                    <div class="row">

                                        
                                        <div class="col-lg-4 col-xxl-3">
                                            <div class="card border">
                                                <div class="card-header">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0">
                                                            <img class="img-radius img-fluid wid-80"
                                                                src="<?php echo e(!empty($maintainer->user->profile) ? fetch_file($maintainer->user->profile, 'upload/profile/') : $profile); ?>"
                                                                alt="User image" />
                                                        </div>
                                                        <div class="flex-grow-1 mx-3">
                                                            <h5 class="mb-1">
                                                                <?php echo e(ucfirst(!empty($maintainer->user) ? $maintainer->user->first_name : '') . ' ' . ucfirst(!empty($maintainer->user) ? $maintainer->user->last_name : '')); ?>

                                                            </h5>
                                                            <?php
                                                                $rated = $requests->where('tenant_rating', '>', 0);
                                                                $completedJobs = $requests->where('status', 'completed')->count();
                                                            ?>
                                                            <div class="small text-muted">
                                                                <?php if($rated->count()): ?>
                                                                    <span class="text-warning">★</span> <?php echo e(number_format($rated->avg('tenant_rating'), 1)); ?>

                                                                    (<?php echo e($rated->count()); ?> <?php echo e(__('ratings')); ?>) &middot;
                                                                <?php endif; ?>
                                                                <?php echo e($completedJobs); ?> <?php echo e(__('jobs completed')); ?>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body px-2 pb-0">
                                                    <div class="list-group list-group-flush">
                                                        <a href="#" class="list-group-item list-group-item-action">
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
                                                        <a href="#" class="list-group-item list-group-item-action">
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-shrink-0">
                                                                    <i
                                                                        class="material-icons-two-tone f-20">phonelink_ring</i>
                                                                </div>
                                                                <div class="flex-grow-1 mx-3">
                                                                    <h5 class="m-0"><?php echo e(__('Phone')); ?></h5>
                                                                </div>
                                                                <div class="flex-shrink-0">
                                                                    <small><?php echo e(!empty($maintainer->user) ? $maintainer->user->phone_number : '-'); ?></small>
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
                                                    <h5><?php echo e(__('Assigned Properties')); ?></h5>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th><?php echo e(__('Property Name')); ?></th>
                                                                    <th><?php echo e(__('Address')); ?></th>
                                                                    <th><?php echo e(__('Type')); ?></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $__empty_1 = true; $__currentLoopData = $maintainer->properties(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                    <tr>
                                                                        <td><?php echo e($property->name ?? '-'); ?></td>
                                                                        <td><?php echo e($property->address ?? '-'); ?></td>
                                                                        <td>
                                                                            <span class="badge bg-light-secondary"
                                                                                data-bs-toggle="tooltip"
                                                                                data-bs-original-title="<?php echo e(__('Type')); ?>">
                                                                                <?php echo e(\App\Models\Property::types()[$property->type] ?? $property->type); ?>

                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                                    <tr>
                                                                        <td colspan="3"
                                                                            class="text-center text-muted py-3">
                                                                            <?php echo e(__('No properties assigned.')); ?>

                                                                        </td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                
                                <div class="tab-pane" id="profile-2" role="tabpanel" aria-labelledby="profile-tab-2">

                                    
                                    <div class="d-flex justify-content-end mb-3 gap-2">
                                        <button class="btn btn-outline-primary btn-sm active" id="btn-list-view"
                                            onclick="switchView('list')">
                                            <i class="ti ti-list me-1"></i> <?php echo e(__('List')); ?>

                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" id="btn-kanban-view"
                                            onclick="switchView('kanban')">
                                            <i class="ti ti-layout-kanban me-1"></i> <?php echo e(__('Kanban')); ?>

                                        </button>
                                    </div>

                                    
                                    <div id="list-view">
                                        <div class="dt-responsive table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo e(__('Property')); ?></th>
                                                        <th><?php echo e(__('Unit')); ?></th>
                                                        <th><?php echo e(__('Issue')); ?></th>
                                                        <th><?php echo e(__('Maintainer')); ?></th>
                                                        <th><?php echo e(__('Request Date')); ?></th>
                                                        <th><?php echo e(__('Status')); ?></th>
                                                        <th><?php echo e(__('Attachment')); ?></th>
                                                        <?php if(Gate::check('show maintenance request')): ?>
                                                            <th class="text-right"><?php echo e(__('Action')); ?></th>
                                                        <?php endif; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                        <tr role="row">
                                                            <td><?php echo e(!empty($request->properties) ? $request->properties->name : '-'); ?>

                                                            </td>
                                                            <td><?php echo e(!empty($request->units) ? $request->units->name : '-'); ?>

                                                            </td>
                                                            <td><?php echo e(!empty($request->types) ? $request->types->title : '-'); ?>

                                                            </td>
                                                            <td><?php echo e(!empty($request->maintainers) ? $request->maintainers->name : '-'); ?>

                                                            </td>
                                                            <td><?php echo e(dateFormat($request->request_date)); ?></td>
                                                            <td>
                                                                <?php if($request->status == 'pending'): ?>
                                                                    <span class="badge bg-light-warning">
                                                                        <?php echo e(\App\Models\MaintenanceRequest::status()[$request->status]); ?>

                                                                    </span>
                                                                <?php elseif($request->status == 'in_progress'): ?>
                                                                    <span class="badge bg-light-info">
                                                                        <?php echo e(\App\Models\MaintenanceRequest::status()[$request->status]); ?>

                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-light-success">
                                                                        <?php echo e(\App\Models\MaintenanceRequest::status()[$request->status]); ?>

                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if(!empty($request->issue_attachment)): ?>
                                                                    <a href="<?php echo e(fetch_file($request->issue_attachment, 'upload/issue_attachment/')); ?>"
                                                                        download>
                                                                        <i class="ti ti-download"></i>
                                                                    </a>
                                                                <?php else: ?>
                                                                    -
                                                                <?php endif; ?>
                                                            </td>
                                                            <?php if(Gate::check('show maintenance request')): ?>
                                                                <td class="text-right">
                                                                    <div class="cart-action">
                                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show maintenance request')): ?>
                                                                            <a class="avtar avtar-xs btn-link-warning text-warning"
                                                                                href="<?php echo e(route('maintenance-request.show', \Crypt::encrypt($request->id))); ?>">
                                                                                <i data-feather="eye"></i>
                                                                            </a>
                                                                        <?php endif; ?>
                                                                        <?php if(\Auth::user()->type == 'maintainer'): ?>
                                                                            <a class="avtar avtar-xs btn-link-secondary text-secondary customModal"
                                                                                data-size="lg" data-bs-toggle="tooltip"
                                                                                data-bs-original-title="<?php echo e(__('Status Update')); ?>"
                                                                                href="#"
                                                                                data-url="<?php echo e(route('maintenance-request.action', $request->id)); ?>"
                                                                                data-title="<?php echo e(__('Maintenance Request Status')); ?>">
                                                                                <i data-feather="check-square"></i>
                                                                            </a>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                        <tr>
                                                            <td colspan="8" class="text-center text-muted py-4">
                                                                <?php echo e(__('No maintenance requests found.')); ?>

                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div id="kanban-view" style="display:none;">
                                        <?php
                                            $statuses = \App\Models\MaintenanceRequest::status();
                                            $statusColors = [
                                                'pending' => '#f0ad4e',
                                                'in_progress' => '#5bc0de',
                                                'completed' => '#5cb85c',
                                            ];
                                        ?>

                                        <div class="pc-kanban-wrapper">
                                            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $columnRequests = $requests->where('status', $key);
                                                ?>

                                                <div class="pc-kanban-column kanban-column">
                                                    <div class="pc-kanban-header">
                                                        <h4>
                                                            <?php echo e($label); ?>

                                                            &nbsp;<span class="countTodo counts"
                                                                id="count-<?php echo e($key); ?>">
                                                                (<?php echo e($columnRequests->count()); ?>)
                                                            </span>
                                                        </h4>
                                                    </div>
                                                    <div class="pc-kanban-body">
                                                        <div class="pc-kanban-cards" id="status-<?php echo e($key); ?>"
                                                            data-status="<?php echo e($key); ?>">
                                                            <?php $__currentLoopData = $columnRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <div class="card border"
                                                                    data-request-id="<?php echo e($request->id); ?>">
                                                                    <div class="card-body px-3 py-3">

                                                                        <h5 class="mb-1 text-truncate">
                                                                            <?php echo e(!empty($request->types) ? $request->types->title : '-'); ?>

                                                                        </h5>

                                                                        <div class="link-secondary text-sm mt-1">
                                                                            <i
                                                                                class="material-icons-two-tone text-secondary f-16 me-1">apartment</i>
                                                                            <span><?php echo e(__('Property')); ?>: </span>
                                                                            <?php echo e(!empty($request->properties) ? $request->properties->name : '-'); ?>

                                                                        </div>

                                                                        <div class="link-secondary text-sm mt-1">
                                                                            <i
                                                                                class="material-icons-two-tone text-secondary f-16 me-1">door_front</i>
                                                                            <span><?php echo e(__('Unit')); ?>: </span>
                                                                            <?php echo e(!empty($request->units) ? $request->units->name : '-'); ?>

                                                                        </div>

                                                                        <div class="link-secondary text-sm mt-1">
                                                                            <i
                                                                                class="material-icons-two-tone text-secondary f-16 me-1">calendar_today</i>
                                                                            <span><?php echo e(__('Date')); ?>: </span>
                                                                            <?php echo e(dateFormat($request->request_date)); ?>

                                                                        </div>

                                                                    </div>
                                                                </div>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
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
<?php $__env->stopSection(); ?>
<?php $__env->startPush('css-page'); ?>
    <style>
        .gu-mirror {
            position: fixed !important;
            margin: 0 !important;
            z-index: 9999 !important;
            opacity: 0.85;
            pointer-events: none;
            min-width: 220px;
            background: var(--bs-body-bg, #fff);
            border: 1px solid rgba(0, 0, 0, .12);
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .15);
            padding: 12px;
        }

        .gu-transit {
            opacity: 0.3;
        }

        .pc-kanban-column {
            overflow: visible !important;
        }

        .pc-kanban-body {
            overflow: visible !important;
            max-height: 600px;
            overflow-y: auto !important;
        }

        #kanban-view .pc-kanban-cards .card {
            touch-action: none;
        }

        body.gu-unselectable {
            overflow: hidden !important;
            touch-action: none;
        }

        .form-wizard .nav-pills .nav-link.active,
        .form-wizard .nav-pills .show>.nav-link {
            background: var(--bs-secondary) !important;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('script-page'); ?>
    <script src="<?php echo e(asset('assets/js/plugins/wizard.min.js')); ?>"></script>
    <script>
        $(document).ready(function() {
            new Wizard('#basicwizard', {
                validate: true,
                progress: true
            });
        });
    </script>
    <script>
        (function() {
            var _orig = EventTarget.prototype.addEventListener;
            EventTarget.prototype.addEventListener = function(type, fn, options) {
                if (type === 'touchstart' || type === 'touchmove' || type === 'touchend') {
                    if (typeof options === 'boolean') {
                        options = {
                            capture: options,
                            passive: false
                        };
                    } else if (options && typeof options === 'object') {
                        options = Object.assign({}, options, {
                            passive: false
                        });
                    } else {
                        options = {
                            passive: false
                        };
                    }
                }
                return _orig.call(this, type, fn, options);
            };
        })();
    </script>

    <script src="<?php echo e(asset('assets/js/plugins/dragula.min.js')); ?>"></script>

    <script>
        var drakeRequests = null;

        function initKanban() {
            if (drakeRequests !== null) return;

            var requestContainers = Array.from(
                document.querySelectorAll('#kanban-view .pc-kanban-cards')
            );

            if (typeof dragula === 'undefined' || !requestContainers.length) return;

            drakeRequests = dragula(requestContainers, {
                mirrorContainer: document.body,
                revertOnSpill: true
            });

            drakeRequests.on('drop', function(el, target, source) {
                if (!target || !target.classList.contains('pc-kanban-cards')) return;

                var requestId = el.getAttribute('data-request-id');
                var newStatus = target.getAttribute('data-status');

                if (!requestId || !newStatus) return;

                var placeholder = target.querySelector('.empty-placeholder');
                if (placeholder) placeholder.remove();

                // Fix count: only direct children, not all nested .card.border
                [source, target].forEach(function(col) {
                    if (!col) return;
                    var count = Array.from(col.children).filter(function(c) {
                        return c.classList.contains('card') && c.classList.contains('border');
                    }).length;
                    var key = col.getAttribute('data-status');
                    var badge = document.getElementById('count-' + key);
                    if (badge) badge.textContent = '(' + count + ')';
                });

                $.ajax({
                    url: '<?php echo e(route('maintainer.request.status.update')); ?>',
                    type: 'POST',
                    data: {
                        requestId: requestId,
                        status: newStatus,
                        "_token": $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        toastrs("Success!", "Status updated successfully.", "success");
                    },
                    error: function() {
                        toastrs("Error!", "Failed to update status.", "error");
                    }
                });
            });
        }

        function switchView(view) {
            if (view === 'list') {
                document.getElementById('list-view').style.display = 'block';
                document.getElementById('kanban-view').style.display = 'none';
                document.getElementById('btn-list-view').classList.add('active');
                document.getElementById('btn-kanban-view').classList.remove('active');
            } else {
                document.getElementById('list-view').style.display = 'none';
                document.getElementById('kanban-view').style.display = 'block';
                document.getElementById('btn-kanban-view').classList.add('active');
                document.getElementById('btn-list-view').classList.remove('active');
                initKanban();
            }
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/maintainer/show.blade.php ENDPATH**/ ?>