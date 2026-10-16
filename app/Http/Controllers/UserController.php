<?php

namespace App\Http\Controllers;

use App\Models\LoggedHistory;
use App\Models\Notification;
use App\Models\PackageTransaction;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // Users the logged-in account manages: owners for super admin, staff (not tenants/maintainers) for owners.
    private function findManagedUser($id)
    {
        $query = User::where('parent_id', parentId())->where('id', $id);
        if (\Auth::user()->type == 'super admin') {
            $query->where('type', 'owner');
        } else {
            $query->whereNotIn('type', ['tenant', 'maintainer', 'landlord']);
        }
        return $query->first();
    }

    private function findAssignableRole($id)
    {
        return Role::where('parent_id', parentId())->whereNotIn('name', ['tenant', 'maintainer', 'landlord'])->where('id', $id)->first();
    }

    public function index()
    {
        if (\Auth::user()->can('manage user')) {
            if (\Auth::user()->type == 'super admin') {
                $users = User::where('parent_id', parentId())->where('type', 'owner')->orderBy('id', 'desc')->get();
                return view('user.index', compact('users'));
            } else {
                $users = User::where('parent_id', '=', parentId())->whereNotIn('type', ['tenant', 'maintainer', 'landlord'])->orderBy('id', 'desc')->get();
                return view('user.index', compact('users'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function create()
    {
        if (!\Auth::user()->can('create user')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        $userRoles = Role::where('parent_id', parentId())->whereNotIn('name', ['tenant', 'maintainer', 'landlord'])->get()->pluck('name', 'id');
        return view('user.create', compact('userRoles'));
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create user')) {
            if (\Auth::user()->type == 'super admin') {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required',
                        'email' => 'required|email|unique:users',
                        'password' => 'required|min:6',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $user = new User();
                $user->first_name = $request->name;
                $user->email = $request->email;
                $user->password = \Hash::make($request->password);
                $user->phone_number = $request->phone_number;
                $user->type = 'owner';
                $user->profile = 'avatar.png';
                $user->lang = 'english';
                $user->subscription = 1;
                $user->code = uniqid();
                $user->parent_id = parentId();
                $user->email_verified_at = now();
                $user->save();
                $userRole = Role::findByName('owner');
                $user->assignRole($userRole);
                defaultTenantCreate($user->id);
                defaultMaintainerCreate($user->id);
                defaultLandlordCreate($user->id);
                defaultStaffRolesCreate($user->id);
                defaultTemplate($user->id);

                // Default frontend Template
                FrontHomePageSection($user->id);
                AdditionalPageSection($user->id);



                if ($request->hasFile('profile')) {
                    $uploadResult = handleFileUpload($request->file('profile'), 'upload/profile/');
                    if ($uploadResult['flag'] == 0) {
                        return redirect()->back()->with('error', $uploadResult['msg']);
                    }
                    $user->profile = $uploadResult['filename'];
                    $user->save();
                }


                $module = 'owner_create';
                $setting = settings();
                $errorMessage = '';
                if (!empty($user)) {
                    $data['subject'] = 'New User Created';
                    $data['module'] = $module;
                    $data['password'] = $request->password;
                    $data['name'] = $request->name;
                    $data['email'] = $request->email;
                    $data['url'] = env('APP_URL');
                    $data['logo'] = $setting['company_logo'];
                    $to = $user->email;
                    $response = commonEmailSend($to, $data);
                    if ($response['status'] == 'error') {
                        $errorMessage = $response['message'];
                    }
                }

                return redirect()->route('users.index')->with('success', __('User successfully created.') . $errorMessage);
            } else {

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'first_name' => 'required',
                        'last_name' => 'required',
                        'email' => 'required|email|unique:users',
                        'password' => 'required|min:6',
                        'role' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $pricing_feature_settings = getSettingsValByIdName(1, 'pricing_feature');
                if ($pricing_feature_settings == 'on') {
                    $ids = parentId();
                    $authUser = \App\Models\User::find($ids);
                    $totalUser = $authUser->totalUser();
                    $subscription = Subscription::find($authUser->subscription);
                    if ($totalUser >= $subscription->user_limit && $subscription->user_limit != 0) {
                        return redirect()->back()->with('error', __('Your user limit is over, please upgrade your subscription.'));
                    }
                }
                $userRole = $this->findAssignableRole($request->role);
                if (!$userRole) {
                    return redirect()->back()->with('error', __('Invalid role selected.'));
                }
                $user = new User();
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->email = $request->email;
                $user->phone_number = $request->phone_number;
                $user->password = \Hash::make($request->password);
                $user->type = $userRole->name;
                $user->email_verified_at = now();
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

                triggerN8n('create_user', [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone_number,
                    'password' => $request->password,
                ]);
                $module = 'user_create';
                $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
                $setting = settings();
                $errorMessage = '';
                if (!empty($notification)) {
                    $notification->password = $request->password;
                    $notification_responce = MessageReplace($notification, $user->id);
                    $data['subject'] = $notification_responce['subject'];
                    $data['message'] = $notification_responce['message'];
                    $data['module'] = $module;
                    $data['password'] = $request->password;
                    $data['logo'] = $setting['company_logo'];
                    $to = $user->email;

                    if ($notification->enabled_email == 1) {
                        $response = commonEmailSend($to, $data);
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

                return redirect()->route('users.index')->with('success', __('User successfully created.') . '</br>' . $errorMessage);
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show(User $user)
    {
        if (!\Auth::user()->can('show user') || !$this->findManagedUser($user->id)) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        } else {
            $settings = settings();
            $transactions = PackageTransaction::where('user_id', $user->id)->orderBy('created_at', 'DESC')->get();
            $subscriptions = Subscription::get();
            return view('user.show', compact('user', 'transactions', 'settings', 'subscriptions'));
        }
    }


    public function edit($id)
    {
        if (!\Auth::user()->can('edit user')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        $user = $this->findManagedUser($id);
        if (!$user) {
            return redirect()->back()->with('error', __('User not found.'));
        }
        $userRoles = Role::where('parent_id', '=', parentId())->whereNotIn('name', ['tenant', 'maintainer', 'landlord'])->get()->pluck('name', 'id');
        $assignedRoleId = $user->roles->first()?->id;
        return view('user.edit', compact('user', 'userRoles', 'assignedRoleId'));
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit user')) {
            $user = $this->findManagedUser($id);
            if (!$user) {
                return redirect()->back()->with('error', __('User not found.'));
            }
            if (\Auth::user()->type == 'super admin') {

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required',
                        'email' => 'required|email|unique:users,email,' . $id,
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }
                $user->first_name = $request->name;
                $user->email = $request->email;
                $user->phone_number = $request->phone_number;
                $user->save();
                return redirect()->route('users.index')->with('success', 'User successfully updated.');
            } else {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'first_name' => 'required',
                        'last_name' => 'required',
                        'email' => 'required|email|unique:users,email,' . $id,
                        'role' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                $userRole = $this->findAssignableRole($request->role);
                if (!$userRole) {
                    return redirect()->back()->with('error', __('Invalid role selected.'));
                }
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->email = $request->email;
                $user->phone_number = $request->phone_number;
                $user->type = $userRole->name;
                $user->save();
                $user->roles()->sync($userRole);
                return redirect()->route('users.index')->with('success', 'User successfully updated.');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function destroy($id)
    {

        if (\Auth::user()->can('delete user')) {

            $user = $this->findManagedUser($id);
            if ($user && $user->id != \Auth::user()->id) {
                if (!empty($user->profile) && $user->profile !== 'avatar.png') {
                    deleteOldFile($user->profile, 'upload/profile/');
                }

                $user->delete();
                return redirect()->route('users.index')->with('success', __('User successfully deleted.'));
            }


            return redirect()->route('users.index')->with('error', __('User not found.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function loggedHistory()
    {
        $ids = parentId();
        $authUser = \App\Models\User::find($ids);
        $subscription = \App\Models\Subscription::find($authUser->subscription);

        if (\Auth::user()->can('manage logged history') && $subscription->enabled_logged_history == 1) {
            $histories = LoggedHistory::where('parent_id', parentId())->get();
            return view('logged_history.index', compact('histories'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function loggedHistoryShow($id)
    {
        if (\Auth::user()->can('manage logged history')) {
            $histories = LoggedHistory::where('parent_id', parentId())->findOrFail($id);
            return view('logged_history.show', compact('histories'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function loggedHistoryDestroy($id)
    {
        if (\Auth::user()->can('delete logged history')) {
            $histories = LoggedHistory::where('parent_id', parentId())->findOrFail($id);
            $histories->delete();
            return redirect()->back()->with('success', 'Logged history succefully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
