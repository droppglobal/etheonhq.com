<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TenantController extends Controller
{
    private function findOwnTenant($id)
    {
        return scopeLandlord(Tenant::where('parent_id', parentId())->where('id', $id), 'property')->first();
    }

    private function findOwnUnit($id)
    {
        return scopeLandlord(PropertyUnit::where('parent_id', parentId())->where('id', $id))->first();
    }
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage tenant')) {

            if ($request->ajax()) {
                $startDate = '';
                $endDate = '';
                $dateRange = $request->date;

                if (!empty($dateRange)) {
                    [$start, $end] = explode(' - ', $dateRange);
                    $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($start))->format('Y-m-d');
                    $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($end))->format('Y-m-d');
                }
                $query = scopeLandlord(Tenant::where('parent_id', parentId()), 'property');

                if (!empty($request->search)) {
                    $query->whereHas('user', function ($q) use ($request) {
                        $q->where('first_name', 'like', '%' . $request->search . '%')
                            ->orWhere('last_name', 'like', '%' . $request->search . '%')
                            ->orWhere('phone_number', 'like', '%' . $request->search . '%');
                    });
                }

                if (!empty($request->property)) {
                    $query->where('property', $request->property);
                }

                if ($startDate && $endDate) {
                    $query->whereBetween('lease_end_date', [$startDate, $endDate]);
                }

                if (!empty($request->unit)) {
                    $query->where('unit', $request->unit);
                }

                $tenants = $query->orderBy('id', 'desc')->paginate(12);
                $html = view('tenant.tenant_list', compact('tenants'))->render();

                return response()->json(['html' => $html]);
            }

            $tenants = scopeLandlord(Tenant::where('parent_id', parentId()), 'property')->orderBy('id', 'desc')->paginate(12);
            $property = scopeLandlord(Property::where('parent_id', parentId()), 'id')->pluck('name', 'id');
            $property->prepend(__('Select Property'), '');

            $units = PropertyUnit::where('parent_id', parentId())->pluck('name', 'id');
            $units->prepend(__('Select Unit'), '');

            return view('tenant.index', compact('tenants', 'property', 'units'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create tenant')) {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
            $property->prepend(__('Select Property'), 0);
            return view('tenant.create', compact('property'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create tenant')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required|email|unique:users',
                    'password' => 'required',
                    'phone_number' => 'required',
                    'family_member' => 'required',
                    'country' => 'required',
                    'state' => 'required',
                    'city' => 'required',
                    'zip_code' => 'required',
                    'address' => 'required',
                    'property' => 'required',
                    'unit' => 'required',
                    'lease_start_date' => 'required',
                    'lease_end_date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return response()->json([
                    'status' => 'error',
                    'msg' => $messages->first(),
                ]);
            }

            $unit = $this->findOwnUnit($request->unit);
            if (!$unit || $unit->property_id != $request->property || $unit->is_occupied == 1) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'This property unit is not available at the moment',
                ]);
            }
            $pricing_feature_settings = getSettingsValByIdName(1, 'pricing_feature');
            if ($pricing_feature_settings == 'on') {
                $ids = parentId();
                $authUser = User::find($ids);
                $totalTenant = $authUser->totalTenant();
                $subscription = Subscription::find($authUser->subscription);
                if ($totalTenant >= $subscription->tenant_limit && $subscription->tenant_limit != 0) {
                    return response()->json([
                        'status' => 'error',
                        'msg' => __('Your tenant limit is over, please upgrade your subscription.'),
                        'id' => 0,
                    ]);
                }
            }

            $userRole = Role::where('parent_id', parentId())->where('name', 'tenant')->first();
            $setting = settings();

            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->password = \Hash::make($request->password);
            $user->phone_number = $request->phone_number;
            $user->type = $userRole->name;
            $user->email_verified_at = now();
            $user->is_active = 1;
            $user->profile = 'avatar.png';
            $user->lang = 'english';
            $user->parent_id = parentId();
            $user->save();
            $user->assignRole($userRole);


            if ($request->hasFile('profile')) {
                $uploadResult = handleFileUpload($request->file('profile'), 'upload/profile/');
                if ($uploadResult['flag'] == 0) {
                    return redirect()->back()->with('error', $uploadResult['msg']);
                }
                $user->profile = $uploadResult['filename'];
                $user->save();
            }

            $tenant = new Tenant();
            $tenant->user_id = $user->id;
            $tenant->family_member = $request->family_member;
            $tenant->country = $request->country;
            $tenant->state = $request->state;
            $tenant->city = $request->city;
            $tenant->zip_code = $request->zip_code;
            $tenant->address = $request->address;
            $tenant->property = $request->property;
            $tenant->unit = $request->unit;
            $user->is_active = 1;
            $tenant->lease_start_date = $request->lease_start_date;
            $tenant->lease_end_date = $request->lease_end_date;
            $tenant->parent_id = parentId();
            $tenant->save();

            $agreement = new Agreement();
            $agreement->agreement_id = $this->agreementNumber();
            $agreement->parent_id = parentId();
            $agreement->property_id = $tenant->property;
            $agreement->unit_id = $tenant->unit;
            $agreement->tenant_id = $tenant->id;
            $agreement->terms_condition = getSettingsValByName('terms_condition');
            $agreement->description = getSettingsValByName('agreement_description');
            $agreement->date = now();
            $agreement->status = 'Draft';
            $agreement->save();

            $pro_unit = PropertyUnit::find($tenant->unit);
            $pro_unit->is_occupied = 1;
            $pro_unit->save();


            if ($request->hasFile('tenant_images')) {
                foreach ($request->file('tenant_images') as $image) {
                    $uploadResult = handleFileUpload($image, 'upload/tenant/');

                    if ($uploadResult['flag'] == 1) {
                        TenantDocument::create([
                            'property_id' => $request->property,
                            'tenant_id' => $tenant->id,
                            'document' => $uploadResult['filename'],
                            'parent_id' => parentId(),
                        ]);
                    } else {
                        return redirect()->back()->with('error', $uploadResult['msg']);
                    }
                }
            }

            triggerN8n('create_tenant', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number,
                'password' => $request->password,
                'company_name' => getSettingsValByName('company_name'),
                'company_email' => getSettingsValByName('company_email'),
                'company_phone' => getSettingsValByName('company_phone'),
            ]);

            $module = 'tenant_create';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            $errorMessage = '';
            if (!empty($notification)) {
                $notification->password = $request->password;
                $notification_responce = MessageReplace($notification, $user->id);
                $datas['subject'] = $notification_responce['subject'];
                $datas['message'] = $notification_responce['message'];
                $datas['module'] = $module;
                $datas['logo'] = $setting['company_logo'];
                $to = $user->email;
                if ($notification->enabled_email == 1) {
                    $response = commonEmailSend($to, $datas);
                    if ($response['status'] == 'error') {
                        $errorMessage = $response['message'];
                    }
                }

                if ($notification->enabled_sms == 1) {
                    $twilio_sid = getSettingsValByName('twilio_sid');
                    if (!empty($twilio_sid)) {
                        send_twilio_msg($user->phone_number, $notification_responce['sms_message']);
                    }
                }

                if ($notification->enabled_whatsapp == 1) {
                    $twilio_sid = getSettingsValByName('twilio_sid');
                    if (!empty($twilio_sid)) {
                        $params = [
                            'to' => $user->phone_number,
                            'body' => $notification_responce['sms_message'],
                        ];
                        sendTwilioWhatsappMsg($params);
                    }
                }
            }


            return response()->json([
                'status' => 'success',
                'msg' => __('Tenant successfully created.') . '</br>' . $errorMessage,

            ]);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function show($ids)
    {

        if (\Auth::user()->can('show tenant')) {

            $id = Crypt::decrypt($ids);
            $tenant = $this->findOwnTenant($id);
            if (!$tenant) {
                return redirect()->back()->with('error', __('Tenant not found.'));
            }
            $agreements = Agreement::where('parent_id', parentId())->where('tenant_id', $tenant->id)->orderBy('id', 'desc')->get();
            $invoices = Invoice::where('parent_id', parentId())->where('tenant', $tenant->id)->orderBy('id', 'desc')->get();
            $maintenanceRequests = MaintenanceRequest::where('parent_id', parentId())->where('tenant_id', $tenant->id)->orderBy('id', 'desc')->get();
            return view('tenant.show', compact('tenant', 'invoices', 'agreements', 'maintenanceRequests'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function edit($ids)
    {
        if (\Auth::user()->can('edit tenant')) {
            $id = Crypt::decrypt($ids);
            $tenant = $this->findOwnTenant($id);
            if (!$tenant) {
                return redirect()->back()->with('error', __('Tenant not found.'));
            }
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
            $property->prepend(__('Select Property'), 0);

            $user = User::find($tenant->user_id);
            return view('tenant.edit', compact('property', 'tenant', 'user'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
    public function update(Request $request, Tenant $tenant)
    {
        if (!\Auth::user()->can('edit tenant') || $tenant->parent_id != parentId()) {
            return response()->json([
                'status' => 'error',
                'msg' => __('Permission Denied!'),
            ]);
        }

        $validator = \Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $tenant->user_id,
            'phone_number' => 'required',
            'family_member' => 'required',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'zip_code' => 'required',
            'address' => 'required',
            'property' => 'required',
            'unit' => 'required',
            'lease_start_date' => 'required',
            'lease_end_date' => 'required',
            'tenant_images.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'msg' => $validator->getMessageBag()->first(),
            ]);
        }

        $newUnit = $this->findOwnUnit($request->unit);
        if (!$newUnit || $newUnit->property_id != $request->property) {
            return response()->json([
                'status' => 'error',
                'msg' => __('Invalid property unit selected.'),
            ]);
        }
        if ($tenant->unit != $request->unit && $newUnit->is_occupied == 1) {
            return response()->json([
                'status' => 'error',
                'msg' => __('This property unit is not available at the moment'),
            ]);
        }

        try {
            \DB::beginTransaction();

            $user = User::find($tenant->user_id);
             $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
            ]);

            // Profile upload
            if ($request->hasFile('profile')) {
                $uploadResult = handleFileUpload($request->file('profile'), 'upload/profile/');

                if ($uploadResult['flag'] == 0) {
                    \DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'msg' => $uploadResult['msg'],
                    ]);
                }

                // Delete old profile only after new upload succeeds
                if (!empty($user->profile) && $user->profile !== 'avatar.png') {
                    deleteOldFile($user->profile, 'upload/profile/');
                }

                $user->profile = $uploadResult['filename'];
                $user->save();
            }

            $old_unit_id = $tenant->unit;

            $tenant->update([
                'family_member' => $request->family_member,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
                'zip_code' => $request->zip_code,
                'address' => $request->address,
                'property' => $request->property,
                'unit' => $request->unit,
                'lease_start_date' => $request->lease_start_date,
                'lease_end_date' => $request->lease_end_date,
            ]);

            // Free old unit if changed
            if ($old_unit_id != $request->unit) {
                $old_unit = PropertyUnit::find($old_unit_id);
                if ($old_unit) {
                    $old_unit->is_occupied = 0;
                    $old_unit->save();
                }
            }

            // Mark new unit as occupied (unless the tenant has already exited)
            if (empty($tenant->exit_date)) {
                $newUnit->is_occupied = 1;
                $newUnit->save();
            }

            // ===== Tenant Documents Sync =====
            if ($request->input('dropzone_loaded')) {
                $keptExistingIds = array_filter($request->input('existing_images', []));

                TenantDocument::where('tenant_id', $tenant->id)
                    ->get()
                    ->each(function ($doc) use ($keptExistingIds) {
                        if (!in_array($doc->id, $keptExistingIds)) {
                            deleteOldFile($doc->document, 'upload/tenant/');
                            $doc->delete();
                        }
                    });
            }

            if ($request->hasFile('tenant_images')) {
                foreach ($request->file('tenant_images') as $image) {
                    $uploadResult = handleFileUpload($image, 'upload/tenant/');

                    if ($uploadResult['flag'] == 0) {
                        \DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'msg' => $uploadResult['msg'],
                        ]);
                    }

                    TenantDocument::create([
                        'property_id' => $request->property,
                        'tenant_id' => $tenant->id,
                        'document' => $uploadResult['filename'],
                        'parent_id' => parentId(),
                    ]);
                }
            }

            \DB::commit();

            return response()->json([
                'status' => 'success',
                'msg' => __('Tenant successfully updated.'),
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'status' => 'error',
                'msg' => __('Something went wrong: ') . $e->getMessage(),
            ]);
        }
    }

    public function destroy(Tenant $tenant)
    {
        if (\Auth::user()->can('delete tenant') && $tenant->parent_id == parentId()) {

            $documents = TenantDocument::where('tenant_id', $tenant->id)->get();
            foreach ($documents as $doc) {
                if (!empty($doc->document)) {
                    deleteOldFile($doc->document, 'upload/tenant/');
                }
                $doc->delete();
            }

            $user = User::find($tenant->user_id);
            Agreement::where('tenant_id', $tenant->id)->delete();

            // Free the unit if this tenant still occupies it
            if (empty($tenant->exit_date)) {
                $unit = PropertyUnit::find($tenant->unit);
                if ($unit) {
                    $unit->is_occupied = 0;
                    $unit->save();
                }
            }

            if ($user && !empty($user->profile) && $user->profile !== 'avatar.png') {
                deleteOldFile($user->profile, 'upload/profile/');
            }

            if ($user) {
                $user->delete();
            }

            $tenant->delete();

            return redirect()->back()->with('success', 'Tenant successfully deleted.');
        }

        return redirect()->back()->with('error', __('Permission Denied!'));
    }

    public function fileDestroy($id)
    {
        $document = TenantDocument::where('parent_id', parentId())->findOrFail($id);
        if (!\Auth::user()->can('edit tenant')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        // Delete file from storage
        if (!empty($document->document)) {
            deleteOldFile($document->document, 'upload/tenant/');
        }

        $document->delete();

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }


    public function getPropertyUnit($property_id)
    {

        // dd($property_id);
        $units = PropertyUnit::where('parent_id', parentId())->where('property_id', $property_id)
            ->select('id', 'name', 'is_occupied')
            ->get();

        $formatted = $units->mapWithKeys(function ($unit) {
            return [
                $unit->id => [
                    'name' => $unit->name,
                    'is_occupied' => $unit->is_occupied,
                ],
            ];
        });

        return response()->json($formatted);
    }

    public function getUnitDetails($id)
    {
        if (!\Auth::user()->can('create tenant') && !\Auth::user()->can('edit tenant')) {
            return response()->json(['error' => 'Permission Denied'], 403);
        }
        $unit = $this->findOwnUnit($id);

        if (!$unit) {
            return response()->json(['error' => 'Unit not found'], 404);
        }

        return response()->json([
            'name' => ucfirst($unit->name),
            'is_occupied' => $unit->is_occupied,
            'bedroom' => $unit->bedroom,
            'kitchen' => $unit->kitchen,
            'baths' => $unit->baths,
            'rent_type' => $unit->rent_type,
            'rent' => priceFormat($unit->rent),
            'start_date' => $unit->start_date,
            'end_date' => $unit->end_date,
            'payment_due_date' => $unit->payment_due_date,
            'rent_duration' => $unit->rent_duration,
            'deposit_type' => $unit->deposit_type,
            'deposit_amount' => priceFormat($unit->deposit_amount),
            'late_fee_type' => $unit->late_fee_type,
            'late_fee_amount' => priceFormat($unit->late_fee_amount),
            'incident_receipt_amount' => priceFormat($unit->incident_receipt_amount),
            'notes' => $unit->notes,
        ]);
    }

    public function tenantExit($id)
    {
        if (\Auth::user()->can('create tenant')) {


            $tenant = $this->findOwnTenant($id);
            if (!$tenant) {
                return redirect()->back()->with('error', __('Tenant not found.'));
            }

            return view('tenant.exit', compact('tenant'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function tenantExitUpdate(Request $request, $tid)
    {
        if (\Auth::user()->can('create tenant')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'exit_amount' => 'required',
                    'extra_charge' => 'required',
                    'exit_date' => 'required',

                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $tenant = $this->findOwnTenant($tid);
            if (!$tenant) {
                return redirect()->back()->with('error', __('Tenant not found.'));
            }

            $pro_unit = PropertyUnit::find($tenant->unit);
            if ($pro_unit) {
                $pro_unit->is_occupied = 0;
                $pro_unit->save();
            }

            $agreement = Agreement::where('tenant_id', $tenant->id)->where('unit_id', $tenant->unit)->latest()->first();
            if ($agreement) {
                $agreement->status = 'Completed';
                $agreement->save();
            }

            $tenant->exit_amount = $request->exit_amount;
            $tenant->extra_charge = $request->extra_charge;
            $tenant->exit_date = $request->exit_date;
            $tenant->reason = $request->reason;
            $tenant->lease_end_date = $request->exit_date;
            $tenant->save();


            return redirect()->back()->with('success', __('Tenant exit details updated successfully'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function agreementNumber()
    {
        $latestAgreement = Agreement::where('parent_id', parentId())->latest()->first();
        if ($latestAgreement == null) {
            return 1;
        } else {
            return $latestAgreement->agreement_id + 1;
        }
    }

    public function leaseRenew(Request $request)
    {
        $tenant = $this->findOwnTenant($request->tenant_id);
        if (!\Auth::user()->can('manage lease renew') || !$tenant) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
        $property->prepend(__('Select Property'), 0);
        $selectedUnit = $tenant->unit;
        return view('tenant.lease_renew', compact('property', 'selectedUnit', 'tenant'));
    }

    public function leaseRenewStore(Request $request)
    {
        $tenant = $this->findOwnTenant($request->tenant_id);
        if (!\Auth::user()->can('manage lease renew') || !$tenant) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'property' => 'required',
            'unit' => 'required',
            'lease_start_date' => 'required|date',
            'lease_end_date' => 'required|date|after_or_equal:lease_start_date',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        if (
            $tenant->property != $request->property ||
            $tenant->unit != $request->unit
        ) {
            $newUnit = $this->findOwnUnit($request->unit);

            if (!$newUnit || $newUnit->property_id != $request->property || $newUnit->is_occupied == 1) {
                return redirect()->back()->with('error', __('This property unit is not available at the moment'));
            }

            $oldUnit = PropertyUnit::find($tenant->unit);
            if ($oldUnit) {
                $oldUnit->is_occupied = 0;
                $oldUnit->save();
            }

            $newUnit->is_occupied = 1;
            $newUnit->save();
        }
        $oldAgreement = Agreement::where('tenant_id', $tenant->id)
            ->where('status', '!=', 'Completed')
            ->latest()
            ->first();

        if ($oldAgreement) {
            $oldAgreement->status = 'Completed';
            $oldAgreement->save();
        }

        $tenant->property = $request->property;
        $tenant->unit = $request->unit;
        $tenant->lease_start_date = $request->lease_start_date;
        $tenant->lease_end_date = $request->lease_end_date;
        $tenant->save();

        $agreement = new Agreement();
        $agreement->agreement_id = $this->agreementNumber();
        $agreement->agreement_start_date = $tenant->lease_start_date;
        $agreement->agreement_end_date = $tenant->lease_end_date;
        $agreement->parent_id = parentId();
        $agreement->property_id = $tenant->property;
        $agreement->unit_id = $tenant->unit;
        $agreement->tenant_id = $tenant->id;
        $agreement->terms_condition = getSettingsValByName('terms_condition');
        $agreement->description = getSettingsValByName('agreement_description');
        $agreement->date = now();
        $agreement->status = 'Draft';
        $agreement->save();

        return redirect()->back()->with('success', __('Lease Renew Successfully'));
    }

    public function calendar()
    {
        if (\Auth::user()->can('manage calendar')) {
            $eventData = \App\Support\CalendarEvents::forAccount(parentId());
            $types = collect(\App\Support\CalendarEvents::types())->only(collect($eventData)->pluck('type')->unique()->all());
            return view('tenant.calendar', compact('eventData', 'types'));

        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
