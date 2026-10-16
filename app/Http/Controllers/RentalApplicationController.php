<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Lead;
use App\Models\Notification;
use App\Models\PropertyUnit;
use App\Models\RentalApplication;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RentalApplicationController extends Controller
{
    private function findOwnApplication($id)
    {
        return RentalApplication::where('parent_id', parentId())->where('id', $id)->first();
    }

    public function index(Request $request)
    {
        if (!\Auth::user()->can('manage rental application')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $query = RentalApplication::where('parent_id', parentId())->orderBy('id', 'desc');
        if (!empty($request->status) && array_key_exists($request->status, RentalApplication::statuses())) {
            $query->where('status', $request->status);
        }
        $applications = $query->get();
        $statuses = RentalApplication::statuses();
        return view('rental_application.index', compact('applications', 'statuses'));
    }

    public function show($id)
    {
        $application = $this->findOwnApplication($id);
        if (!\Auth::user()->can('manage rental application') || !$application) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $unitAvailable = !empty($application->units) && $application->units->is_occupied == 0;
        $emailTaken = User::where('email', $application->email)->exists();
        return view('rental_application.show', compact('application', 'unitAvailable', 'emailTaken'));
    }

    public function reject(Request $request, $id)
    {
        $application = $this->findOwnApplication($id);
        if (!\Auth::user()->can('review rental application') || !$application || $application->status != 'pending') {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $application->status = 'rejected';
        $application->review_notes = $request->review_notes;
        $application->reviewed_at = now();
        $application->save();

        if (!empty($application->lead)) {
            $application->lead->status = 'lost';
            $application->lead->touchActivity();
        }

        $settings = settings();
        commonEmailSend($application->email, [
            'module' => 'rental_application_rejected',
            'subject' => __('Your rental application'),
            'message' => '<p>' . __('Dear') . ' ' . e($application->full_name) . ',</p><p>'
                . __('Thank you for your interest. Unfortunately we are unable to accept your application at this time.') . '</p>',
            'logo' => $settings['company_logo'] ?? '',
        ]);

        return redirect()->route('rental-application.index')->with('success', __('Application rejected.'));
    }

    // Approving creates the tenant login, the tenant record and a draft agreement, and occupies the unit.
    public function approve(Request $request, $id)
    {
        $application = $this->findOwnApplication($id);
        if (!\Auth::user()->can('review rental application') || !$application || $application->status != 'pending') {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $validator = \Validator::make($request->all(), [
            'lease_start_date' => 'required|date',
            'lease_end_date' => 'required|date|after_or_equal:lease_start_date',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $unit = PropertyUnit::where('parent_id', parentId())->where('id', $application->unit_id)->first();
        if (empty($unit) || $unit->is_occupied == 1) {
            return redirect()->back()->with('error', __('This property unit is not available at the moment'));
        }
        if (User::where('email', $application->email)->exists()) {
            return redirect()->back()->with('error', __('A user with this email already exists.'));
        }

        if (getSettingsValByIdName(1, 'pricing_feature') == 'on') {
            $owner = User::find(parentId());
            $subscription = Subscription::find($owner->subscription);
            if ($subscription && $subscription->tenant_limit != 0 && $owner->totalTenant() >= $subscription->tenant_limit) {
                return redirect()->back()->with('error', __('Your tenant limit is over, please upgrade your subscription.'));
            }
        }

        $userRole = Role::where('parent_id', parentId())->where('name', 'tenant')->first();
        if (empty($userRole)) {
            return redirect()->back()->with('error', __('Tenant role not found.'));
        }

        $password = Str::random(10);

        \DB::beginTransaction();
        try {
            $user = new User();
            $user->first_name = $application->first_name;
            $user->last_name = $application->last_name ?: '';
            $user->email = $application->email;
            $user->password = \Hash::make($password);
            $user->phone_number = $application->phone;
            $user->type = $userRole->name;
            $user->email_verified_at = now();
            $user->is_active = 1;
            $user->profile = 'avatar.png';
            $user->lang = 'english';
            $user->parent_id = parentId();
            $user->save();
            $user->assignRole($userRole);

            $tenant = new Tenant();
            $tenant->user_id = $user->id;
            $tenant->family_member = $application->family_member;
            $tenant->address = $application->current_address;
            $tenant->property = $application->property_id;
            $tenant->unit = $application->unit_id;
            $tenant->lease_start_date = $request->lease_start_date;
            $tenant->lease_end_date = $request->lease_end_date;
            $tenant->parent_id = parentId();
            $tenant->save();

            $agreement = new Agreement();
            $agreement->agreement_id = (new TenantController())->agreementNumber();
            $agreement->parent_id = parentId();
            $agreement->property_id = $tenant->property;
            $agreement->unit_id = $tenant->unit;
            $agreement->tenant_id = $tenant->id;
            $agreement->agreement_start_date = $tenant->lease_start_date;
            $agreement->agreement_end_date = $tenant->lease_end_date;
            $agreement->terms_condition = getSettingsValByName('terms_condition');
            $agreement->description = getSettingsValByName('agreement_description');
            $agreement->date = now();
            $agreement->status = 'Draft';
            $agreement->save();

            $unit->is_occupied = 1;
            $unit->save();

            // keep the uploaded documents with the new tenant
            foreach (['id_document' => __('ID Proof'), 'income_document' => __('Income Proof')] as $field => $title) {
                if (!empty($application->$field)) {
                    \App\Models\TenantDocument::create([
                        'tenant_id' => $tenant->id,
                        'property_id' => $tenant->property,
                        'document' => $application->$field,
                        'title' => $title,
                        'document_type' => $field == 'id_document' ? 'id_proof' : 'income_proof',
                        'parent_id' => parentId(),
                    ]);
                }
            }

            $application->status = 'approved';
            $application->review_notes = $request->review_notes;
            $application->reviewed_at = now();
            $application->tenant_id = $tenant->id;
            $application->save();

            if (!empty($application->lead)) {
                $application->lead->status = 'won';
                $application->lead->tenant_id = $tenant->id;
                $application->lead->touchActivity();
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', __('Something went wrong: ') . $e->getMessage());
        }

        // same welcome email as a manually created tenant (includes the login password)
        $errorMessage = '';
        $notification = Notification::where('parent_id', parentId())->where('module', 'tenant_create')->first();
        if (!empty($notification) && $notification->enabled_email == 1) {
            $notification->password = $password;
            $response = MessageReplace($notification, $user->id);
            $sent = commonEmailSend($user->email, [
                'subject' => $response['subject'],
                'message' => $response['message'],
                'module' => 'tenant_create',
                'logo' => settings()['company_logo'] ?? '',
            ]);
            if ($sent['status'] == 'error') {
                $errorMessage = '</br>' . $sent['message'];
            }
        }

        return redirect()->route('tenant.show', \Crypt::encrypt($tenant->id))
            ->with('success', __('Application approved and tenant created.') . $errorMessage);
    }
}
