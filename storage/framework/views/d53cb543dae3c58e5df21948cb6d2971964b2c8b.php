<?php echo e(Form::open(['url' => 'pages', 'method' => 'post'])); ?>

<div class="modal-body">
    <?php echo $__env->make('ai.writer', ['type' => 'custom_page'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="row">
        <div class="form-group  col-md-12">
            <?php echo e(Form::label('title', __('Title'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Title')])); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('enabled', __('Enabled Page'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::hidden('enabled', 0, ['class' => 'form-check-input'])); ?>

            <div class="form-check form-switch">
                <?php echo e(Form::checkbox('enabled', 1, true, ['class' => 'form-check-input', 'role' => 'switch', 'id' => 'flexSwitchCheckChecked'])); ?>

                <?php echo e(Form::label('', '', ['class' => 'form-check-label'])); ?>

            </div>
        </div>
        <div class="form-group  col-md-12">
            <?php echo e(Form::label('content', __('Content'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::textarea('content', null, ['class' => 'form-control classic-editor'])); ?>

        </div>
    </div>
</div>
<div class="modal-footer">

    <?php echo e(Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded'])); ?>

</div>
<?php echo e(Form::close()); ?>

<?php /**PATH /home/etheonbe/public_html/resources/views/Pages/create.blade.php ENDPATH**/ ?>