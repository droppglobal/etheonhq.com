<?php

namespace App\Http\Controllers;

use App\Models\Comments;
use App\Models\Maintainer;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class MaintenanceRequestController extends Controller
{
    private function applyRoleScope($query, $user)
    {
        if ($user->type == 'maintainer') {
            $query->where('maintainer_id', $user->id);
        } elseif ($user->type == 'tenant') {
            $tenant = $user->tenants;
            $query->where('property_id', !empty($tenant) ? $tenant->property : 0)
                ->where('unit_id', !empty($tenant) ? $tenant->unit : 0);
        } elseif ($user->type == 'landlord') {
            $query->where('parent_id', parentId())->whereIn('property_id', Property::where('landlord_id', $user->id)->pluck('id'));
        } else {
            $query->where('parent_id', parentId());
        }
        return $query;
    }

    // A request the logged-in user may see (assigned maintainer, the tenant of that unit, or the owner's staff).
    private function findVisibleRequest($id)
    {
        return $this->applyRoleScope(MaintenanceRequest::where('id', $id), \Auth::user())->first();
    }

    // Status/amount updates: the assigned maintainer, or staff who can edit requests.
    private function canUpdateStatus($maintenanceRequest)
    {
        $user = \Auth::user();
        if ($user->type == 'maintainer') {
            return $maintenanceRequest->maintainer_id == $user->id;
        }
        return $user->type != 'tenant' && $user->can('edit maintenance request');
    }

    private function isOwnMaintainer($userId, $propertyId = null)
    {
        $query = Maintainer::where('parent_id', parentId())->where('user_id', $userId);
        if (!empty($propertyId)) {
            $query->whereRaw('FIND_IN_SET(?, property_id)', [$propertyId]);
        }
        return $query->exists();
    }

    private function ownVendorId($vendorId)
    {
        return !empty($vendorId) && \App\Models\Vendor::where('parent_id', parentId())->where('id', $vendorId)->exists() ? (int) $vendorId : 0;
    }

    private function applyAjaxFilters($query, $request)
    {
        if (!empty($request->property)) {
            $query->where('property_id', $request->property);
        }

        if (!empty($request->unit)) {
            $query->where('unit_id', $request->unit);
        }

        if (!empty($request->maintainer)) {
            $query->where('maintainer_id', $request->maintainer);
        }

        if (!empty($request->date)) {
            [$start, $end] = explode(' - ', $request->date);
            $query->whereBetween('request_date', [
                \Carbon\Carbon::createFromFormat('m/d/Y', trim($start))->format('Y-m-d'),
                \Carbon\Carbon::createFromFormat('m/d/Y', trim($end))->format('Y-m-d'),
            ]);
        }
        return $query;
    }

    private function getFilterDropdowns()
    {
        $properties = scopeLandlord(Property::where('parent_id', parentId()), 'id')->pluck('name', 'id');
        $properties->prepend(__('Select Property'), '');

        $units = scopeLandlord(PropertyUnit::where('parent_id', parentId()))->pluck('name', 'id');
        $units->prepend(__('Select Units'), '');

        $maintainers = User::where('parent_id', parentId())->where('type', 'maintainer')->get()->pluck('full_name', 'id');
        $maintainers->prepend(__('Select Maintainers'), '');

        return compact('properties', 'units', 'maintainers');
    }

    public function index(Request $request)
    {
        if (!\Auth::user()->can('manage maintenance request'))
            return redirect()->back()->with('error', __('Permission Denied!'));

        $query = $this->applyRoleScope(MaintenanceRequest::orderBy('id', 'desc'), \Auth::user());

        if ($request->ajax()) {
            $maintenanceRequests = $this->applyAjaxFilters($query, $request)->get();
            return response()->json([
                'html' => view('maintenance_request.maintenance_list', compact('maintenanceRequests'))->render()
            ]);
        }

        return view('maintenance_request.index', $this->getFilterDropdowns());
    }

    public function pendingRequest(Request $request)
    {
        if (!\Auth::user()->can('manage maintenance request'))
            return redirect()->back()->with('error', __('Permission Denied!'));

        $query = $this->applyRoleScope(MaintenanceRequest::where('status', 'pending')->orderBy('id', 'desc'), \Auth::user());

        if ($request->ajax()) {
            $maintenanceRequests = $this->applyAjaxFilters($query, $request)->get();
            return response()->json([
                'html' => view('maintenance_request.maintenance_list', compact('maintenanceRequests'))->render()
            ]);
        }

        $maintenanceRequests = $query->get();
        return view(
            'maintenance_request.type',
            array_merge($this->getFilterDropdowns(), compact('maintenanceRequests'))
        )->with('title', __('Pending Requests'));
    }

    public function inProgressRequest(Request $request)
    {
        if (!\Auth::user()->can('manage maintenance request'))
            return redirect()->back()->with('error', __('Permission Denied!'));

        $query = $this->applyRoleScope(MaintenanceRequest::where('status', 'in_progress')->orderBy('id', 'desc'), \Auth::user());

        if ($request->ajax()) {
            $maintenanceRequests = $this->applyAjaxFilters($query, $request)->get();
            return response()->json([
                'html' => view('maintenance_request.maintenance_list', compact('maintenanceRequests'))->render()
            ]);
        }

        $maintenanceRequests = $query->get();
        return view(
            'maintenance_request.type',
            array_merge($this->getFilterDropdowns(), compact('maintenanceRequests'))
        )->with('title', __('In Progress Requests'));
    }

    public function create()
    {
        if (\Auth::user()->can('create maintenance request')) {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
            $property->prepend(__('Select Property'), 0);


            $types = Type::where('parent_id', parentId())->where('type', 'issue')->get()->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');

            $status = MaintenanceRequest::status();
            $vendors = \App\Models\Vendor::where('parent_id', parentId())->where('is_active', 1)->pluck('name', 'id');
            return view('maintenance_request.create', compact('property', 'types', 'status', 'vendors'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create maintenance request')) {
            $rules = [
                'property_id' => 'required',
                'unit_id' => 'required',
                'issue_type' => 'required',
            ];

            if (\Auth::user()->type != 'tenant') {
                $rules['maintainer_id'] = 'required';
                $rules['request_date'] = 'required';
            }
            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $tenant = null;
            if (auth()->user()->type == 'tenant') {
                // a tenant can only raise requests for the unit they rent
                $tenant = auth()->user()->tenants;
                if (empty($tenant) || $tenant->property != $request->property_id || $tenant->unit != $request->unit_id) {
                    return redirect()->back()->with('error', __('You can only raise requests for your own unit.'));
                }
            } else {
                $unit = PropertyUnit::where('parent_id', parentId())->where('id', $request->unit_id)->where('property_id', $request->property_id)->first();
                if (!$unit) {
                    return redirect()->back()->with('error', __('Invalid property unit selected.'));
                }
                if (!$this->isOwnMaintainer($request->maintainer_id)) {
                    return redirect()->back()->with('error', __('Invalid maintainer selected.'));
                }
                if (!empty($request->tenant_id) && !Tenant::where('parent_id', parentId())->where('id', $request->tenant_id)->exists()) {
                    return redirect()->back()->with('error', __('Invalid tenant selected.'));
                }
            }
            $MaintenanceRequest = new MaintenanceRequest();
            $MaintenanceRequest->property_id = $request->property_id;
            $MaintenanceRequest->unit_id = $request->unit_id;
            $MaintenanceRequest->issue_type = $request->issue_type;
            $MaintenanceRequest->status = !empty($request->status) && auth()->user()->type != 'tenant' ? $request->status : 'pending';
            $MaintenanceRequest->notes = $request->notes;
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->preferred_date) && $request->preferred_date >= date('Y-m-d')) {
                $MaintenanceRequest->preferred_date = $request->preferred_date;
            }
            $MaintenanceRequest->preferred_slot = array_key_exists($request->preferred_slot, MaintenanceRequest::slots()) ? $request->preferred_slot : null;
            if (auth()->user()->type == 'tenant') {
                $MaintenanceRequest->request_date = now();
                $MaintenanceRequest->tenant_id = $tenant->id;
            } else {
                $MaintenanceRequest->tenant_id = $request->tenant_id;
                $MaintenanceRequest->request_date = $request->request_date;
                $MaintenanceRequest->maintainer_id = $request->maintainer_id;
                $MaintenanceRequest->vendor_id = $this->ownVendorId($request->vendor_id);

            }
            $MaintenanceRequest->parent_id = parentId();
            $MaintenanceRequest->save();


            if ($request->hasFile('issue_attachment')) {
                $uploadResult = handleFileUpload($request->file('issue_attachment'), 'upload/issue_attachment/');
                if ($uploadResult['flag'] == 0) {
                    return redirect()->back()->with('error', $uploadResult['msg']);
                }
                $MaintenanceRequest->issue_attachment = $uploadResult['filename'];
                $MaintenanceRequest->save();
            }
            \App\Support\MaintenanceTriage::apply($MaintenanceRequest);
            $owner = User::find($MaintenanceRequest->parent_id);
            $setting = settings();
            if (auth()->user()->type != 'tenant') {

                triggerN8n('create_maintenance_request', [
                    'tenant_name' => !empty($MaintenanceRequest->tenetData->user->name) ? $MaintenanceRequest->tenetData->user->name : '',
                    'tenant_mail' => !empty($MaintenanceRequest->tenetData->user->email) ? $MaintenanceRequest->tenetData->user->email : '',
                    'tenant_phone' => !empty($MaintenanceRequest->tenetData->user->phone_number) ? $MaintenanceRequest->tenetData->user->phone_number : '',
                    'issue_type' => !empty($MaintenanceRequest->types) ? $MaintenanceRequest->types->title : '',
                    'issue_description' => $MaintenanceRequest->notes,
                    'owner_name' => $owner->name,
                    'owner_email' => $owner->email,
                    'owner_phone' => $owner->phone_number,
                    'created_at' => dateFormat($MaintenanceRequest->created_at),
                    'company_name' => $setting['company_name'],
                    'company_email' => $setting['company_email'],
                    'company_phone' => $setting['company_phone'],
                    'company_address' => $setting['company_address']
                ]);

                $module = 'maintenance_request_create';
                $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
                $errorMessage = '';
                $user = User::where('id', $MaintenanceRequest->maintainer_id)->first();
                if (!empty($notification) && !empty($user)) {
                    $notification['user_id'] = \Auth::user()->id;

                    // dd($user);
                    $notification_responce = MessageReplace($notification, $MaintenanceRequest->id);
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
                return redirect()->back()->with('success', __('Maintenance request successfully created.') . '</br>' . $errorMessage);
            }

            return redirect()->back()->with('success', __('Maintenance request successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function show($ids)
    {

        if (!\Auth::user()->can('show maintenance request') && !\Auth::user()->can('manage maintenance request')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $id = Crypt::decrypt($ids);
        $maintenanceRequest = $this->findVisibleRequest($id);
        if (!$maintenanceRequest) {
            return redirect()->back()->with('error', __('Maintenance request not found.'));
        }
        $maintainer = Maintainer::where('user_id', $maintenanceRequest->maintainer_id)->first();

        return view('maintenance_request.show', compact('maintenanceRequest', 'maintainer'));
    }


    public function edit(MaintenanceRequest $maintenanceRequest)
    {
        if (\Auth::user()->can('edit maintenance request') && $maintenanceRequest->parent_id == parentId() && \Auth::user()->type != 'tenant' && \Auth::user()->type != 'maintainer') {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
            $property->prepend(__('Select Property'), 0);

            $types = Type::where('parent_id', parentId())->where('type', 'issue')->get()->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');

            $status = MaintenanceRequest::status();
            $selectedUnit = $maintenanceRequest->unit_id;
            $selectedMaintainer = $maintenanceRequest->maintainer_id;
            $vendors = \App\Models\Vendor::where('parent_id', parentId())->pluck('name', 'id');

            return view('maintenance_request.edit', compact('property', 'types', 'maintenanceRequest', 'status', 'selectedUnit', 'selectedMaintainer', 'vendors'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        if (\Auth::user()->can('edit maintenance request') && $maintenanceRequest->parent_id == parentId() && \Auth::user()->type != 'tenant' && \Auth::user()->type != 'maintainer') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'property_id' => 'required',
                    'unit_id' => 'required',
                    'issue_type' => 'required',
                    'maintainer_id' => 'required',
                    'request_date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $unit = PropertyUnit::where('parent_id', parentId())->where('id', $request->unit_id)->where('property_id', $request->property_id)->first();
            if (!$unit || !$this->isOwnMaintainer($request->maintainer_id)) {
                return redirect()->back()->with('error', __('Invalid property unit or maintainer selected.'));
            }

            $maintenanceRequest->property_id = $request->property_id;
            $maintenanceRequest->unit_id = $request->unit_id;
            $maintenanceRequest->issue_type = $request->issue_type;
            $maintenanceRequest->maintainer_id = $request->maintainer_id;
            $maintenanceRequest->vendor_id = $this->ownVendorId($request->vendor_id);
            $maintenanceRequest->status = $request->status;
            $maintenanceRequest->tenant_id = $request->tenant_id;
            $maintenanceRequest->notes = $request->notes;
            $maintenanceRequest->request_date = $request->request_date;
            $maintenanceRequest->save();

            if ($request->hasFile('issue_attachment')) {
                $uploadResult = handleFileUpload($request->file('issue_attachment'), 'upload/issue_attachment/');
                if ($uploadResult['flag'] == 0) {
                    return redirect()->back()->with('error', $uploadResult['msg']);
                }
                $maintenanceRequest->issue_attachment = $uploadResult['filename'];
                $maintenanceRequest->save();
            }

            return redirect()->back()->with('success', __('Maintenance request successfully update.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        if (\Auth::user()->can('delete maintenance request') && $maintenanceRequest->parent_id == parentId()) {

            if ($maintenanceRequest) {
                if (!empty($maintenanceRequest->issue_attachment)) {
                    deleteOldFile($maintenanceRequest->issue_attachment, 'upload/issue_attachment/');
                }

                $maintenanceRequest->delete();
            }
            return redirect()->back()->with('success', __('Maintenance request successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function action($id)
    {
        $maintenanceRequest = $this->findVisibleRequest($id);
        if (!$maintenanceRequest || !$this->canUpdateStatus($maintenanceRequest)) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $status = MaintenanceRequest::status();
        return view('maintenance_request.action', compact('maintenanceRequest', 'status'));
    }

    public function actionData(Request $request, $id)
    {
        $maintenanceRequest = $this->findVisibleRequest($id);
        if (!$maintenanceRequest || !$this->canUpdateStatus($maintenanceRequest)) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make(
            $request->all(),
            [
                'fixed_date' => 'required',
                'status' => 'required',
                'amount' => 'required',
                'labor_hours' => 'nullable|numeric|min:0|max:9999',
                'after_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $setting = settings();

        if (!array_key_exists($request->status, MaintenanceRequest::status())) {
            return redirect()->back()->with('error', __('Invalid status.'));
        }
        $maintenanceRequest->fixed_date = $request->fixed_date;
        $maintenanceRequest->status = $request->status;
        $maintenanceRequest->amount = $request->amount;
        $maintenanceRequest->labor_hours = $request->labor_hours ?: 0;
        $maintenanceRequest->save();

        if ($request->hasFile('after_photo')) {
            $uploadResult = handleFileUpload($request->file('after_photo'), 'upload/issue_attachment/', ['mimes:jpg,jpeg,png', 'max:10240']);
            if ($uploadResult['flag'] == 0) {
                return redirect()->back()->with('error', $uploadResult['msg']);
            }
            $maintenanceRequest->after_photo = $uploadResult['filename'];
            $maintenanceRequest->save();
        }


        if ($request->hasFile('invoice')) {
            $uploadResult = handleFileUpload($request->file('invoice'), 'upload/invoice/');

            if ($uploadResult['flag'] == 0) {
                return redirect()->back()->with('error', $uploadResult['msg']);
            }
            $maintenanceRequest->invoice = $uploadResult['filename'];
            $maintenanceRequest->save();
        }


        // the "request completed" email/SMS/WhatsApp only when the job is actually completed
        $errorMessage = $maintenanceRequest->status == 'completed' ? self::sendCompletionNotice($maintenanceRequest) : '';

        return redirect()->back()->with('success', __('Maintenance request successfully update.') . '</br>' . $errorMessage);
    }



    /**
     * "Maintenance request completed" notice to the tenant(s) and owner (email, SMS, WhatsApp) and the n8n hook.
     * Returns an email error message, or ''. Shared by the website and the mobile API.
     */
    public static function sendCompletionNotice(MaintenanceRequest $maintenanceRequest)
    {
        $tenants = Tenant::where('parent_id', $maintenanceRequest->parent_id)->where('property', $maintenanceRequest->property_id)
            ->where('unit', $maintenanceRequest->unit_id)
            ->get();

        if ($tenants->isNotEmpty()) {

            $userIds = [];
            foreach ($tenants as $tenant) {
                $userIds[] = $tenant->user_id;
                $userIds[] = $tenant->parent_id;
            }

            $email = User::whereIn('id', array_unique($userIds))
                ->pluck('email')
                ->toArray();
        } else {

            $email = User::where('id', $maintenanceRequest->parent_id)
                ->pluck('email')
                ->toArray();
        }
        $setting = settings();
        triggerN8n('maintenance_request_complete', [
            'tenant_name' => !empty($maintenanceRequest->tenetData->user->name) ? $maintenanceRequest->tenetData->user->name : '',
            'tenant_mail' => !empty($maintenanceRequest->tenetData->user->email) ? $maintenanceRequest->tenetData->user->email : '',
            'tenant_phone' => !empty($maintenanceRequest->tenetData->user->phone_number) ? $maintenanceRequest->tenetData->user->phone_number : '',
            'issue_type' => !empty($maintenanceRequest->types) ? $maintenanceRequest->types->title : '',
            'issue_description' => $maintenanceRequest->notes,
            'maintainer_name' => !empty($maintenanceRequest->maintainers) ? $maintenanceRequest->maintainers->name : '',
            'maintainer_email' => !empty($maintenanceRequest->maintainers) ? $maintenanceRequest->maintainers->email : '',
            'maintainer_phone' => !empty($maintenanceRequest->maintainers) ? $maintenanceRequest->maintainers->phone_number : '',
            'created_at' => dateFormat($maintenanceRequest->created_at),
            'updated_at' => dateFormat($maintenanceRequest->updated_at),
            'company_name' => $setting['company_name'],
            'company_email' => $setting['company_email'],
            'company_phone' => $setting['company_phone'],
            'company_address' => $setting['company_address']
        ]);

        $module = 'maintenance_request_complete';
        $notification = Notification::where('parent_id', $maintenanceRequest->parent_id)->where('module', $module)->first();
        $errorMessage = '';
        if (!empty($notification)) {
            $notification_responce = MessageReplace($notification, $maintenanceRequest->id);
            $datas['subject'] = $notification_responce['subject'];
            $datas['message'] = $notification_responce['message'];
            $datas['module'] = $module;
            $datas['logo'] = $setting['company_logo'];
            $to = $email;
            if ($notification->enabled_email == 1) {
                $response = commonEmailSend($to, $datas);
                if ($response['status'] == 'error') {
                    $errorMessage = $response['message'];
                }
            }

            if ($notification->enabled_sms == 1) {
                $twilio_sid = getSettingsValByName('twilio_sid');
                if (!empty($twilio_sid) && !empty($maintenanceRequest->maintainers)) {
                    send_twilio_msg($maintenanceRequest->maintainers->phone_number, $notification_responce['sms_message']);
                }
            }

            if ($notification->enabled_whatsapp == 1) {
                $twilio_sid = getSettingsValByName('twilio_sid');
                if (!empty($twilio_sid) && !empty($maintenanceRequest->tenetData->user)) {
                    $params = [
                        'to' => $maintenanceRequest->tenetData->user->phone_number,
                        'body' => $notification_responce['sms_message'],
                    ];
                    sendTwilioWhatsappMsg($params);
                }
            }
        }

        return $errorMessage;
    }

    public function comment(Request $request, $id)
    {

        // dd($request->all(),$id);
        $validator = \Validator::make(
            $request->all(),
            [
                'comment' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $maintenance = $this->findVisibleRequest($id);
        if (!$maintenance) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $comment = new Comments();
        $comment->maintenance_id = $maintenance->id;
        $comment->user_id = \Auth::user()->id;
        $comment->comment = $request->comment;
        $comment->parent_id = parentId();
        $comment->save();


        return redirect()->back()->with('success', __('Comment successfully send.'));
    }

    public function commentDestroy($id)
    {
        $comment = Comments::findOrFail($id);
        $authUser = \Auth::user();
        if ($authUser->id == $comment->user_id || ($authUser->type === 'owner' && $comment->parent_id == parentId())) {
            $comment->delete();
            return redirect()->back()->with('success', __('Comment deleted successfully.'));
        }

        return redirect()->back()->with('error', __('You are not authorized to delete this comment.'));
    }

    // The tenant who raised a completed request rates the work (1-5)
    // the assigned maintainer tells the tenant they are on their way
    public function onTheWay($id)
    {
        $maintenanceRequest = MaintenanceRequest::where('parent_id', parentId())->where('id', (int) $id)
            ->where('maintainer_id', \Auth::id())->whereIn('status', ['pending', 'in_progress'])->first();
        if (!$maintenanceRequest) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $maintenanceRequest->on_the_way_at = now();
        $maintenanceRequest->status = 'in_progress'; // the status push to the tenant is replaced by the message below
        $maintenanceRequest->saveQuietly();

        if ($tenant = $maintenanceRequest->tenetData) {
            $name = \Auth::user()->name;
            $what = optional($maintenanceRequest->types)->title ?: __('your repair');
            \App\Support\Notify::push($tenant->user_id, __(':name is on the way', ['name' => $name]), __('For :what. Please make sure someone can let them in.', ['what' => $what]), route('maintenance-request.show', \Crypt::encrypt($maintenanceRequest->id)));
            if (!empty(optional($tenant->user)->phone_number)) {
                send_twilio_msg($tenant->user->phone_number, __(':name is on the way for :what.', ['name' => $name, 'what' => $what]), parentId());
            }
        }
        return redirect()->back()->with('success', __('The tenant has been told you are on the way.'));
    }

    public function rate(Request $request, $id)
    {
        $maintenanceRequest = $this->findVisibleRequest($id);
        if (\Auth::user()->type != 'tenant' || !$maintenanceRequest || $maintenanceRequest->status != 'completed' || $maintenanceRequest->tenant_rating > 0) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'tenant_rating' => 'required|integer|min:1|max:5',
            'tenant_feedback' => 'nullable|max:1000',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        $maintenanceRequest->tenant_rating = $request->tenant_rating;
        $maintenanceRequest->tenant_feedback = $request->tenant_feedback;
        $maintenanceRequest->save();
        return redirect()->back()->with('success', __('Thank you for your feedback.'));
    }

    public function maintenanceAssign(Request $request)
    {
        if (!\Auth::user()->can('assign maintainer')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $maintenanceRequest = MaintenanceRequest::where('parent_id', parentId())->findOrFail($request->request_id);
        $status = MaintenanceRequest::status();

        $maintainers = Maintainer::where('parent_id', parentId())
            ->where('property_id', $maintenanceRequest->property_id)
            ->get()
            ->mapWithKeys(function ($maintainer) {
                $name = optional($maintainer->user)->name;
                $type = optional($maintainer->types)->title;

                return [
                    $maintainer->user_id => $name . ($type ? ' - ' . $type : '')
                ];
            });

        $maintainers->prepend(__('Select Maintainer'), '');

        return view('maintenance_request.maintainer_assign', compact('maintenanceRequest', 'maintainers', 'status'));
    }

    public function maintenanceAssignStore(Request $request)
    {
        if (!\Auth::user()->can('assign maintainer')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $maintenanceRequest = MaintenanceRequest::where('parent_id', parentId())->find($request->request_id);
        if (!$maintenanceRequest || !$this->isOwnMaintainer($request->maintainer_id, $maintenanceRequest->property_id)) {
            return redirect()->back()->with('error', __('Invalid maintainer selected.'));
        }
        if (!array_key_exists($request->status, MaintenanceRequest::status())) {
            return redirect()->back()->with('error', __('Invalid status.'));
        }
        $maintenanceRequest->maintainer_id = $request->maintainer_id;
        $maintenanceRequest->status = $request->status;
        $maintenanceRequest->save();

        return redirect()->back()->with('success', 'Maintenance Request Assign Successfully');
    }



    public function propertyBasedMaintainer(Request $request)
    {
        $maintainers = Maintainer::where('parent_id', parentId())
            ->when($request->property_id, function ($q) use ($request) {

                $propertyIds = is_array($request->property_id)
                    ? $request->property_id
                    : [$request->property_id];

                $q->where(function ($query) use ($propertyIds) {
                    foreach ($propertyIds as $id) {
                        $query->orWhereRaw("FIND_IN_SET(?, property_id)", [$id]);
                    }
                });
            })
            ->get()
            ->mapWithKeys(function ($maintainer) {
                $name = optional($maintainer->user)->name;
                $type = optional($maintainer->types)->title;

                return [
                    $maintainer->user_id => $name . ($type ? ' - ' . $type : '')
                ];
            });


        return response()->json($maintainers);
    }

}
