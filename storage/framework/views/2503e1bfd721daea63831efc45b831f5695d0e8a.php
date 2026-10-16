<?php

    $admin_logo = getSettingsValByName('company_logo');
    $ids = parentId();
    $authUser = \App\Models\User::find($ids);
    $subscription = \App\Models\Subscription::find($authUser->subscription);
    $routeName = \Request::route()->getName();
    $pricing_feature_settings = getSettingsValByIdName(1, 'pricing_feature');

    $theme_mode = getSettingsValByName('theme_mode');
    $light_logo = getSettingsValByName('light_logo');
    if (auth()->user()->type != 'super admin') {
        $light_logo = getSettingsValByName('company_light_logo');
    }
?>
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="#" class="b-brand text-primary">
                <?php if($theme_mode == 'dark'): ?>
                    <img src="<?php echo e(asset(Storage::url('upload/logo/')) . '/' . (isset($light_logo) && !empty($light_logo) ? $light_logo : 'logo.png')); ?>"
                        alt="" class="logo logo-lg" />
                <?php else: ?>
                    <img src="<?php echo e(asset(Storage::url('upload/logo/')) . '/' . (isset($admin_logo) && !empty($admin_logo) ? $admin_logo : 'logo.png')); ?>"
                        alt="" class="logo logo-lg" />
                <?php endif; ?>
            </a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label><?php echo e(__('Home')); ?></label>
                    <i class="ti ti-dashboard"></i>
                </li>
                <li class="pc-item <?php echo e(in_array($routeName, ['dashboard', 'home', '']) ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('dashboard')); ?>" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
                        <span class="pc-mtext"><?php echo e(__('Dashboard')); ?></span>
                    </a>
                </li>
                <?php if(\Auth::user()->type == 'super admin'): ?>
                    <?php if(Gate::check('manage user')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['users.index', 'users.show']) ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('users.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-user-plus"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Customers')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if(Gate::check('manage user') || Gate::check('manage role') || Gate::check('manage logged history')): ?>
                        <li
                            class="pc-item pc-hasmenu <?php echo e(in_array($routeName, ['users.index', 'logged.history', 'role.index', 'role.create', 'role.edit']) ? 'pc-trigger active' : ''); ?>">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-users"></i>
                                </span>
                                <span class="pc-mtext"><?php echo e(__('Staff Management')); ?></span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: <?php echo e(in_array($routeName, ['users.index', 'logged.history', 'role.index', 'role.create', 'role.edit']) ? 'block' : 'none'); ?>">
                                <?php if(Gate::check('manage user')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['users.index']) ? 'active' : ''); ?>">
                                        <a class="pc-link" href="<?php echo e(route('users.index')); ?>"><?php echo e(__('Users')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage role')): ?>
                                    <li
                                        class="pc-item  <?php echo e(in_array($routeName, ['role.index', 'role.create', 'role.edit']) ? 'active' : ''); ?>">
                                        <a class="pc-link" href="<?php echo e(route('role.index')); ?>"><?php echo e(__('Roles')); ?> </a>
                                    </li>
                                <?php endif; ?>
                                <?php if(!empty($subscription) || !empty($subscription->enabled_logged_history)): ?>
                                    <?php if($pricing_feature_settings == 'off' || !empty($subscription) || $subscription->enabled_logged_history == 1): ?>
                                        <?php if(Gate::check('manage logged history')): ?>
                                            <li
                                                class="pc-item  <?php echo e(in_array($routeName, ['logged.history']) ? 'active' : ''); ?>">
                                                <a class="pc-link"
                                                    href="<?php echo e(route('logged.history')); ?>"><?php echo e(__('Logged History')); ?></a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>


                <?php if(Gate::check('manage property') ||
                        Gate::check('manage unit') ||
                        Gate::check('manage tenant') ||
                        Gate::check('manage invoice') ||
                        Gate::check('manage expense') ||
                        Gate::check('manage maintainer') ||
                        Gate::check('manage maintenance request') ||
                        Gate::check('manage contact') ||
                        Gate::check('manage support') ||
                        Gate::check('manage note') ||
                        Gate::check('manage calendar')): ?>
                    <li class="pc-item pc-caption">
                        <label><?php echo e(__('Business Management')); ?></label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>

                    <?php if(Gate::check('manage tenant')): ?>
                        <li
                            class="pc-item <?php echo e(in_array($routeName, ['tenant.index', 'tenant.create', 'tenant.edit', 'tenant.show']) ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('tenant.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-user"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Tenants')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage maintainer')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['maintainer.index','maintainer.show']) ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('maintainer.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-user-check"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Maintainers')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage tenant') || Gate::check('manage property') || Gate::check('manage unit')): ?>
                        <li
                            class="pc-item pc-hasmenu  <?php echo e(in_array($routeName, ['property.index', 'property.create', 'property.edit', 'property.show', 'unit.index', 'unit.show']) ? 'pc-trigger active' : ''); ?>">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-home"></i>
                                </span>
                                <span class="pc-mtext"><?php echo e(__('Real Estate')); ?></span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: <?php echo e(in_array($routeName, ['property.index', 'property.create', 'property.edit', 'property.show', 'unit.index', 'unit.show']) ? 'block' : 'none'); ?>">
                                <?php if(Gate::check('manage property')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['property.index', 'property.create', 'property.edit', 'property.show']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('property.index')); ?>"><?php echo e(__('Properties')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage unit')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['unit.index', 'unit.show']) ? 'active' : ''); ?>">
                                        <a class="pc-link" href="<?php echo e(route('unit.index')); ?>"><?php echo e(__('Units')); ?></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>

                    <?php endif; ?>
                     <?php if(Gate::check('manage maintainer') || Gate::check('manage maintenance request') || Gate::check('manage inspection') || Gate::check('manage maintenance schedule') || Gate::check('manage vendor')): ?>
                        <li
                            class="pc-item pc-hasmenu  <?php echo e(in_array($routeName, ['maintenance-request.index', 'maintenance-request.show', 'maintenance-request.pending', 'maintenance-request.inprogress', 'inspection.index', 'inspection.show', 'inspection.edit', 'maintenance-schedule.index', 'vendor.index', 'vendor.show']) ? 'pc-trigger active' : ''); ?>">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-tool"></i>
                                </span>
                                <span class="pc-mtext"><?php echo e(__('Maintenance')); ?></span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>

                            <ul class="pc-submenu"
                                style="display: <?php echo e(in_array($routeName, ['maintenance-request.index', 'maintenance-request.pending', 'maintenance-request.show', 'maintenance-request.inprogress', 'inspection.index', 'inspection.show', 'inspection.edit', 'maintenance-schedule.index', 'vendor.index', 'vendor.show']) ? 'block' : 'none'); ?>">
                                <?php if(Gate::check('manage maintenance request')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['maintenance-request.index', 'maintenance-request.show']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('maintenance-request.index')); ?>"><?php echo e(__('All Requests')); ?></a>
                                    </li>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['maintenance-request.pending']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('maintenance-request.pending')); ?>"><?php echo e(__('Pending')); ?></a>
                                    </li>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['maintenance-request.inprogress']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('maintenance-request.inprogress')); ?>"><?php echo e(__('In Progress')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage maintenance schedule')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['maintenance-schedule.index']) ? 'active' : ''); ?>">
                                        <a class="pc-link" href="<?php echo e(route('maintenance-schedule.index')); ?>"><?php echo e(__('Preventive Maintenance')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage inspection')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['inspection.index', 'inspection.show', 'inspection.edit']) ? 'active' : ''); ?>">
                                        <a class="pc-link" href="<?php echo e(route('inspection.index')); ?>"><?php echo e(__('Inspections')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage vendor')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['vendor.index', 'vendor.show']) ? 'active' : ''); ?>">
                                        <a class="pc-link" href="<?php echo e(route('vendor.index')); ?>"><?php echo e(__('Vendors')); ?></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>

                    <?php endif; ?>

                    <?php if(Gate::check('manage invoice') || Gate::check('manage expense') || Gate::check('manage meter reading')): ?>
                        <li
                            class="pc-item pc-hasmenu  <?php echo e(in_array($routeName, ['invoice.index', 'invoice.create', 'invoice.edit', 'invoice.show', 'expense.index', 'meter-reading.index']) ? 'pc-trigger  active' : ''); ?>">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-file-invoice"></i>
                                </span>
                                <span class="pc-mtext"><?php echo e(__('Finance')); ?></span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: <?php echo e(in_array($routeName, ['invoice.index', 'invoice.create', 'invoice.edit', 'invoice.show', 'expense.index', 'meter-reading.index']) ? 'block' : 'none'); ?>">
                                <?php if(Gate::check('manage invoice')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['invoice.index', 'invoice.create', 'invoice.edit', 'invoice.show']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('invoice.index')); ?>"><?php echo e(__('Invoices')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage expense')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['expense.index']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('expense.index')); ?>"><?php echo e(__('Expense')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage meter reading')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['meter-reading.index']) ? 'active' : ''); ?>">
                                        <a class="pc-link" href="<?php echo e(route('meter-reading.index')); ?>"><?php echo e(__('Utility Billing')); ?></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage calendar')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['calendar']) ? 'active' : ''); ?>">
                            <a class="pc-link" href="<?php echo e(route('calendar')); ?>">
                                <span class="pc-micon"><i class="ti ti-calendar"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Calendar')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if(Gate::check('manage facility') || Gate::check('manage visitor') || Gate::check('manage parking') || Gate::check('manage poll')
                        || Gate::check('book facility') || Gate::check('create visitor pass') || Gate::check('vote poll')): ?>
                        <?php $communityRoutes = ['facility.index', 'visitor.index', 'parking.index', 'poll.index', 'poll.show']; ?>
                        <li class="pc-item pc-hasmenu <?php echo e(in_array($routeName, $communityRoutes) ? 'pc-trigger active' : ''); ?>">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-building-community"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Community')); ?></span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu" style="display: <?php echo e(in_array($routeName, $communityRoutes) ? 'block' : 'none'); ?>">
                                <?php if(Gate::check('manage facility') || Gate::check('book facility')): ?>
                                    <li class="pc-item <?php echo e($routeName == 'facility.index' ? 'active' : ''); ?>"><a class="pc-link" href="<?php echo e(route('facility.index')); ?>"><?php echo e(\Auth::user()->type == 'tenant' ? __('Book a Facility') : __('Facilities')); ?></a></li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage visitor') || Gate::check('create visitor pass')): ?>
                                    <li class="pc-item <?php echo e($routeName == 'visitor.index' ? 'active' : ''); ?>"><a class="pc-link" href="<?php echo e(route('visitor.index')); ?>"><?php echo e(\Auth::user()->type == 'tenant' ? __('Visitor Passes') : __('Gate / Visitors')); ?></a></li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage parking') || \Auth::user()->type == 'tenant'): ?>
                                    <li class="pc-item <?php echo e($routeName == 'parking.index' ? 'active' : ''); ?>"><a class="pc-link" href="<?php echo e(route('parking.index')); ?>"><?php echo e(\Auth::user()->type == 'tenant' ? __('My Parking') : __('Parking')); ?></a></li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage poll') || Gate::check('vote poll')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['poll.index', 'poll.show']) ? 'active' : ''); ?>"><a class="pc-link" href="<?php echo e(route('poll.index')); ?>"><?php echo e(__('Polls')); ?></a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php if(\Auth::user()->type == 'tenant' && !empty(\App\Support\WebsiteSite::visitors(parentId())['reviews'])): ?>
                        <li class="pc-item <?php echo e($routeName == 'my-review' ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('my-review')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-star"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Write a Review')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(\Auth::user()->type == 'tenant'): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['autopay.index']) ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('autopay.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-credit-card"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Autopay')); ?></span>
                            </a>
                        </li>
                        <li class="pc-item <?php echo e(in_array($routeName, ['assistant.index']) ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('assistant.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-messages"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Assistant')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage assistant chats')): ?>
                        <?php $waitingChats = \App\Models\AssistantMessage::waitingCount(parentId()); ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['assistant-chats.index', 'assistant-chats.show']) ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('assistant-chats.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-messages"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Tenant Chats')); ?></span>
                                <?php if($waitingChats): ?><span class="pc-badge"><?php echo e($waitingChats); ?></span><?php endif; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage landlord')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['landlord.index', 'landlord.show']) ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('landlord.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-building-community"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Landlords')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage lead')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['lead.index', 'lead.show']) ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('lead.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-user-plus"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Leads')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage rental application')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['rental-application.index', 'rental-application.show']) ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('rental-application.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-file-certificate"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Applications')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if(Gate::check('manage agreement')): ?>
                        <li
                            class="pc-item <?php echo e(in_array($routeName, ['agreement.index', 'agreement.show']) ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('agreement.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-notebook"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Agreement')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if(Gate::check('manage income report') ||
                            Gate::check('manage expense report') ||
                            Gate::check('manage profile and loss report') ||
                            Gate::check('manage property unit report') ||
                            Gate::check('manage tenant history report') ||
                            Gate::check('manage maintenance report') || Gate::check('manage ledger report') || Gate::check('manage tax report') ||
                            Gate::check('manage arrears report') || Gate::check('manage rent roll report') || Gate::check('manage payout report')): ?>

                        <li
                            class="pc-item pc-hasmenu  <?php echo e(in_array($routeName, ['report.income', 'report.expense', 'report.profit_loss', 'report.property_unit', 'report.tenant', 'report.maintenance', 'report.ledger', 'report.tax_summary', 'report.arrears', 'report.rent_roll', 'report.payouts']) ? 'pc-trigger  active' : ''); ?>">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-chart-infographic"></i>
                                </span>
                                <span class="pc-mtext"><?php echo e(__('Reports')); ?></span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: <?php echo e(in_array($routeName, ['report.income']) ? 'block' : 'none'); ?>">
                                <?php if(Gate::check('manage income report')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['report.income']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('report.income')); ?>"><?php echo e(__('Income')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage expense report')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['report.expense']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('report.expense')); ?>"><?php echo e(__('Expense')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage profile and loss report')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['report.profit_loss']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('report.profit_loss')); ?>"><?php echo e(__('Profit & Loss')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage property unit report')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['report.property_unit']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('report.property_unit')); ?>"><?php echo e(__('Property Unit')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage tenant history report')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['report.tenant']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('report.tenant')); ?>"><?php echo e(__('Tenant History')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage maintenance report')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['report.maintenance']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('report.maintenance')); ?>"><?php echo e(__('Maintenance')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage ledger report')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['report.ledger']) ? 'active' : ''); ?>">
                                        <a class="pc-link" href="<?php echo e(route('report.ledger')); ?>"><?php echo e(__('Ledger')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage tax report')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['report.tax_summary']) ? 'active' : ''); ?>">
                                        <a class="pc-link" href="<?php echo e(route('report.tax_summary')); ?>"><?php echo e(__('Tax Summary')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage arrears report')): ?>
                                    <li class="pc-item <?php echo e($routeName == 'report.arrears' ? 'active' : ''); ?>"><a class="pc-link" href="<?php echo e(route('report.arrears')); ?>"><?php echo e(__('Arrears Ageing')); ?></a></li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage rent roll report')): ?>
                                    <li class="pc-item <?php echo e($routeName == 'report.rent_roll' ? 'active' : ''); ?>"><a class="pc-link" href="<?php echo e(route('report.rent_roll')); ?>"><?php echo e(__('Rent Roll')); ?></a></li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage payout report')): ?>
                                    <li class="pc-item <?php echo e($routeName == 'report.payouts' ? 'active' : ''); ?>"><a class="pc-link" href="<?php echo e(route('report.payouts')); ?>"><?php echo e(__('Landlord Payouts')); ?></a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                    <?php endif; ?>

                    <?php if(Gate::check('manage contact')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['contact.index']) ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('contact.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-phone-call"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Contact Diary')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage note')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['note.index']) ? 'active' : ''); ?> ">
                            <a href="<?php echo e(route('note.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-notebook"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Notice Board')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                                <?php if(!empty($subscription) || !empty($subscription->enabled_logged_history)): ?>

                    <?php if(\Auth::user()->type != 'super admin' && n8nAllowed(parentId())): ?>
                        <?php if(Gate::check('manage n8n')): ?>
                            <li class="pc-item <?php echo e(in_array($routeName, ['n8n.index']) ? 'active' : ''); ?> ">
                                <a href="<?php echo e(route('n8n.index')); ?>" class="pc-link">
                                    <span class="pc-micon"><i class="ti ti-settings-automation"></i></span>
                                    <span class="pc-mtext"><?php echo e(__('N8N')); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if(Gate::check('manage notification') || Gate::check('manage types')): ?>
                    <li class="pc-item pc-caption">
                        <label><?php echo e(__('System Configuration')); ?></label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>
                    <?php if(Gate::check('manage types')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['type.index']) ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('type.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i data-feather="file-text"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Types')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage amenity')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['amenity.index']) ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('amenity.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i data-feather="grid"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Property Amenity')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage advantage')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['advantage.index']) ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('advantage.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i data-feather="thumbs-up"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Property Advantages')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage notification')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['notification.index']) ? 'active' : ''); ?> ">
                            <a href="<?php echo e(route('notification.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-bell"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Email Notification')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage front home page') || Gate::check('manage blog')): ?>
                        <li
                            class="pc-item pc-hasmenu <?php echo e(in_array($routeName, ['front-home.index', 'website-pages.index', 'website-pages.create', 'website-pages.edit', 'website-reviews.index', 'website-stats', 'additional.index', 'blog.index']) ? 'active' : ''); ?>">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-layout-rows"></i>
                                </span>
                                <span class="pc-mtext"><?php echo e(__('Frontend Manager')); ?></span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: <?php echo e(in_array($routeName, ['front-home.index', 'website-pages.index', 'website-pages.create', 'website-pages.edit', 'website-reviews.index', 'website-stats', 'additional.index', 'blog.index']) ? 'block' : 'none'); ?>">
                                <?php if(Gate::check('manage front home page')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['front-home.index']) ? 'active' : ''); ?> ">
                                        <a href="<?php echo e(route('front-home.index')); ?>"
                                            class="pc-link"><?php echo e(__('Home Page')); ?></a>
                                    </li>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['website-pages.index', 'website-pages.create', 'website-pages.edit']) ? 'active' : ''); ?> ">
                                        <a href="<?php echo e(route('website-pages.index')); ?>"
                                            class="pc-link"><?php echo e(__('Pages')); ?></a>
                                    </li>
                                    <li class="pc-item <?php echo e($routeName == 'website-reviews.index' ? 'active' : ''); ?>">
                                        <a href="<?php echo e(route('website-reviews.index')); ?>" class="pc-link"><?php echo e(__('Reviews')); ?></a>
                                    </li>
                                    <li class="pc-item <?php echo e($routeName == 'website-stats' ? 'active' : ''); ?>">
                                        <a href="<?php echo e(route('website-stats')); ?>" class="pc-link"><?php echo e(__('Website Stats')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage additional')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['additional.index']) ? 'active' : ''); ?> ">
                                        <a href="<?php echo e(route('additional.index')); ?>"
                                            class="pc-link"><?php echo e(__('Additional')); ?></a>
                                    </li>
                                <?php endif; ?>

                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>


                <?php if(Gate::check('manage pricing packages') ||
                        Gate::check('manage pricing transation') ||
                        Gate::check('manage account settings') ||
                        Gate::check('manage password settings') ||
                        Gate::check('manage general settings') ||
                        Gate::check('manage email settings') ||
                        Gate::check('manage payment settings') ||
                        Gate::check('manage company settings') ||
                        Gate::check('manage seo settings') ||
                        Gate::check('manage google recaptcha settings')): ?>
                    <li class="pc-item pc-caption">
                        <label><?php echo e(__('System Settings')); ?></label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>

                    <?php if(Gate::check('manage FAQ') || Gate::check('manage Page')): ?>
                        <li
                            class="pc-item pc-hasmenu <?php echo e(in_array($routeName, ['homepage.index', 'FAQ.index', 'pages.index', 'footerSetting']) ? 'active' : ''); ?>">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-layout-rows"></i>
                                </span>
                                <span class="pc-mtext"><?php echo e(__('CMS')); ?></span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: <?php echo e(in_array($routeName, ['homepage.index', 'FAQ.index', 'pages.index', 'footerSetting']) ? 'block' : 'none'); ?>">
                                <?php if(Gate::check('manage home page')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['homepage.index']) ? 'active' : ''); ?> ">
                                        <a href="<?php echo e(route('homepage.index')); ?>"
                                            class="pc-link"><?php echo e(__('Home Page')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage Page')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['pages.index']) ? 'active' : ''); ?> ">
                                        <a href="<?php echo e(route('pages.index')); ?>"
                                            class="pc-link"><?php echo e(__('Custom Page')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage FAQ')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['FAQ.index']) ? 'active' : ''); ?> ">
                                        <a href="<?php echo e(route('FAQ.index')); ?>" class="pc-link"><?php echo e(__('FAQ')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage footer')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['footerSetting']) ? 'active' : ''); ?> ">
                                        <a href="<?php echo e(route('footerSetting')); ?>"
                                            class="pc-link"><?php echo e(__('Footer')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage auth page')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['authPage.index']) ? 'active' : ''); ?> ">
                                        <a href="<?php echo e(route('authPage.index')); ?>"
                                            class="pc-link"><?php echo e(__('Auth Page')); ?></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php if(Auth::user()->type == 'super admin' || $pricing_feature_settings == 'on'): ?>
                        <?php if(Gate::check('manage pricing packages') || Gate::check('manage pricing transation')): ?>
                            <li
                                class="pc-item pc-hasmenu <?php echo e(in_array($routeName, ['subscriptions.index', 'subscriptions.show', 'subscription.transaction']) ? 'pc-trigger active' : ''); ?>">
                                <a href="#!" class="pc-link">
                                    <span class="pc-micon">
                                        <i class="ti ti-package"></i>
                                    </span>
                                    <span class="pc-mtext"><?php echo e(__('Pricing')); ?></span>
                                    <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                                </a>
                                <ul class="pc-submenu"
                                    style="display: <?php echo e(in_array($routeName, ['subscriptions.index', 'subscriptions.show', 'subscription.transaction']) ? 'block' : 'none'); ?>">
                                    <?php if(Gate::check('manage pricing packages')): ?>
                                        <li
                                            class="pc-item <?php echo e(in_array($routeName, ['subscriptions.index', 'subscriptions.show']) ? 'active' : ''); ?>">
                                            <a class="pc-link"
                                                href="<?php echo e(route('subscriptions.index')); ?>"><?php echo e(__('Packages')); ?></a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if(Gate::check('manage pricing transation')): ?>
                                        <li
                                            class="pc-item <?php echo e(in_array($routeName, ['subscription.transaction']) ? 'active' : ''); ?>">
                                            <a class="pc-link"
                                                href="<?php echo e(route('subscription.transaction')); ?>"><?php echo e(__('Transactions')); ?></a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if(Gate::check('manage coupon') || Gate::check('manage coupon history')): ?>
                        <li
                            class="pc-item pc-hasmenu <?php echo e(in_array($routeName, ['coupons.index', 'coupons.history']) ? 'active' : ''); ?>">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-shopping-cart-discount"></i>
                                </span>
                                <span class="pc-mtext"><?php echo e(__('Coupons')); ?></span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: <?php echo e(in_array($routeName, ['coupons.index', 'coupons.history']) ? 'block' : 'none'); ?>">
                                <?php if(Gate::check('manage coupon')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['coupons.index']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('coupons.index')); ?>"><?php echo e(__('All Coupon')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage coupon history')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['coupons.history']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('coupons.history')); ?>"><?php echo e(__('Coupon History')); ?></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage account settings') ||
                            Gate::check('manage password settings') ||
                            Gate::check('manage general settings') ||
                            Gate::check('manage email settings') ||
                            Gate::check('manage payment settings') ||
                            Gate::check('manage company settings') ||
                            Gate::check('manage seo settings') ||
                            Gate::check('manage google recaptcha settings')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['setting.index']) ? 'active' : ''); ?> ">
                            <a href="<?php echo e(route('setting.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-settings"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Settings')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                <?php endif; ?>
            </ul>
            <div class="w-100 text-center">
                <div class="badge theme-version badge rounded-pill bg-light text-dark f-12"></div>
            </div>
        </div>
    </div>
</nav>
<?php /**PATH /home/etheonbe/public_html/resources/views/admin/menu.blade.php ENDPATH**/ ?>