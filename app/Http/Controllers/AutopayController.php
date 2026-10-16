<?php

namespace App\Http\Controllers;

use App\Support\RazorpayAutopay;
use App\Support\StripeAutopay;
use Illuminate\Http\Request;

/**
 * Tenant-facing page to save a card and switch automatic rent payment on or off.
 */
class AutopayController extends Controller
{
    private function tenant()
    {
        if (\Auth::user()->type != 'tenant') {
            return null;
        }
        return \Auth::user()->tenants;
    }

    public function index()
    {
        $tenant = $this->tenant();
        if (!$tenant) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $stripeAvailable = StripeAutopay::available($tenant->parent_id);
        $razorpayAvailable = RazorpayAutopay::available($tenant->parent_id);
        $available = $stripeAvailable || $razorpayAvailable;
        $stripeKey = $stripeAvailable ? StripeAutopay::settings($tenant->parent_id)['STRIPE_KEY'] : null;
        $maxAmount = $razorpayAvailable ? RazorpayAutopay::maxAmount($tenant) : null;
        return view('autopay.index', compact('tenant', 'available', 'stripeAvailable', 'razorpayAvailable', 'stripeKey', 'maxAmount'));
    }

    public function setupIntent()
    {
        $tenant = $this->tenant();
        if (!$tenant || !StripeAutopay::available($tenant->parent_id)) {
            return response()->json(['error' => __('Permission Denied!')], 403);
        }
        try {
            $intent = StripeAutopay::createSetupIntent($tenant);
            return response()->json(['client_secret' => $intent->client_secret]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function save(Request $request)
    {
        $tenant = $this->tenant();
        if (!$tenant || !StripeAutopay::available($tenant->parent_id) || empty($request->setup_intent)) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        try {
            StripeAutopay::saveFromSetupIntent($tenant, $request->setup_intent);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        return redirect()->route('autopay.index')->with('success', __('Card saved. Your rent will be paid automatically on each due date.'));
    }

    public function disable()
    {
        $tenant = $this->tenant();
        if (!$tenant) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if ($tenant->autopay_gateway == 'razorpay') {
            RazorpayAutopay::disable($tenant);
        } else {
            StripeAutopay::disable($tenant);
        }
        return redirect()->route('autopay.index')->with('success', __('Autopay switched off and card removed.'));
    }

    // Razorpay: order for the one-time card / UPI mandate authorisation
    public function razorpayOrder(Request $request)
    {
        $tenant = $this->tenant();
        if (!$tenant || !RazorpayAutopay::available($tenant->parent_id)) {
            return response()->json(['error' => __('Permission Denied!')], 403);
        }
        try {
            $order = RazorpayAutopay::authorisationOrder($tenant, $request->input('method'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
        $user = \Auth::user();
        return response()->json($order + [
            'name' => $user->name,
            'email' => $user->email,
            'contact' => $user->phone_number,
            'company' => settings()['company_name'] ?? env('APP_NAME'),
        ]);
    }

    public function razorpaySave(Request $request)
    {
        $tenant = $this->tenant();
        if (!$tenant || !RazorpayAutopay::available($tenant->parent_id)) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        try {
            RazorpayAutopay::saveMandate($tenant, $request->razorpay_order_id, $request->razorpay_payment_id, $request->razorpay_signature);
        } catch (\Exception $e) {
            return redirect()->route('autopay.index')->with('error', $e->getMessage());
        }
        return redirect()->route('autopay.index')->with('success', __('Autopay is on. Your rent will be paid automatically on each due date.'));
    }
}
