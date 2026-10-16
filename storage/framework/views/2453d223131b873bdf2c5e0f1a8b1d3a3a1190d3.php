<?php echo e(Form::open(['url' => 'users', 'method' => 'post'])); ?>

<div class="modal-body">
    <div class="row">
        <?php if(\Auth::user()->type != 'super admin'): ?>
            <div class="form-group col-md-6">
                <?php echo e(Form::label('role', __('Assign Role'), ['class' => 'form-label'])); ?>

                <?php echo Form::select('role', $userRoles, null, ['class' => 'form-control hidesearch', 'required' => 'required']); ?>

            </div>
        <?php endif; ?>
        <?php if(\Auth::user()->type == 'super admin'): ?>
            <div class="form-group col-md-6">
                <?php echo e(Form::label('name', __('Name'), ['class' => 'form-label'])); ?>

                <?php echo e(Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Name'), 'required' => 'required'])); ?>

            </div>
        <?php else: ?>
            <div class="form-group col-md-6">
                <?php echo e(Form::label('first_name', __('First Name'), ['class' => 'form-label'])); ?>

                <?php echo e(Form::text('first_name', null, ['class' => 'form-control', 'placeholder' => __('Enter First Name'), 'required' => 'required'])); ?>

            </div>
            <div class="form-group col-md-6">
                <?php echo e(Form::label('last_name', __('Last Name'), ['class' => 'form-label'])); ?>

                <?php echo e(Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('Enter Name'), 'required' => 'required'])); ?>

            </div>
        <?php endif; ?>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('email', __('User Email'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::text('email', null, ['class' => 'form-control', 'placeholder' => __('Enter user email'), 'required' => 'required'])); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('password', __('User Password'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::password('password', ['class' => 'form-control', 'placeholder' => __('Enter user password'), 'required' => 'required', 'minlength' => '6'])); ?>


        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('phone_number', __('User Phone Number'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::text('phone_number', null, ['class' => 'form-control', 'placeholder' => __('Enter user phone number')])); ?>

            <small class="form-text text-muted">
                <?php echo e(__('Please enter the number with country code. e.g., +91XXXXXXXXXX')); ?>

            </small>
        </div>

    </div>
</div>
<div class="modal-footer">
    <?php echo e(Form::submit(__('Create'), ['class' => 'btn btn-secondary ml-10'])); ?>

</div>
<?php echo e(Form::close()); ?>

<?php /**PATH /home/etheonbe/public_html/resources/views/user/create.blade.php ENDPATH**/ ?>