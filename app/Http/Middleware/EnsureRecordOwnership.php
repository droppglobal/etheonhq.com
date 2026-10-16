<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Multi-tenancy guard for route model binding.
 *
 * Every account's records carry the owner's id in `parent_id`. Controllers that
 * receive a bound model (e.g. destroy(Expense $expense)) often only check the
 * permission name, so without this an owner could open, edit or delete another
 * owner's record by changing the id in the URL.
 */
class EnsureRecordOwnership
{
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();

        if ($route && \Auth::check() && in_array('auth', $route->gatherMiddleware())) {
            foreach ($route->parameters() as $parameter) {
                if (!$parameter instanceof Model) {
                    continue;
                }

                $attributes = $parameter->getAttributes();
                if (!array_key_exists('parent_id', $attributes)) {
                    continue;
                }

                // personal records (contacts, notes) carry the creating user's own id instead
                if ((int) $attributes['parent_id'] !== (int) parentId() && (int) $attributes['parent_id'] !== (int) \Auth::id()) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['status' => 'error', 'msg' => __('Permission Denied!')], 403);
                    }
                    return redirect()->back()->with('error', __('Permission Denied!'));
                }
            }
        }

        return $next($request);
    }
}
