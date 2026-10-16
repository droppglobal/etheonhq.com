<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Leads')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item" aria-current="page"><?php echo e(__('Leads')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?php echo e(route('lead.index')); ?>"
                            class="btn btn-sm <?php echo e(empty(request('status')) ? 'btn-secondary' : 'btn-light-secondary'); ?>">
                            <?php echo e(__('All')); ?> <span class="badge bg-white text-dark ms-1"><?php echo e($counts->sum()); ?></span></a>
                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('lead.index', ['status' => $key])); ?>"
                                class="btn btn-sm <?php echo e(request('status') == $key ? 'btn-' . \App\Models\Lead::statusColor($key) : 'btn-light-' . \App\Models\Lead::statusColor($key)); ?>">
                                <?php echo e($label); ?> <span class="badge bg-white text-dark ms-1"><?php echo e($counts[$key] ?? 0); ?></span></a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            <div class="card table-card">
                <div class="card-header">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-4 col-md-6">
                            <form method="get" action="<?php echo e(route('lead.index')); ?>" class="d-flex gap-2">
                                <input type="hidden" name="status" value="<?php echo e(request('status')); ?>">
                                <?php echo Form::select('property', $properties, request('property'), ['class' => 'form-control', 'onchange' => 'this.form.submit()']); ?>

                            </form>
                        </div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create lead')): ?>
                            <div class="col-lg-auto col-md-12 ms-lg-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="<?php echo e(route('lead.create')); ?>" data-title="<?php echo e(__('Add Lead')); ?>">
                                    <i class="ti ti-circle-plus align-text-bottom"></i> <?php echo e(__('Add Lead')); ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Name')); ?></th>
                                    <th><?php echo e(__('Contact')); ?></th>
                                    <th><?php echo e(__('Property')); ?></th>
                                    <th><?php echo e(__('Source')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>
                                    <th><?php echo e(__('Follow-up')); ?></th>
                                    <th><?php echo e(__('Created')); ?></th>
                                    <th class="text-right"><?php echo e(__('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $leads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><a href="<?php echo e(route('lead.show', $lead->id)); ?>" class="fw-semibold"><?php echo e($lead->name); ?></a></td>
                                        <td><?php echo e($lead->email); ?><br><small class="text-muted"><?php echo e($lead->phone); ?></small></td>
                                        <td><?php echo e(optional($lead->properties)->name ?? '-'); ?>

                                            <?php if(!empty($lead->units)): ?>
                                                <br><small class="text-muted"><?php echo e($lead->units->name); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e(\App\Models\Lead::sources()[$lead->source] ?? $lead->source); ?></td>
                                        <td><span class="badge bg-light-<?php echo e(\App\Models\Lead::statusColor($lead->status)); ?>"><?php echo e($statuses[$lead->status] ?? $lead->status); ?></span></td>
                                        <td>
                                            <?php if(!empty($lead->follow_up_date)): ?>
                                                <span class="<?php echo e(\Carbon\Carbon::parse($lead->follow_up_date)->lte(\Carbon\Carbon::today()) && in_array($lead->status, \App\Models\Lead::openStatuses()) ? 'text-danger fw-semibold' : ''); ?>">
                                                    <?php echo e(dateFormat($lead->follow_up_date)); ?></span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e(dateFormat($lead->created_at)); ?></td>
                                        <td class="text-right">
                                            <div class="cart-action">
                                                <?php echo Form::open(['method' => 'DELETE', 'route' => ['lead.destroy', $lead->id]]); ?>

                                                <a class="avtar avtar-xs btn-link-warning text-warning" href="<?php echo e(route('lead.show', $lead->id)); ?>"
                                                    data-bs-toggle="tooltip" data-bs-original-title="<?php echo e(__('View')); ?>"><i data-feather="eye"></i></a>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit lead')): ?>
                                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" href="#" data-size="lg"
                                                        data-url="<?php echo e(route('lead.edit', $lead->id)); ?>" data-title="<?php echo e(__('Edit Lead')); ?>"
                                                        data-bs-toggle="tooltip" data-bs-original-title="<?php echo e(__('Edit')); ?>"><i data-feather="edit"></i></a>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete lead')): ?>
                                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#"
                                                        data-bs-toggle="tooltip" data-bs-original-title="<?php echo e(__('Delete')); ?>"><i data-feather="trash-2"></i></a>
                                                <?php endif; ?>
                                                <?php echo Form::close(); ?>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/lead/index.blade.php ENDPATH**/ ?>