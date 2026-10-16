<?php

namespace App\Http\Controllers;

use App\Models\Type;
use Illuminate\Http\Request;

class TypeController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage types')) {
            $types = Type::where('parent_id',parentId())->orderBy('id', 'desc')->get();
            return view('type.index', compact('types'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function create()
    {
        $types = Type::type();
        return view('type.create', compact('types'));
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create types')) {
            $validator = \Validator::make(
                $request->all(), [
                'title' => 'required',
                'type' => 'required',

            ],
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $type = new Type();
            $type->title = $request->title;
            $type->type = $request->type;
            $type->parent_id =parentId();
            $type->save();

            return redirect()->back()->with('success', __('Type successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function show(Type $type)
    {
        //
    }


    public function edit(Type $type)
    {

        $types = Type::type();
        return view('type.edit', compact('types', 'type'));
    }


    public function update(Request $request, Type $type)
    {
        if (\Auth::user()->can('edit types')) {
            $validator = \Validator::make(
                $request->all(), [
                'title' => 'required',
                'type' => 'required',

            ],
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }


            $type->title = $request->title;
            $type->type = $request->type;
            $type->parent_id =parentId();
            $type->save();

            return redirect()->back()->with('success', __('Type successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function destroy(Type $type)
    {
        if (\Auth::user()->can('delete types')) {
            $type->delete();
            return redirect()->back()->with('success', 'Type successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
}
