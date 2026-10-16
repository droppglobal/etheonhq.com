<?php

namespace App\Http\Controllers;

use App\Models\WebsiteReview;
use App\Support\WebsiteSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Tenant reviews: tenants write one from their account, the owner decides which appear on the website.
 */
class WebsiteReviewController extends Controller
{
    private function canManage($permission = 'manage front home page')
    {
        return Auth::user()->type !== 'tenant' && Auth::user()->can($permission);
    }

    public function index(Request $request)
    {
        if (!$this->canManage()) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        $status = array_key_exists((string) $request->status, WebsiteReview::statuses()) ? $request->status : '';
        $reviews = WebsiteReview::where('parent_id', parentId())->when($status, fn($q) => $q->where('status', $status))
            ->orderByRaw("status = 'pending' desc")->latest()->get();
        $counts = WebsiteReview::where('parent_id', parentId())->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        return view('website_reviews.index', compact('reviews', 'status', 'counts'));
    }

    public function status(Request $request, $id)
    {
        if (!$this->canManage('edit front home page')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        $request->validate(['status' => 'required|in:' . implode(',', array_keys(WebsiteReview::statuses()))]);
        WebsiteReview::where('parent_id', parentId())->where('id', $id)->firstOrFail()->update(['status' => $request->status]);
        return redirect()->back()->with('success', $request->status === 'approved' ? __('Review is now shown on your website.') : __('Review updated.'));
    }

    public function destroy($id)
    {
        if (!$this->canManage('edit front home page')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        WebsiteReview::where('parent_id', parentId())->where('id', $id)->firstOrFail()->delete();
        return redirect()->back()->with('success', __('Review deleted.'));
    }

    // ---------------------------------------------------------------- tenant

    private function tenantAllowed()
    {
        return Auth::user()->type === 'tenant' && !empty(WebsiteSite::visitors(parentId())['reviews']);
    }

    public function mine()
    {
        if (!$this->tenantAllowed()) {
            return redirect()->route('dashboard')->with('error', __('Permission Denied.'));
        }
        $review = WebsiteReview::where('parent_id', parentId())->where('user_id', Auth::id())->first();
        return view('website_reviews.mine', compact('review'));
    }

    public function save(Request $request)
    {
        if (!$this->tenantAllowed()) {
            return redirect()->route('dashboard')->with('error', __('Permission Denied.'));
        }
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'review' => 'required|string|min:10|max:1000',
            'name' => 'required|string|max:80',
        ]);
        WebsiteReview::updateOrCreate(
            ['parent_id' => parentId(), 'user_id' => Auth::id()],
            ['name' => strip_tags($request->name), 'role' => __('Tenant'), 'rating' => (int) $request->rating,
                'review' => strip_tags($request->review), 'status' => 'pending']
        );
        return redirect()->route('my-review')->with('success', __('Thank you! Your review will appear once it is approved.'));
    }
}
