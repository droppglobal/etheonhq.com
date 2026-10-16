<?php
    $settings = settings();
?>
<?php $__env->startSection('tab-title'); ?>
    <?php echo e(__('Register')); ?>

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
                        <h2 class="text-secondary"><b><?php echo e(__('Sign up')); ?> </b></h2>
                        <p class="f-16 mt-2"><?php echo e(__('Enter your details and create account')); ?></p>
                    </div>
                </div>
            </div>

            <?php echo e(Form::open(['route' => 'register', 'method' => 'post', 'id' => 'register-Form'])); ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger" role="alert"><?php echo e(session('error')); ?></div>
            <?php endif; ?>
            <?php if(session('success')): ?>
                <div class="alert alert-success" role="alert"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="name" name="name"
                    placeholder="<?php echo e(__('Name')); ?>" />
                <label for="name"><?php echo e(__('Name')); ?></label>
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-name text-danger" role="alert">
                        <strong><?php echo e($message); ?></strong>
                    </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
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
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password"
                    placeholder="<?php echo e(__('Password')); ?>" />
                <label for="password"><?php echo e(__('Password')); ?></label>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-password text-danger" role="alert">
                        <strong><?php echo e($message); ?></strong>
                    </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <?php if(!empty(request()->query('free_trial')) && request()->query('subscription_id')): ?>
                <input type="hidden" name="subscription_id" value="<?php echo e(request()->query('subscription_id')); ?>">

                <input type="hidden" name="free_trial" value="<?php echo e(request()->query('free_trial')); ?>">
            <?php endif; ?>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                    placeholder="<?php echo e(__('Password Confirmation')); ?>" />
                <label for="password_confirmation"><?php echo e(__('Password Confirmation')); ?></label>
                <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-password_confirmation text-danger" role="alert">
                        <strong><?php echo e($message); ?></strong>
                    </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="form-check mt-3">
                <input class="form-check-input input-primary" type="checkbox" id="agree" name="agree" required />
                <label class="form-check-label" for="agree">
                    <span class="h5 mb-0">
                        <?php echo e(__('Agree with')); ?>

                        <span><a
                                href="<?php echo e(!empty($menu->slug) ? route('page', $menu->slug) : '#'); ?>"><?php echo e(__('Terms and conditions')); ?></a>.</span>
                    </span>
                </label>
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
                <button type="submit" class="btn btn-secondary p-2"><?php echo e(__('Sign Up')); ?></button>
            </div>
            <hr />
            <h5 class="d-flex justify-content-center"><?php echo e(__('Already have an account?')); ?> <a class="ms-1 text-secondary"
                    href="<?php echo e(route('login')); ?>"><?php echo e(__('Login in here')); ?></a>
            </h5>
            <?php echo e(Form::close()); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/auth/register.blade.php ENDPATH**/ ?>