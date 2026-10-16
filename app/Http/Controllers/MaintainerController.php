<?php

namespace App\Http\Controllers;

use App\Models\Maintainer;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\Property;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MaintainerController extends Controller
{

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage maintainer')) {
            if ($request->ajax()) {
                $startDate = '';
                $endDate = '';
                $dateRange = $request->date;

                if (!empty($dateRange)) {
                    [$start, $end] = explode(' - ', $dateRange);
                    $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($start))->format('Y-m-d');
                    $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($end))->format('Y-m-d');
                }
                $query = Maintainer::where('parent_id', parentId());
                if (!empty($request->search)) {
                    $query->whereHas('user', function ($q) use ($request) {
                        $q->where('first_name', 'like', '%' . $request->search . '%')
                            ->orWhere('last_name', 'like', '%' . $request->search . '%')
                            ->orWhere('phone_number', 'like', '%' . $request->search . '%');
                    });
                }

                if (!empty($request->property)) {
                    $query->where('property_id', $request->property);
                }
                if (!empty($request->type)) {
                    $query->where('type_id', $request->type);
                }

                $maintainers = $query->orderBy('id', 'desc')->paginate(12);
                $html = view('maintainer.maintainer_list', compact('maintainers'))->render();

                return response()->json(['html' => $html]);

            }
            $maintainers = Maintainer::where('parent_id', parentId())->orderBy('id', 'desc')->paginate(12);
            $property = Property::where('parent_id', parentId())->pluck('name', 'id');
            $property->prepend(__('Select Property'), '');
            $types = Type::where('parent_id', parentId())->where('type', 'maintainer_type')->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');

            return view('maintainer.index', compact('maintainers', 'property', 'types'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create maintainer')) {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');

            $types = Type::where('parent_id', parentId())->where('type', 'maintainer_type')->get()->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');

            return view('maintainer.create', compact('property', 'types'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create maintainer')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required',
                    'phone_number' => 'required',
                    'property_id' => 'required',
                    'type_id' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $userRole = Role::where('parent_id', parentId())->where('name', 'maintainer')->first();
            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->email_verified_at = now();
            $user->password = \Hash::make($request->password);
            $user->phone_number = $request->phone_number;
            $user->type = $userRole->name;
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

            $maintainer = new Maintainer();
            $maintainer->user_id = $user->id;
            $maintainer->property_id = !empty($request->property_id) ? implode(',', $request->property_id) : '';
            $maintainer->type_id = $request->type_id;
            $maintainer->parent_id = parentId();

            // dd($maintainer);
            $maintainer->save();
            triggerN8n('create_maintainer', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number,
                'password' => $request->password,
            ]);
            $module = 'maintainer_create';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            $setting = settings();
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
                        send_twilio_msg($request->phone_number, $notification_responce['sms_message']);
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


            return redirect()->back()->with('success', __('Maintainer successfully created.') . '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function show($id)
    {
        if (!\Auth::user()->can('manage maintainer')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $id = decrypt($id);
        $maintainer = Maintainer::where('parent_id', parentId())->find($id);
        if (!$maintainer) {
            return redirect()->back()->with('error', __('Maintainer not found.'));
        }
        $requests = MaintenanceRequest::where('maintainer_id', $maintainer->user_id)->orderBy('id', 'desc')->get();
        return view('maintainer.show', compact('maintainer', 'requests'));
    }

    public function updateRequestStatus(Request $request)
    {
        if (!array_key_exists($request->status, MaintenanceRequest::status())) {
            return response()->json(['success' => false], 422);
        }
        $user = \Auth::user();
        $query = MaintenanceRequest::where('id', $request->requestId);
        if ($user->type == 'maintainer') {
            $query->where('maintainer_id', $user->id);
        } elseif ($user->type != 'tenant' && $user->can('edit maintenance request')) {
            $query->where('parent_id', parentId());
        } else {
            return response()->json(['success' => false], 403);
        }
        $maintenanceRequest = $query->first();
        if ($maintenanceRequest) {
            $maintenanceRequest->status = $request->status;
            $maintenanceRequest->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    public function edit(Maintainer $maintainer)
    {
        if (\Auth::user()->can('edit maintainer') && $maintainer->parent_id == parentId()) {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');

            $types = Type::where('parent_id', parentId())->where('type', 'maintainer_type')->get()->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');
            $user = User::find($maintainer->user_id);
            return view('maintainer.edit', compact('property', 'maintainer', 'types', 'user'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function update(Request $request, Maintainer $maintainer)
    {
        $user = User::find($maintainer->user_id);
        if (\Auth::user()->can('edit maintainer') && $maintainer->parent_id == parentId() && $user) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required|email|unique:users,email,' . $user->id,
                    'phone_number' => 'required',
                    'property_id' => 'required',
                    'type_id' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $user = User::find($maintainer->user_id);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->phone_number = $request->phone_number;
            $user->save();

            if ($request->hasFile('profile')) {

                $uploadResult = handleFileUpload($request->file('profile'), 'upload/profile/');
                if ($uploadResult['flag'] == 0) {
                    return redirect()->back()->with('error', $uploadResult['msg']);
                }
                if (!empty($user->profile) && $user->profile !== 'avatar.png') {
                    deleteOldFile($user->profile, 'upload/profile/');
                }
                $user->profile = $uploadResult['filename'];
                $user->save();
            }

            $maintainer->property_id = !empty($request->property_id) ? implode(',', $request->property_id) : 0;
            $maintainer->type_id = $request->type_id;
            $maintainer->save();


            return redirect()->back()->with('success', __('Maintainer successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function destroy(Maintainer $maintainer)
    {
        if (\Auth::user()->can('delete maintainer') && $maintainer->parent_id == parentId()) {

            $user = User::find($maintainer->user_id);
            if ($user && !empty($user->profile) && $user->profile !== 'avatar.png') {
                deleteOldFile($user->profile, 'upload/profile/');
            }

            if ($user) {
                $user->delete();
            }

            $maintainer->delete();
            return redirect()->back()->with('success', __('Maintainer successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
}
