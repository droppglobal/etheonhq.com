<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class BlogController extends Controller
{
    // public function index()
    // {
    //     if (!Auth::user()->can('manage blog')) {
    //         return redirect()->back()->with('error', __('Permission Denied.'));
    //     }
    //     $loginUser = Auth::user();
    //     $blogs = Blog::get();

    //     return view('blog.index', compact('loginUser', 'blogs'));
    // }

    public function create()
    {
        if (\Auth::user()->can('create blog')) {
            return view('blog.create');
        } else {
            $return['status'] = 'error';
            $return['messages'] = __('Permission denied.');
            return response()->json($return);
        }
    }

    public function store(Request $request)
    {

        // dd($request->all());
        if (\Auth::user()->can('create blog')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'image' => 'required|image|mimes:jpeg,png,jpg',
                    'content' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if ($request->image != '') {
                $imageFilenameWithExt = $request->file('image')->getClientOriginalName();
                $imageFilename = pathinfo($imageFilenameWithExt, PATHINFO_FILENAME);
                $imageExtension = $request->file('image')->getClientOriginalExtension();
                $imageFileName = $imageFilename . '_' . time() . '.' . $imageExtension;
                $dir = storage_path('upload/blog/image');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('image')->storeAs('upload/blog/image/', $imageFileName);
            }

            $baseSlug = Str::slug($request->title);
            $slug = $baseSlug;
            $counter = 1;

            while (Blog::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }


            $blog = new Blog();
            $blog->title = $request->title;
            $blog->slug = $slug;
            $blog->content = $request->content;
            $blog->enabled = $request->enabled;
            $blog->image = !empty($imageFileName) ? $imageFileName : '';
            $blog->parent_id = \Auth::user()->id;
            $blog->save();

            return redirect()->back()->with('success', __('Blog successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(Blog $blog)
    {
        if (\Auth::user()->can('edit blog')) {
            return view('blog.edit', compact('blog'));
        } else {
            $return['status'] = 'error';
            $return['messages'] = __('Permission denied.');
            return response()->json($return);
        }
    }
    public function update(Request $request, Blog $blog)
    {
        if (\Auth::user()->can('edit blog')) {
            $rules = [
                'title' => 'required',
                'content' => 'required',
            ];

            if (empty($blog->image) && !$request->hasFile('image')) {
                return redirect()->back()->with('error', __('Image is required.'));
            }

            if ($request->hasFile('image')) {
                $rules['image'] = 'image|mimes:jpeg,png,jpg';
            }

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            if ($request->hasFile('image')) {
                $imageFilenameWithExt = $request->file('image')->getClientOriginalName();
                $imageFilename = pathinfo($imageFilenameWithExt, PATHINFO_FILENAME);
                $imageExtension = $request->file('image')->getClientOriginalExtension();
                $imageFileName = $imageFilename . '_' . time() . '.' . $imageExtension;
                $dir = storage_path('upload/blog/image');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('image')->storeAs('upload/blog/image/', $imageFileName);
                $blog->image = $imageFileName;
            }

            if ($request->title !== $blog->title) {
                $baseSlug = Str::slug($request->title);
                $slug = $baseSlug;
                $counter = 1;

                while (Blog::where('slug', $slug)->where('id', '!=', $blog->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }
                $blog->slug = $slug;
            }


            $blog->title = $request->title;
            $blog->content = $request->content;
            $blog->enabled = $request->enabled;
            $blog->save();

            return redirect()->back()->with('success', __('Blog successfully updated.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function destroy(Blog $blog)
    {
        if (\Auth::user()->can('delete blog')) {

            // Delete Image
            $imagePath = storage_path('upload/blog/image/' . $blog->image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            $blog->delete();

            return redirect()->back()->with('success', 'Blog successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
