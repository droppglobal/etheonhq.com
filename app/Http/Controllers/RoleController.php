<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;


class RoleController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('manage role')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $roleData = Role::where('parent_id', parentId())->orderBy('id', 'desc')->get();
        return view('role.index', compact('roleData'));
    }

    public function create()
    {
        if (!\Auth::user()->can('create role')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $permissionList = $this->assignablePermissions();
        return view('role.create', compact('permissionList'));
    }

    // Permissions the logged-in user holds; a user can never grant more than this.
    private function assignablePermissions()
    {
        $permissionList = new Collection();
        foreach (\Auth::user()->roles as $role) {
            $permissionList = $permissionList->merge($role->permissions);
        }
        return $permissionList->unique('id');
    }

    private function findOwnRole($id)
    {
        return Role::where('parent_id', parentId())->where('id', $id)->first();
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('create role')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'title' => 'required|unique:roles,name,null,id,parent_id,' . parentId(),
                'user_permission' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->route('role.index')->with('error', $messages->first());
        }

        $userRole = new Role();
        $userRole->name = $request->title;
        $userRole->parent_id = parentId();
        $userRole->save();
        $allowed = $this->assignablePermissions()->pluck('id')->map('intval')->toArray();
        $permissions = array_intersect(array_map('intval', (array) $request->user_permission), $allowed);
        $userRole->syncPermissions($permissions);
        return redirect()->route('role.index')->with('success', __('Role successfully created.'));

    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        if (!\Auth::user()->can('edit role')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $role = $this->findOwnRole($id);
        if (!$role) {
            return redirect()->route('role.index')->with('error', __('Role not found.'));
        }
        $permissionList = $this->assignablePermissions();

        $assignPermission = $role->permissions;
        $assignPermission = $assignPermission->pluck('id')->toArray();

        return view('role.edit', compact('role', 'permissionList', 'assignPermission'));
    }

    public function update(Request $request, $id)
    {
        if (!\Auth::user()->can('edit role')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $id = decrypt($id);
        $userRole = $this->findOwnRole($id);
        if (!$userRole) {
            return redirect()->route('role.index')->with('error', __('Role not found.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required|unique:roles,name,' . $userRole->id . ',id,parent_id,' . parentId(),
                'user_permission' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->route('role.index')->with('error', $messages->first());
        }

        // Only the name is editable; tenant/maintainer names are relied on throughout the app.
        if (!in_array($userRole->name, ['tenant', 'maintainer', 'landlord']) && $userRole->name != $request->name) {
            // users.type mirrors the role name, keep them in sync
            \App\Models\User::where('parent_id', parentId())->where('type', $userRole->name)->update(['type' => $request->name]);
            $userRole->name = $request->name;
            $userRole->save();
        }

        $allowed = $this->assignablePermissions()->pluck('id')->map('intval')->toArray();
        $submittedPermissions = array_intersect(array_map('intval', $request->user_permission ?? []), $allowed);
        $editablePermissions = array_map('intval', $request->editable_permissions ?? []);
        $currentPermissions = array_map('intval', $userRole->permissions->pluck('id')->toArray());

        $hiddenPermissions = array_diff($currentPermissions, $editablePermissions);

        $finalPermissions = array_unique(array_merge($submittedPermissions, $hiddenPermissions));
        $userRole->syncPermissions($finalPermissions);

        return redirect()->route('role.index')->with('success', __('Role successfully updated.'));
    }

    public function destroy($id)
    {
        if (!\Auth::user()->can('delete role')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $userRole = $this->findOwnRole($id);
        if (!$userRole || in_array($userRole->name, ['tenant', 'maintainer', 'landlord'])) {
            return redirect()->route('role.index')->with('error', __('This role cannot be deleted.'));
        }
        if (\Auth::user()->roleWiseUserCount($userRole->name) > 0) {
            return redirect()->route('role.index')->with('error', __('Role is assigned to users and cannot be deleted.'));
        }
        $userRole->delete();
        return redirect()->route('role.index')->with('success', 'Role successfully deleted.');
    }

}
