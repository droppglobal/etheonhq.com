<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use Illuminate\Http\Request;

class AmenityController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage amenity')) {
            $amenities = Amenity::where('parent_id', parentId())->orderBy('id', 'desc')->get();
            return view('amenity.index', compact('amenities'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
    public function create()
    {
        return view('amenity.create');
    }


    public function store(Request $request)
    {

        // dd($request->all());
        if (\Auth::user()->can('create amenity')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'description' => 'required',
                    'image' => 'required',

                ],
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $amenity = new Amenity();
            $amenity->name = $request->name;
            $amenity->description = $request->description;

            if ($request->hasFile('image')) {
                $uploadResult = handleFileUpload($request->file('image'), 'upload/amenity/');
                if ($uploadResult['flag'] == 0) {
                    return redirect()->back()->with('error', $uploadResult['msg']);
                }
                $amenity->image = $uploadResult['filename'];
            }

            $amenity->parent_id = parentId();
            $amenity->save();

            return redirect()->back()->with('success', __('Amenity successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function edit(Amenity $amenity)
    {

        return view('amenity.edit', compact('amenity'));
    }


    public function update(Request $request, Amenity $amenity)
    {
        if (\Auth::user()->can('edit amenity')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'description' => 'required',
                ],
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }


            $amenity->name = $request->name;
            $amenity->description = $request->description;

            if ($request->hasFile('image')) {
                $uploadResult = handleFileUpload($request->file('image'), 'upload/amenity/');
                if ($uploadResult['flag'] == 0) {
                    return redirect()->back()->with('error', $uploadResult['msg']);
                }
                $amenity->image = $uploadResult['filename'];
            }

            $amenity->parent_id = parentId();
            $amenity->save();

            return redirect()->back()->with('success', __('Amenity successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function destroy(Amenity $amenity)
    {
        if (\Auth::user()->can('delete amenity')) {

            if ($amenity) {
                if (!empty($amenity->image)) {
                    deleteOldFile($amenity->image, 'upload/amenity/');
                }

                $amenity->delete();
            }
            return redirect()->back()->with('success', __('Amenity successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
}
