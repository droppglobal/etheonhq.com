@php

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
@endphp
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="#" class="b-brand text-primary">
                @if ($theme_mode == 'dark')
                    <img src="{{ asset(Storage::url('upload/logo/')) . '/' . (isset($light_logo) && !empty($light_logo) ? $light_logo : 'logo.png') }}"
                        alt="" class="logo logo-lg" />
                @else
                    <img src="{{ asset(Storage::url('upload/logo/')) . '/' . (isset($admin_logo) && !empty($admin_logo) ? $admin_logo : 'logo.png') }}"
                        alt="" class="logo logo-lg" />
                @endif
            </a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label>{{ __('Home') }}</label>
                    <i class="ti ti-dashboard"></i>
                </li>
                <li class="pc-item {{ in_array($routeName, ['dashboard', 'home', '']) ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
                        <span class="pc-mtext">{{ __('Dashboard') }}</span>
                    </a>
                </li>
                @if (\Auth::user()->type == 'super admin')
                    @if (Gate::check('manage user'))
                        <li class="pc-item {{ in_array($routeName, ['users.index', 'users.show']) ? 'active' : '' }}">
                            <a href="{{ route('users.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-user-plus"></i></span>
                                <span class="pc-mtext">{{ __('Customers') }}</span>
                            </a>
                        </li>
                    @endif
                @else
                    @if (Gate::check('manage user') || Gate::check('manage role') || Gate::check('manage logged history'))
                        <li
                            class="pc-item pc-hasmenu {{ in_array($routeName, ['users.index', 'logged.history', 'role.index', 'role.create', 'role.edit']) ? 'pc-trigger active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-users"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Staff Management') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['users.index', 'logged.history', 'role.index', 'role.create', 'role.edit']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage user'))
                                    <li class="pc-item {{ in_array($routeName, ['users.index']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('users.index') }}">{{ __('Users') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage role'))
                                    <li
                                        class="pc-item  {{ in_array($routeName, ['role.index', 'role.create', 'role.edit']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('role.index') }}">{{ __('Roles') }} </a>
                                    </li>
                                @endif
                                @if (!empty($subscription) || !empty($subscription->enabled_logged_history))
                                    @if ($pricing_feature_settings == 'off' || !empty($subscription) || $subscription->enabled_logged_history == 1)
                                        @if (Gate::check('manage logged history'))
                                            <li
                                                class="pc-item  {{ in_array($routeName, ['logged.history']) ? 'active' : '' }}">
                                                <a class="pc-link"
                                                    href="{{ route('logged.history') }}">{{ __('Logged History') }}</a>
                                            </li>
                                        @endif
                                    @endif
                                @endif
                            </ul>
                        </li>
                    @endif
                @endif


                @if (Gate::check('manage property') ||
                        Gate::check('manage unit') ||
                        Gate::check('manage tenant') ||
                        Gate::check('manage invoice') ||
                        Gate::check('manage expense') ||
                        Gate::check('manage maintainer') ||
                        Gate::check('manage maintenance request') ||
                        Gate::check('manage contact') ||
                        Gate::check('manage support') ||
                        Gate::check('manage note') ||
                        Gate::check('manage calendar'))
                    <li class="pc-item pc-caption">
                        <label>{{ __('Business Management') }}</label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>

                    @if (Gate::check('manage tenant'))
                        <li
                            class="pc-item {{ in_array($routeName, ['tenant.index', 'tenant.create', 'tenant.edit', 'tenant.show']) ? 'active' : '' }}">
                            <a href="{{ route('tenant.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-user"></i></span>
                                <span class="pc-mtext">{{ __('Tenants') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage maintainer'))
                        <li class="pc-item {{ in_array($routeName, ['maintainer.index','maintainer.show']) ? 'active' : '' }}">
                            <a href="{{ route('maintainer.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-user-check"></i></span>
                                <span class="pc-mtext">{{ __('Maintainers') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage tenant') || Gate::check('manage property') || Gate::check('manage unit'))
                        <li
                            class="pc-item pc-hasmenu  {{ in_array($routeName, ['property.index', 'property.create', 'property.edit', 'property.show', 'unit.index', 'unit.show']) ? 'pc-trigger active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-home"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Real Estate') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['property.index', 'property.create', 'property.edit', 'property.show', 'unit.index', 'unit.show']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage property'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['property.index', 'property.create', 'property.edit', 'property.show']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('property.index') }}">{{ __('Properties') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage unit'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['unit.index', 'unit.show']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('unit.index') }}">{{ __('Units') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>

                    @endif
                     @if (Gate::check('manage maintainer') || Gate::check('manage maintenance request') || Gate::check('manage inspection') || Gate::check('manage maintenance schedule') || Gate::check('manage vendor'))
                        <li
                            class="pc-item pc-hasmenu  {{ in_array($routeName, ['maintenance-request.index', 'maintenance-request.show', 'maintenance-request.pending', 'maintenance-request.inprogress', 'inspection.index', 'inspection.show', 'inspection.edit', 'maintenance-schedule.index', 'vendor.index', 'vendor.show']) ? 'pc-trigger active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-tool"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Maintenance') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>

                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['maintenance-request.index', 'maintenance-request.pending', 'maintenance-request.show', 'maintenance-request.inprogress', 'inspection.index', 'inspection.show', 'inspection.edit', 'maintenance-schedule.index', 'vendor.index', 'vendor.show']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage maintenance request'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['maintenance-request.index', 'maintenance-request.show']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('maintenance-request.index') }}">{{ __('All Requests') }}</a>
                                    </li>
                                    <li
                                        class="pc-item {{ in_array($routeName, ['maintenance-request.pending']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('maintenance-request.pending') }}">{{ __('Pending') }}</a>
                                    </li>
                                    <li
                                        class="pc-item {{ in_array($routeName, ['maintenance-request.inprogress']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('maintenance-request.inprogress') }}">{{ __('In Progress') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage maintenance schedule'))
                                    <li class="pc-item {{ in_array($routeName, ['maintenance-schedule.index']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('maintenance-schedule.index') }}">{{ __('Preventive Maintenance') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage inspection'))
                                    <li class="pc-item {{ in_array($routeName, ['inspection.index', 'inspection.show', 'inspection.edit']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('inspection.index') }}">{{ __('Inspections') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage vendor'))
                                    <li class="pc-item {{ in_array($routeName, ['vendor.index', 'vendor.show']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('vendor.index') }}">{{ __('Vendors') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>

                    @endif

                    @if (Gate::check('manage invoice') || Gate::check('manage expense') || Gate::check('manage meter reading'))
                        <li
                            class="pc-item pc-hasmenu  {{ in_array($routeName, ['invoice.index', 'invoice.create', 'invoice.edit', 'invoice.show', 'expense.index', 'meter-reading.index']) ? 'pc-trigger  active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-file-invoice"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Finance') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['invoice.index', 'invoice.create', 'invoice.edit', 'invoice.show', 'expense.index', 'meter-reading.index']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage invoice'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['invoice.index', 'invoice.create', 'invoice.edit', 'invoice.show']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('invoice.index') }}">{{ __('Invoices') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage expense'))
                                    <li class="pc-item {{ in_array($routeName, ['expense.index']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('expense.index') }}">{{ __('Expense') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage meter reading'))
                                    <li class="pc-item {{ in_array($routeName, ['meter-reading.index']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('meter-reading.index') }}">{{ __('Utility Billing') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                    @if (Gate::check('manage calendar'))
                        <li class="pc-item {{ in_array($routeName, ['calendar']) ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('calendar') }}">
                                <span class="pc-micon"><i class="ti ti-calendar"></i></span>
                                <span class="pc-mtext">{{ __('Calendar') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (Gate::check('manage facility') || Gate::check('manage visitor') || Gate::check('manage parking') || Gate::check('manage poll')
                        || Gate::check('book facility') || Gate::check('create visitor pass') || Gate::check('vote poll'))
                        @php $communityRoutes = ['facility.index', 'visitor.index', 'parking.index', 'poll.index', 'poll.show']; @endphp
                        <li class="pc-item pc-hasmenu {{ in_array($routeName, $communityRoutes) ? 'pc-trigger active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-building-community"></i></span>
                                <span class="pc-mtext">{{ __('Community') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu" style="display: {{ in_array($routeName, $communityRoutes) ? 'block' : 'none' }}">
                                @if (Gate::check('manage facility') || Gate::check('book facility'))
                                    <li class="pc-item {{ $routeName == 'facility.index' ? 'active' : '' }}"><a class="pc-link" href="{{ route('facility.index') }}">{{ \Auth::user()->type == 'tenant' ? __('Book a Facility') : __('Facilities') }}</a></li>
                                @endif
                                @if (Gate::check('manage visitor') || Gate::check('create visitor pass'))
                                    <li class="pc-item {{ $routeName == 'visitor.index' ? 'active' : '' }}"><a class="pc-link" href="{{ route('visitor.index') }}">{{ \Auth::user()->type == 'tenant' ? __('Visitor Passes') : __('Gate / Visitors') }}</a></li>
                                @endif
                                @if (Gate::check('manage parking') || \Auth::user()->type == 'tenant')
                                    <li class="pc-item {{ $routeName == 'parking.index' ? 'active' : '' }}"><a class="pc-link" href="{{ route('parking.index') }}">{{ \Auth::user()->type == 'tenant' ? __('My Parking') : __('Parking') }}</a></li>
                                @endif
                                @if (Gate::check('manage poll') || Gate::check('vote poll'))
                                    <li class="pc-item {{ in_array($routeName, ['poll.index', 'poll.show']) ? 'active' : '' }}"><a class="pc-link" href="{{ route('poll.index') }}">{{ __('Polls') }}</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif
                    @if (\Auth::user()->type == 'tenant' && !empty(\App\Support\WebsiteSite::visitors(parentId())['reviews']))
                        <li class="pc-item {{ $routeName == 'my-review' ? 'active' : '' }}">
                            <a href="{{ route('my-review') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-star"></i></span>
                                <span class="pc-mtext">{{ __('Write a Review') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (\Auth::user()->type == 'tenant')
                        <li class="pc-item {{ in_array($routeName, ['autopay.index']) ? 'active' : '' }}">
                            <a href="{{ route('autopay.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-credit-card"></i></span>
                                <span class="pc-mtext">{{ __('Autopay') }}</span>
                            </a>
                        </li>
                        <li class="pc-item {{ in_array($routeName, ['assistant.index']) ? 'active' : '' }}">
                            <a href="{{ route('assistant.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-messages"></i></span>
                                <span class="pc-mtext">{{ __('Assistant') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage assistant chats'))
                        @php $waitingChats = \App\Models\AssistantMessage::waitingCount(parentId()); @endphp
                        <li class="pc-item {{ in_array($routeName, ['assistant-chats.index', 'assistant-chats.show']) ? 'active' : '' }}">
                            <a href="{{ route('assistant-chats.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-messages"></i></span>
                                <span class="pc-mtext">{{ __('Tenant Chats') }}</span>
                                @if ($waitingChats)<span class="pc-badge">{{ $waitingChats }}</span>@endif
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage landlord'))
                        <li class="pc-item {{ in_array($routeName, ['landlord.index', 'landlord.show']) ? 'active' : '' }}">
                            <a href="{{ route('landlord.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-building-community"></i></span>
                                <span class="pc-mtext">{{ __('Landlords') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage lead'))
                        <li class="pc-item {{ in_array($routeName, ['lead.index', 'lead.show']) ? 'active' : '' }}">
                            <a href="{{ route('lead.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-user-plus"></i></span>
                                <span class="pc-mtext">{{ __('Leads') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage rental application'))
                        <li class="pc-item {{ in_array($routeName, ['rental-application.index', 'rental-application.show']) ? 'active' : '' }}">
                            <a href="{{ route('rental-application.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-file-certificate"></i></span>
                                <span class="pc-mtext">{{ __('Applications') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (Gate::check('manage agreement'))
                        <li
                            class="pc-item {{ in_array($routeName, ['agreement.index', 'agreement.show']) ? 'active' : '' }}">
                            <a href="{{ route('agreement.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-notebook"></i></span>
                                <span class="pc-mtext">{{ __('Agreement') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (Gate::check('manage income report') ||
                            Gate::check('manage expense report') ||
                            Gate::check('manage profile and loss report') ||
                            Gate::check('manage property unit report') ||
                            Gate::check('manage tenant history report') ||
                            Gate::check('manage maintenance report') || Gate::check('manage ledger report') || Gate::check('manage tax report') ||
                            Gate::check('manage arrears report') || Gate::check('manage rent roll report') || Gate::check('manage payout report'))

                        <li
                            class="pc-item pc-hasmenu  {{ in_array($routeName, ['report.income', 'report.expense', 'report.profit_loss', 'report.property_unit', 'report.tenant', 'report.maintenance', 'report.ledger', 'report.tax_summary', 'report.arrears', 'report.rent_roll', 'report.payouts']) ? 'pc-trigger  active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-chart-infographic"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Reports') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['report.income']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage income report'))
                                    <li class="pc-item {{ in_array($routeName, ['report.income']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('report.income') }}">{{ __('Income') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage expense report'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['report.expense']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('report.expense') }}">{{ __('Expense') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage profile and loss report'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['report.profit_loss']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('report.profit_loss') }}">{{ __('Profit & Loss') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage property unit report'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['report.property_unit']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('report.property_unit') }}">{{ __('Property Unit') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage tenant history report'))
                                    <li class="pc-item {{ in_array($routeName, ['report.tenant']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('report.tenant') }}">{{ __('Tenant History') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage maintenance report'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['report.maintenance']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('report.maintenance') }}">{{ __('Maintenance') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage ledger report'))
                                    <li class="pc-item {{ in_array($routeName, ['report.ledger']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('report.ledger') }}">{{ __('Ledger') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage tax report'))
                                    <li class="pc-item {{ in_array($routeName, ['report.tax_summary']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('report.tax_summary') }}">{{ __('Tax Summary') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage arrears report'))
                                    <li class="pc-item {{ $routeName == 'report.arrears' ? 'active' : '' }}"><a class="pc-link" href="{{ route('report.arrears') }}">{{ __('Arrears Ageing') }}</a></li>
                                @endif
                                @if (Gate::check('manage rent roll report'))
                                    <li class="pc-item {{ $routeName == 'report.rent_roll' ? 'active' : '' }}"><a class="pc-link" href="{{ route('report.rent_roll') }}">{{ __('Rent Roll') }}</a></li>
                                @endif
                                @if (Gate::check('manage payout report'))
                                    <li class="pc-item {{ $routeName == 'report.payouts' ? 'active' : '' }}"><a class="pc-link" href="{{ route('report.payouts') }}">{{ __('Landlord Payouts') }}</a></li>
                                @endif
                            </ul>
                        </li>

                    @endif

                    @if (Gate::check('manage contact'))
                        <li class="pc-item {{ in_array($routeName, ['contact.index']) ? 'active' : '' }}">
                            <a href="{{ route('contact.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-phone-call"></i></span>
                                <span class="pc-mtext">{{ __('Contact Diary') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage note'))
                        <li class="pc-item {{ in_array($routeName, ['note.index']) ? 'active' : '' }} ">
                            <a href="{{ route('note.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-notebook"></i></span>
                                <span class="pc-mtext">{{ __('Notice Board') }}</span>
                            </a>
                        </li>
                    @endif
                                @if (!empty($subscription) || !empty($subscription->enabled_logged_history))

                    @if (\Auth::user()->type != 'super admin' && n8nAllowed(parentId()))
                        @if (Gate::check('manage n8n'))
                            <li class="pc-item {{ in_array($routeName, ['n8n.index']) ? 'active' : '' }} ">
                                <a href="{{ route('n8n.index') }}" class="pc-link">
                                    <span class="pc-micon"><i class="ti ti-settings-automation"></i></span>
                                    <span class="pc-mtext">{{ __('N8N') }}</span>
                                </a>
                            </li>
                        @endif
                    @endif
                    @endif
                @endif

                @if (Gate::check('manage notification') || Gate::check('manage types'))
                    <li class="pc-item pc-caption">
                        <label>{{ __('System Configuration') }}</label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>
                    @if (Gate::check('manage types'))
                        <li class="pc-item {{ in_array($routeName, ['type.index']) ? 'active' : '' }}">
                            <a href="{{ route('type.index') }}" class="pc-link">
                                <span class="pc-micon"><i data-feather="file-text"></i></span>
                                <span class="pc-mtext">{{ __('Types') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage amenity'))
                        <li class="pc-item {{ in_array($routeName, ['amenity.index']) ? 'active' : '' }}">
                            <a href="{{ route('amenity.index') }}" class="pc-link">
                                <span class="pc-micon"><i data-feather="grid"></i></span>
                                <span class="pc-mtext">{{ __('Property Amenity') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage advantage'))
                        <li class="pc-item {{ in_array($routeName, ['advantage.index']) ? 'active' : '' }}">
                            <a href="{{ route('advantage.index') }}" class="pc-link">
                                <span class="pc-micon"><i data-feather="thumbs-up"></i></span>
                                <span class="pc-mtext">{{ __('Property Advantages') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage notification'))
                        <li class="pc-item {{ in_array($routeName, ['notification.index']) ? 'active' : '' }} ">
                            <a href="{{ route('notification.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-bell"></i></span>
                                <span class="pc-mtext">{{ __('Email Notification') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage front home page') || Gate::check('manage blog'))
                        <li
                            class="pc-item pc-hasmenu {{ in_array($routeName, ['front-home.index', 'website-pages.index', 'website-pages.create', 'website-pages.edit', 'website-reviews.index', 'website-stats', 'additional.index', 'blog.index']) ? 'active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-layout-rows"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Frontend Manager') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['front-home.index', 'website-pages.index', 'website-pages.create', 'website-pages.edit', 'website-reviews.index', 'website-stats', 'additional.index', 'blog.index']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage front home page'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['front-home.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('front-home.index') }}"
                                            class="pc-link">{{ __('Home Page') }}</a>
                                    </li>
                                    <li
                                        class="pc-item {{ in_array($routeName, ['website-pages.index', 'website-pages.create', 'website-pages.edit']) ? 'active' : '' }} ">
                                        <a href="{{ route('website-pages.index') }}"
                                            class="pc-link">{{ __('Pages') }}</a>
                                    </li>
                                    <li class="pc-item {{ $routeName == 'website-reviews.index' ? 'active' : '' }}">
                                        <a href="{{ route('website-reviews.index') }}" class="pc-link">{{ __('Reviews') }}</a>
                                    </li>
                                    <li class="pc-item {{ $routeName == 'website-stats' ? 'active' : '' }}">
                                        <a href="{{ route('website-stats') }}" class="pc-link">{{ __('Website Stats') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage additional'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['additional.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('additional.index') }}"
                                            class="pc-link">{{ __('Additional') }}</a>
                                    </li>
                                @endif

                            </ul>
                        </li>
                    @endif
                @endif


                @if (Gate::check('manage pricing packages') ||
                        Gate::check('manage pricing transation') ||
                        Gate::check('manage account settings') ||
                        Gate::check('manage password settings') ||
                        Gate::check('manage general settings') ||
                        Gate::check('manage email settings') ||
                        Gate::check('manage payment settings') ||
                        Gate::check('manage company settings') ||
                        Gate::check('manage seo settings') ||
                        Gate::check('manage google recaptcha settings'))
                    <li class="pc-item pc-caption">
                        <label>{{ __('System Settings') }}</label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>

                    @if (Gate::check('manage FAQ') || Gate::check('manage Page'))
                        <li
                            class="pc-item pc-hasmenu {{ in_array($routeName, ['homepage.index', 'FAQ.index', 'pages.index', 'footerSetting']) ? 'active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-layout-rows"></i>
                                </span>
                                <span class="pc-mtext">{{ __('CMS') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['homepage.index', 'FAQ.index', 'pages.index', 'footerSetting']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage home page'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['homepage.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('homepage.index') }}"
                                            class="pc-link">{{ __('Home Page') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage Page'))
                                    <li class="pc-item {{ in_array($routeName, ['pages.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('pages.index') }}"
                                            class="pc-link">{{ __('Custom Page') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage FAQ'))
                                    <li class="pc-item {{ in_array($routeName, ['FAQ.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('FAQ.index') }}" class="pc-link">{{ __('FAQ') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage footer'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['footerSetting']) ? 'active' : '' }} ">
                                        <a href="{{ route('footerSetting') }}"
                                            class="pc-link">{{ __('Footer') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage auth page'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['authPage.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('authPage.index') }}"
                                            class="pc-link">{{ __('Auth Page') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                    @if (Auth::user()->type == 'super admin' || $pricing_feature_settings == 'on')
                        @if (Gate::check('manage pricing packages') || Gate::check('manage pricing transation'))
                            <li
                                class="pc-item pc-hasmenu {{ in_array($routeName, ['subscriptions.index', 'subscriptions.show', 'subscription.transaction']) ? 'pc-trigger active' : '' }}">
                                <a href="#!" class="pc-link">
                                    <span class="pc-micon">
                                        <i class="ti ti-package"></i>
                                    </span>
                                    <span class="pc-mtext">{{ __('Pricing') }}</span>
                                    <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                                </a>
                                <ul class="pc-submenu"
                                    style="display: {{ in_array($routeName, ['subscriptions.index', 'subscriptions.show', 'subscription.transaction']) ? 'block' : 'none' }}">
                                    @if (Gate::check('manage pricing packages'))
                                        <li
                                            class="pc-item {{ in_array($routeName, ['subscriptions.index', 'subscriptions.show']) ? 'active' : '' }}">
                                            <a class="pc-link"
                                                href="{{ route('subscriptions.index') }}">{{ __('Packages') }}</a>
                                        </li>
                                    @endif
                                    @if (Gate::check('manage pricing transation'))
                                        <li
                                            class="pc-item {{ in_array($routeName, ['subscription.transaction']) ? 'active' : '' }}">
                                            <a class="pc-link"
                                                href="{{ route('subscription.transaction') }}">{{ __('Transactions') }}</a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                    @endif
                    @if (Gate::check('manage coupon') || Gate::check('manage coupon history'))
                        <li
                            class="pc-item pc-hasmenu {{ in_array($routeName, ['coupons.index', 'coupons.history']) ? 'active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-shopping-cart-discount"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Coupons') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['coupons.index', 'coupons.history']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage coupon'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['coupons.index']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('coupons.index') }}">{{ __('All Coupon') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage coupon history'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['coupons.history']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('coupons.history') }}">{{ __('Coupon History') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                    @if (Gate::check('manage account settings') ||
                            Gate::check('manage password settings') ||
                            Gate::check('manage general settings') ||
                            Gate::check('manage email settings') ||
                            Gate::check('manage payment settings') ||
                            Gate::check('manage company settings') ||
                            Gate::check('manage seo settings') ||
                            Gate::check('manage google recaptcha settings'))
                        <li class="pc-item {{ in_array($routeName, ['setting.index']) ? 'active' : '' }} ">
                            <a href="{{ route('setting.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-settings"></i></span>
                                <span class="pc-mtext">{{ __('Settings') }}</span>
                            </a>
                        </li>
                    @endif

                @endif
            </ul>
            <div class="w-100 text-center">
                <div class="badge theme-version badge rounded-pill bg-light text-dark f-12"></div>
            </div>
        </div>
    </div>
</nav>
