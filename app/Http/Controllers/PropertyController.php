<?php

namespace App\Http\Controllers;

use App\Models\Advantage;
use App\Models\Amenity;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\PropertyUnit;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    private function findOwnProperty($id)
    {
        return scopeLandlord(Property::where('parent_id', parentId())->where('id', $id), 'id')->first();
    }

    private function findOwnUnit($id)
    {
        return scopeLandlord(PropertyUnit::where('parent_id', parentId())->where('id', $id))->first();
    }

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage property')) {

            if ($request->ajax()) {

                $query = scopeLandlord(Property::where('parent_id', parentId()), 'id');

                if (!empty($request->search)) {
                    $query->where('name', 'like', '%' . $request->search . '%');
                }


                if (!empty($request->type)) {
                    $query->where('type', $request->type);
                }

                $properties = $query->orderBy('id', 'desc')->paginate(12);
                $html = view('property.property_list', compact('properties'))->render();

                return response()->json(['html' => $html]);
            }

            $properties = scopeLandlord(Property::where('parent_id', parentId()), 'id')->orderBy('id', 'desc')->paginate(12);
            $types = Property::types();

            return view('property.index', compact('types', 'properties'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create property')) {
            $types = Property::types();
            $unitTypes = PropertyUnit::type();
            $rentTypes = PropertyUnit::rentTypes();
            $amenities = Amenity::where('parent_id', parentId())->orderBy('id', 'desc')->get();
            $advantages = Advantage::where('parent_id', parentId())->orderBy('id', 'desc')->get();

            return view('property.create', compact('types', 'rentTypes', 'unitTypes', 'amenities', 'advantages'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function store(Request $request)
    {
        if (!\Auth::user()->can('create property')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'type' => 'required',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'zip_code' => 'required',
            'address' => 'required',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'property_images.*' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'msg' => $validator->getMessageBag()->first(),
            ]);
        }

        $pricing_feature_settings = getSettingsValByIdName(1, 'pricing_feature');
        if ($pricing_feature_settings == 'on') {
            $authUser = User::find(parentId());
            $subscription = Subscription::find($authUser->subscription);
            if ($subscription && $subscription->property_limit != 0 && $authUser->totalProperty() >= $subscription->property_limit) {
                return response()->json([
                    'status' => 'error',
                    'msg' => __('Your property limit is over, please upgrade your subscription.'),
                ]);
            }
        }

        try {
            \DB::beginTransaction();

            $property = new Property();
            $property->name = $request->name;
            $property->description = $request->description;
            $property->type = $request->type;
            $property->amenities_id = is_array($request->amenities) ? implode(',', $request->amenities) : null;
            $property->advantage_id = is_array($request->advantages) ? implode(',', $request->advantages) : null;
            $property->country = $request->country;
            $property->state = $request->state;
            $property->city = $request->city;
            $property->zip_code = $request->zip_code;
            $property->listing_type = $request->listing_type;
            $property->price = !empty($request->price) ? $request->price : 0;
            $property->address = $request->address;
            $property->parent_id = parentId();
            $property->save();

            if ($request->hasFile('thumbnail')) {
                $uploadResult = handleFileUpload($request->file('thumbnail'), 'upload/property/thumbnail/');

                if ($uploadResult['flag'] == 0) {
                    \DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'msg' => $uploadResult['msg'],
                    ]);
                }

                PropertyImage::create([
                    'property_id' => $property->id,
                    'image' => $uploadResult['filename'],
                    'type' => 'thumbnail',
                ]);
            }

            if ($request->hasFile('property_images')) {
                foreach ($request->file('property_images') as $image) {
                    $uploadResult = handleFileUpload($image, 'upload/property/image/');

                    if ($uploadResult['flag'] == 0) {
                        \DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'msg' => $uploadResult['msg'],
                        ]);
                    }

                    PropertyImage::create([
                        'property_id' => $property->id,
                        'image' => $uploadResult['filename'],
                        'type' => 'extra',
                    ]);
                }
            }

            if (!empty($request->unitname) && !empty($request->bedroom) && !empty($request->kitchen)) {
                $unit = new PropertyUnit();
                $unit->name = $request->unitname;
                $unit->bedroom = $request->bedroom;
                $unit->kitchen = $request->kitchen;
                $unit->baths = !empty($request->baths) ? $request->baths : 0;
                $unit->rent = !empty($request->rent) ? $request->rent : 0;
                $unit->rent_type = $request->rent_type;

                if ($request->rent_type == 'custom') {
                    $unit->start_date = $request->start_date;
                    $unit->end_date = $request->end_date;
                    $unit->payment_due_date = $request->payment_due_date;
                } else {
                    $unit->rent_duration = $request->rent_duration;
                }

                $unit->deposit_type = !empty($request->deposit_type) ? $request->deposit_type : null;
                $unit->deposit_amount = !empty($request->deposit_amount) ? $request->deposit_amount : 0;
                $unit->late_fee_type = !empty($request->late_fee_type) ? $request->late_fee_type : null;
                $unit->late_fee_amount = !empty($request->late_fee_amount) ? $request->late_fee_amount : 0;
                $unit->incident_receipt_amount = !empty($request->incident_receipt_amount) ? $request->incident_receipt_amount : 0;
                $unit->notes = $request->notes;
                $unit->property_id = $property->id;
                $unit->is_occupied = 0;
                $unit->parent_id = parentId();
                $unit->save();
            }

            \DB::commit();

            return response()->json([
                'status' => 'success',
                'msg' => __('Property successfully created.'),
                'id' => \Crypt::encrypt($property->id),
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'status' => 'error',
                'msg' => __('Something went wrong: ') . $e->getMessage(),
            ]);
        }
    }

    // "Write with AI" for the listing description (template draft when AI is not available)
    public function aiDescription(Request $request)
    {
        if (!\Auth::user()->can('create property') && !\Auth::user()->can('edit property')) {
            return response()->json(['error' => __('Permission Denied!')], 403);
        }
        if (!empty($request->property_id) && !$this->findOwnProperty($request->property_id)) {
            return response()->json(['error' => __('Property not found.')], 404);
        }
        $facts = \App\Support\ListingWriter::facts($request->all(), parentId());
        if ($facts['name'] === '' && $facts['city'] === '' && empty($facts['units'])) {
            return response()->json(['error' => __('Fill in at least the property name and location first.')], 422);
        }
        $result = \App\Support\ListingWriter::write($facts, $request->tone, $request->length, $request->language, parentId(), settings()['CURRENCY_SYMBOL'] ?? '');
        return response()->json($result);
    }

    public function show($ids)
    {
        if (\Auth::user()->can('show property')) {
            $id = Crypt::decrypt($ids);
            $property = $this->findOwnProperty($id);
            if (!$property) {
                return redirect()->back()->with('error', __('Property not found.'));
            }
            $units = PropertyUnit::where('property_id', $property->id)->orderBy('id', 'desc')->get();

            $selectedAmenities = collect();
            if (!empty($property->amenities_id)) {
                $ids = array_filter(explode(',', $property->amenities_id));
                $selectedAmenities = Amenity::whereIn('id', $ids)->get();
            }

            $selectedAdvantages = collect();
            if (!empty($property->advantage_id)) {
                $ids = array_filter(explode(',', $property->advantage_id));
                $selectedAdvantages = Advantage::whereIn('id', $ids)->get();
            }
            $expenses = Expense::where('parent_id', parentId())->where('property_id', $property->id)->get();

            return view('property.show', compact('property', 'units', 'selectedAmenities', 'selectedAdvantages','expenses'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function edit($ids)
    {
        if (\Auth::user()->can('edit property')) {
            $id = Crypt::decrypt($ids);
            $property = $this->findOwnProperty($id);
            if (!$property) {
                return redirect()->back()->with('error', __('Property not found.'));
            }
            $types = Property::types();


            $amenities = Amenity::where('parent_id', parentId())->orderBy('id', 'desc')->get();
            $selectedAmenities = explode(',', $property->amenities_id);

            $advantages = Advantage::where('parent_id', parentId())->orderBy('id', 'desc')->get();
            $selectedAdvantages = explode(',', $property->advantage_id);

            return view('property.edit', compact('types', 'property', 'amenities', 'selectedAmenities', 'advantages', 'selectedAdvantages'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function update(Request $request, Property $property)
    {
        if (!\Auth::user()->can('edit property') || $property->parent_id != parentId()) {
            return response()->json([
                'status' => 'error',
                'msg' => __('Permission Denied!'),
            ]);
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'type' => 'required',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'zip_code' => 'required',
            'address' => 'required',
            'property_images.*' => 'nullable|file|mimes:jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'msg' => $validator->getMessageBag()->first(),
            ]);
        }

        try {
            \DB::beginTransaction();

            $property->name = $request->name;
            $property->description = $request->description;
            $property->type = $request->type;
            $property->amenities_id = is_array($request->amenities) ? implode(',', $request->amenities) : null;
            $property->advantage_id = is_array($request->advantages) ? implode(',', $request->advantages) : null;
            $property->country = $request->country;
            $property->state = $request->state;
            $property->city = $request->city;
            $property->zip_code = $request->zip_code;
            $property->listing_type = $request->listing_type;
            $property->price = !empty($request->price) ? $request->price : 0;
            $property->address = $request->address;
            $property->save();

            if ($request->hasFile('thumbnail')) {
                $uploadResult = handleFileUpload($request->file('thumbnail'), 'upload/property/thumbnail/');

                if ($uploadResult['flag'] == 0) {
                    \DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'msg' => $uploadResult['msg'],
                    ]);
                }

                $oldThumbnails = PropertyImage::where('property_id', $property->id)
                    ->where('type', 'thumbnail')
                    ->get();

                foreach ($oldThumbnails as $old) {
                    deleteOldFile($old->image, 'upload/property/thumbnail/');
                    $old->delete();
                }

                PropertyImage::create([
                    'property_id' => $property->id,
                    'image' => $uploadResult['filename'],
                    'type' => 'thumbnail',
                ]);
            }

            if ($request->input('dropzone_loaded')) {
                $keptExistingIds = array_filter($request->input('existing_images', []));

                PropertyImage::where('property_id', $property->id)
                    ->where('type', 'extra')
                    ->get()
                    ->each(function ($img) use ($keptExistingIds) {
                        if (!in_array($img->id, $keptExistingIds)) {
                            deleteOldFile($img->image, 'upload/property/image/');
                            $img->delete();
                        }
                    });
            }

            if ($request->hasFile('property_images')) {
                foreach ($request->file('property_images') as $image) {
                    $uploadResult = handleFileUpload($image, 'upload/property/image/');

                    if ($uploadResult['flag'] == 0) {
                        \DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'msg' => $uploadResult['msg'],
                        ]);
                    }

                    PropertyImage::create([
                        'property_id' => $property->id,
                        'image' => $uploadResult['filename'],
                        'type' => 'extra',
                    ]);
                }
            }

            \DB::commit();

            return response()->json([
                'status' => 'success',
                'msg' => __('Property successfully updated.'),
                'id' => \Crypt::encrypt($property->id),
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'status' => 'error',
                'msg' => __('Something went wrong: ') . $e->getMessage(),
            ]);
        }
    }


    public function destroy(Property $property)
    {
        if (\Auth::user()->can('delete property') && $property->parent_id == parentId()) {
            if (PropertyUnit::where('property_id', $property->id)->where('is_occupied', 1)->exists()) {
                return redirect()->back()->with('error', __('Property has occupied units and cannot be deleted.'));
            }

            $documents = PropertyImage::where('property_id', $property->id)->get();
            foreach ($documents as $doc) {
                if (!empty($doc->image)) {
                    $folder = $doc->type == 'thumbnail' ? 'upload/property/thumbnail/' : 'upload/property/image/';
                    deleteOldFile($doc->image, $folder);
                }
                $doc->delete();
            }
            PropertyUnit::where('property_id', $property->id)->delete();
            $property->delete();

            return redirect()->back()->with('success', 'Property successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function units(Request $request)
    {
        if (!\Auth::user()->can('manage unit')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        if ($request->ajax()) {
            $query = scopeLandlord(PropertyUnit::where('parent_id', parentId()));

            if (!empty($request->property)) {
                $query->where('property_id', $request->property);
            }

            if (!empty($request->tenant)) {
                $query->whereHas('tenantRelation', function ($q) use ($request) {
                    $q->whereHas('user', function ($q2) use ($request) {
                        $q2->where('id', $request->tenant);
                    });
                });
            }

            if ($request->status !== null && $request->status !== '') {
                $query->where('is_occupied', $request->status);
            }

            $units = $query->get();

            return response()->json([
                'html' => view('unit.unit_list', compact('units'))->render()
            ]);
        }
        $properties = scopeLandlord(Property::where('parent_id', parentId()), 'id')->pluck('name', 'id');
        $properties->prepend(__('Select Property'), '');

        $tenants = User::where('parent_id', parentId())->where('type', 'tenant')
            ->when(landlordPropertyIds() !== null, fn($q) => $q->whereIn('id', scopeLandlord(Tenant::where('parent_id', parentId()), 'property')->pluck('user_id')))
            ->get()->pluck('full_name', 'id');
        $tenants->prepend(__('Select tenants'), '');

        $status = [
            '' => __('Select Status'),
            '1' => __('Occupied'),
            '0' => __('Vacant'),
        ];
        return view('unit.index', compact('properties', 'tenants', 'status'));
    }

    public function unitCreate($property_id)
    {
        if (!\Auth::user()->can('create unit') || !$this->findOwnProperty($property_id)) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $types = PropertyUnit::type();
        $rentTypes = PropertyUnit::rentTypes();
        return view('unit.create', compact('types', 'property_id', 'rentTypes'));
    }

    public function unitStore(Request $request, $property_id)
    {

        if (\Auth::user()->can('create unit') && $this->findOwnProperty($property_id)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'bedroom' => 'required',
                    'kitchen' => 'required',
                    'baths' => 'required',
                    'rent' => 'required',
                    'rent_type' => 'required',
                    'deposit_type' => 'required',
                    'deposit_amount' => 'required',
                    'late_fee_type' => 'required',
                    'late_fee_amount' => 'required',
                    'incident_receipt_amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $unit = new PropertyUnit();
            $unit->name = $request->name;
            $unit->bedroom = $request->bedroom;
            $unit->kitchen = $request->kitchen;
            $unit->baths = $request->baths;
            $unit->rent = $request->rent;
            $unit->rent_type = $request->rent_type;
            if ($request->rent_type == 'custom') {
                $unit->start_date = $request->start_date;
                $unit->end_date = $request->end_date;
                $unit->payment_due_date = $request->payment_due_date;
            } else {
                $unit->rent_duration = $request->rent_duration;
            }

            $unit->deposit_type = $request->deposit_type;
            $unit->deposit_amount = $request->deposit_amount;
            $unit->late_fee_type = $request->late_fee_type;
            $unit->late_fee_amount = $request->late_fee_amount;
            $unit->incident_receipt_amount = $request->incident_receipt_amount;
            $unit->notes = $request->notes;
            $unit->property_id = $property_id;
            $unit->is_occupied = 0;
            $unit->parent_id = parentId();
            $unit->save();
            return redirect()->back()->with('success', __('Unit successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function unitdirectCreate()
    {
        if (!\Auth::user()->can('create unit')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $name = Property::where('parent_id', parentId())->pluck('name', 'id');
        $types = PropertyUnit::type();
        $rentTypes = PropertyUnit::rentTypes();
        return view('unit.directcreate', compact('types', 'rentTypes', 'name'));
    }

    public function unitdirectStore(Request $request)
    {
        if (\Auth::user()->can('create unit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'property_id' => 'required',
                    'bedroom' => 'required',
                    'kitchen' => 'required',
                    'baths' => 'required',
                    'rent' => 'required',
                    'rent_type' => 'required',
                    'deposit_type' => 'required',
                    'deposit_amount' => 'required',
                    'late_fee_type' => 'required',
                    'late_fee_amount' => 'required',
                    'incident_receipt_amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            if (!$this->findOwnProperty($request->property_id)) {
                return redirect()->back()->with('error', __('Property not found.'));
            }

            $unit = new PropertyUnit();
            $unit->name = $request->name;
            $unit->property_id = $request->property_id;
            $unit->bedroom = $request->bedroom;
            $unit->kitchen = $request->kitchen;
            $unit->baths = $request->baths;
            $unit->rent = $request->rent;
            $unit->rent_type = $request->rent_type;
            if ($request->rent_type == 'custom') {
                $unit->start_date = $request->start_date;
                $unit->end_date = $request->end_date;
                $unit->payment_due_date = $request->payment_due_date;
            } else {
                $unit->rent_duration = $request->rent_duration;
            }

            $unit->deposit_type = $request->deposit_type;
            $unit->deposit_amount = $request->deposit_amount;
            $unit->late_fee_type = $request->late_fee_type;
            $unit->late_fee_amount = $request->late_fee_amount;
            $unit->incident_receipt_amount = $request->incident_receipt_amount;
            $unit->notes = $request->notes;
            $unit->is_occupied = 0;
            $unit->parent_id = parentId();
            $unit->save();
            return redirect()->back()->with('success', __('Unit successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function unitEdit($property_id, $unit_id)
    {
        $unit = $this->findOwnUnit($unit_id);
        if (!\Auth::user()->can('edit unit') || !$unit) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $types = PropertyUnit::type();
        $rentTypes = PropertyUnit::rentTypes();
        $rentSuggestion = \App\Support\RentSuggestion::forUnit($unit);
        return view('unit.edit', compact('types', 'property_id', 'rentTypes', 'unit', 'rentSuggestion'));
    }

    public function unitShow($unit_id)
    {

        if (\Auth::user()->can('show unit')) {

            $uid = Crypt::decrypt($unit_id);
            $unit = $this->findOwnUnit($uid);
            if (!$unit) {
                return redirect()->back()->with('error', __('Unit not found.'));
            }


            $invoices = Invoice::where('parent_id', parentId())->where('unit_id', $unit->id)->orderBy('id', 'desc')->get();
            $expenses = Expense::where('parent_id', parentId())->where('unit_id', $unit->id)->orderBy('id', 'desc')->get();

            $rentSuggestion = \App\Support\RentSuggestion::forUnit($unit);
            return view('unit.show', compact('unit', 'invoices', 'expenses', 'rentSuggestion'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function unitUpdate(Request $request, $property_id, $unit_id)
    {
        if (\Auth::user()->can('edit unit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'bedroom' => 'required',
                    'kitchen' => 'required',
                    'baths' => 'required',
                    'rent' => 'required',
                    'rent_type' => 'required',
                    'deposit_type' => 'required',
                    'deposit_amount' => 'required',
                    'late_fee_type' => 'required',
                    'late_fee_amount' => 'required',
                    'incident_receipt_amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $unit = $this->findOwnUnit($unit_id);
            if (!$unit) {
                return redirect()->back()->with('error', __('Unit not found.'));
            }
            $unit->name = $request->name;
            $unit->bedroom = $request->bedroom;
            $unit->kitchen = $request->kitchen;
            $unit->baths = $request->baths;
            $unit->rent = $request->rent;
            $unit->rent_type = $request->rent_type;
            if ($request->rent_type == 'custom') {
                $unit->start_date = $request->start_date;
                $unit->end_date = $request->end_date;
                $unit->payment_due_date = $request->payment_due_date;
            } else {
                $unit->rent_duration = $request->rent_duration;
            }

            $unit->deposit_type = $request->deposit_type;
            $unit->deposit_amount = $request->deposit_amount;
            $unit->late_fee_type = $request->late_fee_type;
            $unit->late_fee_amount = $request->late_fee_amount;
            $unit->incident_receipt_amount = $request->incident_receipt_amount;
            $unit->notes = $request->notes;
            $unit->save();
            return redirect()->back()->with('success', __('Unit successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function unitDestroy($property_id, $unit_id)
    {
        if (\Auth::user()->can('delete unit')) {
            $unit = $this->findOwnUnit($unit_id);
            if (!$unit) {
                return redirect()->back()->with('error', __('Unit not found.'));
            }
            if ($unit->is_occupied == 1) {
                return redirect()->back()->with('error', __('Unit is occupied and cannot be deleted.'));
            }
            $unit->delete();
            return redirect()->back()->with('success', 'Unit successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function getPropertyUnit($property_id)
    {
        $units = PropertyUnit::where('parent_id', parentId())->where('property_id', $property_id)->get()->pluck('name', 'id');
        return response()->json($units);
    }

    public function fileDestroy($id)
    {
        $images = PropertyImage::findOrFail($id);
        if (!\Auth::user()->can('edit property') || !$this->findOwnProperty($images->property_id)) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        // Delete file from storage
        if (!empty($images->image)) {
            deleteOldFile($images->image, 'upload/property/image/');
        }

        $images->delete();

        return redirect()->back()->with('success', 'Image deleted successfully.');
    }
}
