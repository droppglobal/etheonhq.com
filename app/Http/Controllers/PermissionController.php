<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{

    public function index()
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $permissionData = Permission::orderBy('id', 'desc')->get();
        return view('user_permission.index', compact('permissionData'));
    }


    public function create()
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $userRoles = Role::get()->pluck('name','id');
        return view('user_permission.create', compact('userRoles'));
    }


    public function store(Request $request)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $validator = \Validator::make(
            $request->all(), [
                'title' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $permissions=explode(',',$request->title);
        foreach ($permissions as $permission){
            $permission = trim($permission);
            if ($permission === '') {
                continue;
            }
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            if (!empty($request->user_roles)) {
                foreach ($request->user_roles as $userRole) {
                    $role = Role::find($userRole);
                    if (!$role) {
                        continue;
                    }
                    $permissionArr = Permission::where('name', $permission)->first();
                    $role->givePermissionTo($permissionArr);
                }
            }
        }
        return redirect()->back()->with('success', 'Permission successfully created.');
    }


    public function destroy($id)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $permission = Permission::findOrFail($id);
        $permission->delete();
        return redirect()->back()->with('success', 'Permission successfully deleted.');
    }
}
