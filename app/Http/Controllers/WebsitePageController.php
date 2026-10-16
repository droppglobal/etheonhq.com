<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WebsitePage;
use App\Support\WebsiteSections;
use App\Support\WebsiteSite;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Pages of the owner's public website (About, Services, Policies...): created in Frontend Manager > Pages,
 * shown at /web/{code}/page/{slug} and optionally in the website menu.
 */
class WebsitePageController extends Controller
{
    private function find($id)
    {
        return WebsitePage::where('parent_id', parentId())->where('id', $id)->firstOrFail();
    }

    private function allowed($permission = 'manage front home page')
    {
        return \Auth::user()->can($permission);
    }

    public function index()
    {
        if (!$this->allowed()) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $pages = WebsitePage::where('parent_id', parentId())->orderBy('sort_order')->orderBy('id')->get();
        $code = \Auth::user()->code;
        return view('website_pages.index', compact('pages', 'code'));
    }

    public function create()
    {
        if (!$this->allowed('edit front home page')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $page = new WebsitePage(['show_in_menu' => 1, 'is_active' => 1]);
        return view('website_pages.form', ['page' => $page, 'languages' => WebsiteSections::languages(), 'contentLang' => '']);
    }

    public function store(Request $request)
    {
        if (!$this->allowed('edit front home page')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $data = $this->validated($request);
        $data['parent_id'] = parentId();
        $data['sort_order'] = (int) WebsitePage::where('parent_id', parentId())->max('sort_order') + 10;
        WebsitePage::create($data);
        return redirect()->route('website-pages.index')->with('success', __('Page saved.'));
    }

    public function edit(Request $request, $id)
    {
        if (!$this->allowed('edit front home page')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $page = $this->find($id);
        $languages = WebsiteSections::languages();
        $lang = (string) $request->content_lang;
        $contentLang = isset($languages[$lang]) && $lang !== WebsiteSite::design(parentId())['site_language'] ? $lang : '';
        return view('website_pages.form', compact('page', 'languages', 'contentLang'));
    }

    public function update(Request $request, $id)
    {
        if (!$this->allowed('edit front home page')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $page = $this->find($id);
        $languages = WebsiteSections::languages();
        $lang = (string) $request->content_lang;
        if (isset($languages[$lang]) && $lang !== WebsiteSite::design(parentId())['site_language']) {
            // translation: only title and text, the rest is shared
            $request->validate(['title' => 'nullable|string|max:120', 'content' => 'nullable|string|max:200000']);
            $t = json_decode((string) $page->translations, true) ?: [];
            $t[$lang] = ['title' => trim((string) $request->title), 'content' => WebsiteSite::cleanHtml($request->input('content'))];
            $page->translations = json_encode($t, JSON_UNESCAPED_UNICODE);
            $page->save();
            return redirect()->route('website-pages.edit', [$page->id, 'content_lang' => $lang])->with('success', __('Translation saved.'));
        }
        $page->update($this->validated($request, $page->id));
        return redirect()->route('website-pages.index')->with('success', __('Page saved.'));
    }

    public function destroy($id)
    {
        if (!$this->allowed('edit front home page')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $this->find($id)->delete();
        return redirect()->route('website-pages.index')->with('success', __('Page deleted.'));
    }

    private function validated(Request $request, $ignoreId = null)
    {
        $request->validate([
            'title' => 'required|string|max:120',
            'slug' => 'nullable|string|max:120',
            'content' => 'nullable|string|max:200000',
            'meta_title' => 'nullable|string|max:120',
            'meta_description' => 'nullable|string|max:300',
        ]);
        // readable, unique address: "about-us", "about-us-2"...
        $base = Str::slug($request->slug ?: $request->title) ?: 'page';
        $slug = $base;
        $n = 2;
        while (WebsitePage::where('parent_id', parentId())->where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $n++;
        }
        return [
            'title' => trim($request->title),
            'slug' => $slug,
            'content' => WebsiteSite::cleanHtml($request->input('content')),
            'show_in_menu' => $request->boolean('show_in_menu'),
            'is_active' => $request->boolean('is_active'),
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
        ];
    }

    // public page on the owner's website
    public function show($code, $slug)
    {
        $user = User::where('code', $code)->where('type', 'owner')->firstOrFail();
        $page = WebsitePage::where('parent_id', $user->id)->where('slug', $slug)->where('is_active', 1)->firstOrFail();
        $settings = settingsById($user->id);
        $site = WebsiteSite::for($user);
        $text = $page->localized($site->lang);
        return view('theme.page', compact('user', 'settings', 'page', 'site', 'text'));
    }
}
