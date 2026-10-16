<?php

namespace App\Http\Controllers;

use App\Models\Advantage;
use Illuminate\Http\Request;

class AdvantageController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage advantage')) {
            $advantages = Advantage::where('parent_id', parentId())->orderBy('id', 'desc')->get();
            return view('advantage.index', compact('advantages'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
    public function create()
    {
        return view('advantage.create');
    }

    public function store(Request $request)
    {

        // dd($request->all());
        if (\Auth::user()->can('create advantage')) {
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

            $amenity = new Advantage();
            $amenity->name = $request->name;
            $amenity->description = $request->description;
            $amenity->parent_id = parentId();
            $amenity->save();

            return redirect()->back()->with('success', __('Advantage successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function edit(Advantage $advantage)
    {

        return view('advantage.edit', compact('advantage'));
    }

    public function update(Request $request, Advantage $advantage)
    {
        if (\Auth::user()->can('edit advantage')) {
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


            $advantage->name = $request->name;
            $advantage->description = $request->description;
            $advantage->parent_id = parentId();
            $advantage->save();

            return redirect()->back()->with('success', __('Advantage successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function destroy(Advantage $advantage)
    {
        if (\Auth::user()->can('delete advantage')) {

            if ($advantage) {
                $advantage->delete();
            }
            return redirect()->back()->with('success', __('Advantage successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
}
