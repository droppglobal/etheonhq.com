<?php
    $settings = settings();
?>
<?php $__env->startSection('tab-title'); ?>
    <?php echo e(__('Reset Password')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startPush('script-page'); ?>
    <?php if($settings['google_recaptcha'] == 'on'): ?>
        <?php echo NoCaptcha::renderJs(); ?>

    <?php endif; ?>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="d-flex justify-content-center">
                    <div class="auth-header">
                        <h2 class=" text-secondary"><b><?php echo e(__('Forgot Password ?')); ?> </b></h2>
                        <p class="f-16 mt-2"><?php echo e(__('Enter your email and well send you a link to reset')); ?></p>
                    </div>
                </div>
            </div>
            <?php if(session('error')): ?>
                <div class="alert alert-danger" role="alert"><?php echo e(session('error')); ?></div>
            <?php endif; ?>
            <?php if(session('success')): ?>
                <div class="alert alert-success" role="alert"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            <?php if(session('status')): ?>
                <div class="alert alert-success" role="alert"><?php echo e(session('status')); ?></div>
            <?php endif; ?>
            <?php echo e(Form::open(['route' => 'password.email', 'method' => 'post', 'id' => 'forgotpasswordForm'])); ?>

            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email"
                    placeholder="<?php echo e(__('Email address')); ?>" />
                <label for="email"><?php echo e(__('Email address')); ?></label>
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-email text-danger" role="alert">
                        <strong><?php echo e($message); ?></strong>
                    </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <?php if($settings['google_recaptcha'] == 'on'): ?>
                <div class="form-group">
                    <label for="email" class="form-label"></label>
                    <?php echo NoCaptcha::display(); ?>

                    <?php $__errorArgs = ['g-recaptcha-response'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="small text-danger" role="alert">
                            <strong><?php echo e($message); ?></strong>
                        </span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

            <?php endif; ?>
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-secondary p-2"><?php echo e(__('Send Reset Link')); ?></button>
            </div>
            <hr />
            <h5 class="d-flex justify-content-center"><?php echo e(__('Back to')); ?> <a class="ms-1 text-secondary"
                    href="<?php echo e(route('login')); ?>"><?php echo e(__('Log In')); ?></a>
            </h5>
            <?php echo e(Form::close()); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/auth/forgot-password.blade.php ENDPATH**/ ?>