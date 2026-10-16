<?php echo e(Form::open(['route' => 'maintenance.assign.store', 'method' => 'post'])); ?>


<div class="modal-body">

    <?php echo e(Form::hidden('request_id', $maintenanceRequest->id)); ?>


    <div class="form-group">
        <?php echo e(Form::label('maintainer_id', __('Maintainer'), ['class' => 'form-label'])); ?>

        <?php echo e(Form::select('maintainer_id', $maintainers, null, ['class' => 'form-control select2', 'required'])); ?>

    </div>
    <div class="form-group">
        <?php echo e(Form::label('status', __('Status'), ['class' => 'form-label'])); ?>

        <?php echo e(Form::select('status', $status, null, ['class' => 'form-control select2', 'required'])); ?>

    </div>

</div>

<div class="modal-footer">
    <button type="submit" class="btn btn-secondary">
        <?php echo e(__('Assign')); ?>

    </button>
</div>

<?php echo e(Form::close()); ?>

<?php /**PATH /home/etheonbe/public_html/resources/views/maintenance_request/maintainer_assign.blade.php ENDPATH**/ ?>