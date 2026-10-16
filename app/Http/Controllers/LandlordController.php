<?php

namespace App\Http\Controllers;

use App\Models\LandlordPayout;
use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\User;
use App\Support\LandlordStatement;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

/**
 * Agency side: landlords (property owners) whose properties this account manages.
 */
class LandlordController extends Controller
{
    private function findOwn($id)
    {
        return User::where('parent_id', parentId())->where('type', 'landlord')->where('id', $id)->first();
    }

    private function landlordRole()
    {
        return defaultLandlordCreate(parentId());
    }

    // properties can belong to one landlord; reassigning moves them
    private function assignProperties($landlordId, $propertyIds)
    {
        $propertyIds = array_map('intval', (array) $propertyIds);
        Property::where('parent_id', parentId())->where('landlord_id', $landlordId)->whereNotIn('id', $propertyIds)->update(['landlord_id' => 0]);
        Property::where('parent_id', parentId())->whereIn('id', $propertyIds)->update(['landlord_id' => $landlordId]);
    }

    public function index()
    {
        if (!\Auth::user()->can('manage landlord')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $landlords = User::where('parent_id', parentId())->where('type', 'landlord')->orderBy('first_name')->get();
        $profiles = LandlordProfile::where('parent_id', parentId())->get()->keyBy('user_id');
        $propertyCounts = Property::where('parent_id', parentId())->where('landlord_id', '>', 0)->selectRaw('landlord_id, COUNT(*) as total')->groupBy('landlord_id')->pluck('total', 'landlord_id');
        $balances = $landlords->mapWithKeys(function ($landlord) {
            return [$landlord->id => LandlordStatement::balance($landlord->id, parentId())];
        });
        return view('landlord.index', compact('landlords', 'profiles', 'propertyCounts', 'balances'));
    }

    public function create()
    {
        if (!\Auth::user()->can('create landlord')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $landlord = null;
        $profile = null;
        $properties = Property::where('parent_id', parentId())->pluck('name', 'id');
        $assigned = [];
        return view('landlord.form', compact('landlord', 'profile', 'properties', 'assigned'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('create landlord')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'first_name' => 'required|max:191',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'management_fee_percent' => 'nullable|numeric|min:0|max:100',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $role = $this->landlordRole();
        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name ?: '';
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;
        $user->password = \Hash::make($request->password);
        $user->type = 'landlord';
        $user->email_verified_at = now();
        $user->is_active = 1;
        $user->profile = 'avatar.png';
        $user->lang = 'english';
        $user->parent_id = parentId();
        $user->save();
        $user->assignRole($role);

        LandlordProfile::create([
            'user_id' => $user->id,
            'management_fee_percent' => $request->management_fee_percent ?: 0,
            'bank_details' => $request->bank_details,
            'notes' => $request->notes,
            'parent_id' => parentId(),
        ]);
        $this->assignProperties($user->id, $request->property_ids);

        return redirect()->back()->with('success', __('Landlord successfully created.'));
    }

    public function show(Request $request, $id)
    {
        $landlord = $this->findOwn($id);
        if (!\Auth::user()->can('manage landlord') || !$landlord) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        [$from, $to, $month] = LandlordStatement::monthRange($request->month);
        $statement = LandlordStatement::build($landlord->id, parentId(), $from, $to);
        $profile = LandlordProfile::where('parent_id', parentId())->where('user_id', $landlord->id)->first();
        $properties = Property::where('parent_id', parentId())->where('landlord_id', $landlord->id)->get();
        return view('landlord.show', compact('landlord', 'statement', 'month', 'profile', 'properties'));
    }

    public function edit($id)
    {
        $landlord = $this->findOwn($id);
        if (!\Auth::user()->can('edit landlord') || !$landlord) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $profile = LandlordProfile::where('parent_id', parentId())->where('user_id', $landlord->id)->first();
        $properties = Property::where('parent_id', parentId())->pluck('name', 'id');
        $assigned = Property::where('parent_id', parentId())->where('landlord_id', $landlord->id)->pluck('id')->toArray();
        return view('landlord.form', compact('landlord', 'profile', 'properties', 'assigned'));
    }

    public function update(Request $request, $id)
    {
        $landlord = $this->findOwn($id);
        if (!\Auth::user()->can('edit landlord') || !$landlord) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'first_name' => 'required|max:191',
            'email' => 'required|email|unique:users,email,' . $landlord->id,
            'management_fee_percent' => 'nullable|numeric|min:0|max:100',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        $landlord->first_name = $request->first_name;
        $landlord->last_name = $request->last_name ?: '';
        $landlord->email = $request->email;
        $landlord->phone_number = $request->phone_number;
        $landlord->save();

        LandlordProfile::updateOrCreate(
            ['parent_id' => parentId(), 'user_id' => $landlord->id],
            ['management_fee_percent' => $request->management_fee_percent ?: 0, 'bank_details' => $request->bank_details, 'notes' => $request->notes]
        );
        $this->assignProperties($landlord->id, $request->property_ids);

        return redirect()->back()->with('success', __('Landlord successfully updated.'));
    }

    public function destroy($id)
    {
        $landlord = $this->findOwn($id);
        if (!\Auth::user()->can('delete landlord') || !$landlord) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if (LandlordPayout::where('parent_id', parentId())->where('landlord_id', $landlord->id)->exists()) {
            return redirect()->back()->with('error', __('This landlord has payouts recorded. Remove their properties instead of deleting the account.'));
        }
        Property::where('parent_id', parentId())->where('landlord_id', $landlord->id)->update(['landlord_id' => 0]);
        LandlordProfile::where('parent_id', parentId())->where('user_id', $landlord->id)->delete();
        $landlord->delete();
        return redirect()->route('landlord.index')->with('success', __('Landlord successfully deleted.'));
    }

    public function payoutStore(Request $request, $id)
    {
        $landlord = $this->findOwn($id);
        if (!\Auth::user()->can('edit landlord') || !$landlord) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payout_date' => 'required|date',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        LandlordPayout::create($request->only('amount', 'payout_date', 'method', 'reference', 'notes') + ['landlord_id' => $landlord->id, 'parent_id' => parentId()]);
        return redirect()->back()->with('success', __('Payout recorded.'));
    }

    public function payoutDestroy($id)
    {
        $payout = LandlordPayout::where('parent_id', parentId())->find($id);
        if (!\Auth::user()->can('edit landlord') || !$payout) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $payout->delete();
        return redirect()->back()->with('success', __('Payout deleted.'));
    }
}
