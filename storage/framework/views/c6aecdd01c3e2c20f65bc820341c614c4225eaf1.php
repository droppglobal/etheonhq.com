<?php
    $users = \Auth::user();
    $languages = \App\Models\Custom::languages();
    $userLang = \Auth::user()->lang;
    $profile = asset(Storage::url('upload/profile/avatar.png'));
?>
<style>
    .pc-header .access {
        margin: 0 -2px;
    }
</style>
<header class="pc-header">
    <div class="header-wrapper">
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <li class="pc-h-item header-mobile-collapse">
                    <a href="#" class="pc-head-link head-link-secondary ms-0" id="sidebar-hide">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link head-link-secondary ms-0" id="mobile-collapse">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <?php if(\Auth::user()->type == 'owner'): ?>
                    <li class="dropdown pc-h-item me-3">

                        <a class="pc-head-link access head-link-secondary dropdown-toggle arrow-none me-0"
                            data-bs-toggle="dropdown" href="#" role="button" data-bs-tooltip="tooltip"
                            data-bs-original-title="<?php echo e(__('Quick Access')); ?>" data-bs-placement="bottom"
                            title="<?php echo e(__('Quick Access')); ?>">
                            <i class="ti ti-plus"></i>
                        </a>

                        <div class="dropdown-menu pc-h-dropdown">
                            <a href="<?php echo e(route('property.create')); ?>" class="dropdown-item">
                                <i class="ti ti-building me-2"></i> <?php echo e(__('Add Property')); ?>

                            </a>

                            <a href="<?php echo e(route('tenant.create')); ?>" class="dropdown-item">
                                <i class="ti ti-user me-2"></i> <?php echo e(__('Add Tenant')); ?>

                            </a>

                            <a href="#" class="dropdown-item customModal"
                                data-url="<?php echo e(route('maintainer.create')); ?>" data-title="<?php echo e(__('Create Maintainer')); ?>"
                                data-size="lg">
                                <i class="ti ti-user-check me-2"></i> <?php echo e(__('Add Maintainer')); ?>

                            </a>

                            <a href="#" class="dropdown-item customModal"
                                data-url="<?php echo e(route('unit.direct-create')); ?>" data-title="<?php echo e(__('Create Unit')); ?>"
                                data-size="lg">
                                <i class="ti ti ti-3d-cube-sphere me-2"></i> <?php echo e(__('Add Unit')); ?>

                            </a>

                            <a href="#" class="dropdown-item customModal"
                                data-url="<?php echo e(route('maintenance-request.create')); ?>"
                                data-title="<?php echo e(__('Create Maintenance Request')); ?>" data-size="lg">
                                <i class="ti ti-tool  me-2"></i> <?php echo e(__('Create Maintenance Request')); ?>

                            </a>

                            <a href="<?php echo e(route('invoice.create')); ?>" class="dropdown-item">
                                <i class="ti ti-file-invoice me-2"></i> <?php echo e(__('Create Invoice')); ?>

                            </a>

                            <a href="#" class="dropdown-item customModal"
                                data-url="<?php echo e(route('expense.create')); ?>" data-title="<?php echo e(__('Create Expense')); ?>"
                                data-size="lg">
                                <i class="ti ti-receipt me-2"></i> <?php echo e(__('Create Expense')); ?>

                            </a>

                            <a href="#" class="dropdown-item customModal"
                                data-url="<?php echo e(route('agreement.create')); ?>" data-title="<?php echo e(__('Create Agreement')); ?>"
                                data-size="lg">
                                <i class="ti ti ti-notebook me-2"></i> <?php echo e(__('Create Agreement')); ?>

                            </a>
                            <a href="#" class="dropdown-item customModal"
                                data-url="<?php echo e(route('contact.create')); ?>" data-title="<?php echo e(__('Create Contact')); ?>"
                                data-size="lg">
                                <i class="ti ti-phone me-2"></i> <?php echo e(__('Create Contact')); ?>

                            </a>
                            <a href="#" class="dropdown-item customModal" data-url="<?php echo e(route('note.create')); ?>"
                                data-title="<?php echo e(__('Create Notice Board')); ?>" data-size="lg">
                                <i class="ti ti ti-notebook me-2"></i> <?php echo e(__('Create Notice Board')); ?>

                            </a>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
            <li class="pc-h-item d-none d-md-inline-flex">
                <form class="header-search position-relative" autocomplete="off">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-search icon-search">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>

                    <input type="search" id="globalSearch" class="form-control"
                        placeholder="Search tenant, property...">

                    <!-- Dropdown -->
                    <div id="searchDropdown" class="search-dropdown d-none"></div>
                </form>
            </li>
        </div>
        <!-- [Mobile Media Block end] -->
        <div class="ms-auto">
            <ul class="list-unstyled">
                <?php if(\Auth::user()->type == 'owner'): ?>
                    <li class="dropdown pc-h-item">
                        <a href="#"
                            class="pc-head-link head-link-secondary dropdown-toggle arrow-none me-0 copy_link"
                            data-url="<?php echo e(route('web.page', \Auth::user()->code)); ?>" data-bs-placement="bottom"
                            data-bs-toggle="tooltip" data-bs-original-title="<?php echo e(__('Click to Copy')); ?>">
                            <i class="ti ti-world fs-15"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <li class="dropdown pc-h-item" data-bs-toggle="tooltip"
                    data-bs-original-title="<?php echo e(__('Language')); ?>" data-bs-placement="bottom">
                    <a class="pc-head-link head-link-primary dropdown-toggle arrow-none me-0"
                        data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false"
                        aria-expanded="false">
                        <i class="ti ti-language"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                        <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($language != 'en'): ?>
                                <a href="<?php echo e(route('language.change', $language)); ?>"
                                    class="dropdown-item <?php echo e($userLang == $language ? 'active' : ''); ?>">
                                    <span class="align-middle"><?php echo e(ucfirst($language)); ?></span>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </li>
                <?php if(\Auth::user()->type == 'super admin' || \Auth::user()->type == 'owner'): ?>
                    <li class="dropdown pc-h-item pc-mega-menu" data-bs-toggle="tooltip"
                        data-bs-original-title="<?php echo e(__('Theme Settings')); ?>" data-bs-placement="bottom">
                        <a href="#" class="pc-head-link head-link-secondary dropdown-toggle arrow-none me-0"
                            data-bs-toggle="offcanvas" data-bs-target="#offcanvas_pc_layout">
                            <i class="ti ti-settings"></i>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="dropdown pc-h-item header-user-profile">
                    <a class="pc-head-link head-link-primary dropdown-toggle arrow-none me-0"
                        data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false"
                        aria-expanded="false">

                        <img src="<?php echo e(!empty($users->profile) ? fetch_file($users->profile, 'upload/profile/') : $profile); ?>"
                            alt="user-image" class="user-avtar" />

                        <span>
                            <i class="ti ti-user-check"></i>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                        <div class="dropdown-header">
                            <h4>
                                <?php echo e(__('Good Morning')); ?>,
                                <span class="small text-muted"><?php echo e(\Auth::user()->name); ?></span>
                            </h4>
                            <p class="text-muted"><?php echo e(\Auth::user()->type); ?></p>

                            <div class="profile-notification-scroll position-relative"
                                style="max-height: calc(100vh - 280px)">
                                <hr />

                                
                                <a href="#" id="push-toggle" class="dropdown-item d-none">
                                    <i class="ti ti-bell"></i>
                                    <span id="push-toggle-text"><?php echo e(__('Turn on notifications')); ?></span>
                                </a>
                                <?php if (is_impersonating()) : ?>
                                    <a href="<?php echo e(route('impersonate.leave')); ?>" class="dropdown-item"
                                        data-actions="Account">
                                        <i class="ti ti-transfer-out"></i>
                                        <span><?php echo e(__('Leave')); ?></span>
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo e(route('logout')); ?>" class="dropdown-item"
                                    onclick="event.preventDefault(); document.getElementById('frm-logout').submit();">
                                    <i class="ti ti-logout"></i>
                                    <span><?php echo e(__('Logout')); ?></span>
                                    <form id="frm-logout" action="<?php echo e(route('logout')); ?>" method="POST"
                                        class="d-none">
                                        <?php echo e(csrf_field()); ?>

                                    </form>
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>
<?php /**PATH /home/etheonbe/public_html/resources/views/admin/header.blade.php ENDPATH**/ ?>