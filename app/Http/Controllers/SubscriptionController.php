<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CouponHistory;
use App\Models\PackageTransaction;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Stripe;

class SubscriptionController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('manage pricing packages')) {
            $subscriptions = Subscription::get();
            $userId = auth()->id();
            $userEmail = auth()->user()->email;
            $userIp = $_SERVER['REMOTE_ADDR'];

            $hasUsedTrial = PackageTransaction::where('user_id', $userId)->where('free_trial', 1)->Where('email', $userEmail)->Where('ip', $userIp)->exists();
               
 
             return view('subscription.index', compact('subscriptions', 'hasUsedTrial'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if (!\Auth::user()->can('create pricing packages')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $intervals = Subscription::$intervals;

        return view('subscription.create', compact('intervals'));
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create pricing packages')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required|unique:subscriptions,title',
                    'package_amount' => 'required',
                    'interval' => 'required',
                    'user_limit' => 'required',
                    'property_limit' => 'required',
                    'tenant_limit' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $subscription = new Subscription();
            $subscription->title = $request->title;
            $subscription->interval = $request->interval;
            $subscription->package_amount = $request->package_amount;
            $subscription->user_limit = $request->user_limit;
            $subscription->property_limit = $request->property_limit;
            $subscription->tenant_limit = $request->tenant_limit;
            $subscription->enabled_logged_history = isset($request->enabled_logged_history) ? 1 : 0;
            $subscription->enabled_openai = isset($request->enabled_openai) ? 1 : 0;
            $subscription->enabled_n8n = isset($request->enabled_n8n) ? 1 : 0;
            $subscription->enabled_custom_domain = isset($request->enabled_custom_domain) ? 1 : 0;
            $subscription->free_trial = isset($request->free_trial) ? 1 : 0;
            $subscription->free_trial_days = !empty($request->free_trial_days) ? $request->free_trial_days : 0;
            $subscription->save();

            return redirect()->route('subscriptions.index')->with('success', __('Subscription successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($ids)
    {
        if (\Auth::user()->can('buy pricing packages')) {
            $id = Crypt::decrypt($ids);
            $subscription = Subscription::find($id);
            $settings = subscriptionPaymentSettings();

            return view('subscription.show', compact('subscription', 'settings'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit(subscription $subscription)
    {
        if (!\Auth::user()->can('edit pricing packages')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $intervals = Subscription::$intervals;

        return view('subscription.edit', compact('intervals', 'subscription'));
    }


    public function update(Request $request, subscription $subscription)
    {

        if (\Auth::user()->can('edit pricing packages')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required|unique:subscriptions,title,' . $subscription->id,
                    'package_amount' => 'required',
                    'interval' => 'required',
                    'user_limit' => 'required',
                    'property_limit' => 'required',
                    'tenant_limit' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $subscription->title = $request->title;
            $subscription->interval = $request->interval;
            $subscription->package_amount = $request->package_amount;
            $subscription->user_limit = $request->user_limit;
            $subscription->property_limit = $request->property_limit;
            $subscription->tenant_limit = $request->tenant_limit;
            $subscription->enabled_logged_history = isset($request->enabled_logged_history) ? 1 : 0;
            $subscription->enabled_openai = isset($request->enabled_openai) ? 1 : 0;
            $subscription->enabled_n8n = isset($request->enabled_n8n) ? 1 : 0;
            $subscription->enabled_custom_domain = isset($request->enabled_custom_domain) ? 1 : 0;
            $subscription->free_trial = isset($request->free_trial) ? 1 : 0;
            $subscription->free_trial_days = !empty($request->free_trial_days) ? $request->free_trial_days : 0;
            $subscription->save();

            return redirect()->route('subscriptions.index')->with('success', __('Subscription successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(subscription $subscription)
    {
        if (\Auth::user()->can('delete pricing packages')) {
            $subscription->delete();

            return redirect()->route('subscriptions.index')->with('success', __('Subscription successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function transaction()
    {
        if (\Auth::user()->can('manage pricing transation')) {
            if (\Auth::user()->type == 'super admin') {
                $transactions = PackageTransaction::orderBy('created_at', 'DESC')->get();
            } else {
                $transactions = PackageTransaction::where('user_id', \Auth::user()->id)->orderBy('created_at', 'DESC')->get();
            }
            $settings = settings();
            return view('subscription.transaction', compact('transactions', 'settings'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function stripePayment(Request $request, $ids)
    {
        if (\Auth::user()->can('buy pricing packages')) {
            $settings = subscriptionPaymentSettings();
            $authUser = \Auth::user();
            $id = \Illuminate\Support\Facades\Crypt::decrypt($ids);
            $subscription = Subscription::find($id);
            if ($subscription) {
                try {
                    $amount = Coupon::couponApply($id, $request->coupon);
                    $packageTransId = uniqid('', true);
                    if ($amount > 0) {
                        Stripe\Stripe::setApiKey($settings['STRIPE_SECRET']);
                        $data = Stripe\Charge::create(
                            [
                                "amount" => 100 * $amount,
                                "currency" => $settings['CURRENCY'],
                                "source" => $request->stripeToken,
                                "description" => " Subscription - " . $subscription->name,
                                "metadata" => ["package_transaction_id" => $packageTransId],
                                'address' => [
                                    'line1' => $request->state ?? 'NA',
                                    'city' => $request->city ?? 'NA',
                                    'postal_code' => $request->zipcode ?? '000000',
                                    'country' => $request->country ?? 'NA',
                                ]
                            ]
                        );
                    } else {
                        $data['amount_refunded'] = 0;
                        $data['failure_code'] = '';
                        $data['paid'] = 1;
                        $data['captured'] = 1;
                        $data['status'] = 'succeeded';
                    }

                    if ($data['amount_refunded'] == 0 && empty($data['failure_code']) && $data['paid'] == 1 && $data['captured'] == 1) {

                        if ($data['status'] == 'succeeded') {
                            $data['holder_name'] = $request->name;
                            $data['subscription_id'] = $subscription->id;
                            $data['amount'] = $amount;
                            $data['subscription_transactions_id'] = $packageTransId;
                            $data['payment_type'] = 'Stripe';
                            PackageTransaction::transactionData($data);

                            if ($subscription->couponCheck() > 0 && !empty($request->coupon)) {
                                $couhis['coupon'] = $request->coupon;
                                $couhis['package'] = $subscription->id;
                                CouponHistory::couponData($couhis);
                            }

                            $assignPlan = assignSubscription($subscription->id);
                            if ($assignPlan['is_success']) {
                                return redirect()->route('subscriptions.index')->with('success', __('Subscription activate successfully.'));
                            } else {
                                return redirect()->route('subscriptions.index')->with('error', __($assignPlan['error']));
                            }
                        } else {
                            return redirect()->route('subscriptions.index')->with('error', __('Your payment failed.'));
                        }
                    } else {
                        return redirect()->route('subscriptions.index')->with('error', __('Transaction failed.'));
                    }
                } catch (\Exception $e) {
                    return redirect()->route('subscriptions.index')->with('error', __($e->getMessage()));
                }
            } else {
                return redirect()->route('subscriptions.index')->with('error', __('Subscription is not found.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function freeTrial(Request $request, $ids, $trial)
    {

        if (!\Auth::user()->can('buy pricing packages')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        if ($trial == 1) {
            $id = decrypt($ids);
            $subscription = Subscription::find($id);
            if (!$subscription || $subscription->free_trial != 1) {
                return redirect()->route('subscriptions.index')->with('error', __('Free trial is not available for this package.'));
            }
            $packageTransId = uniqid('', true);

            $userId = auth()->id();
            $userEmail = auth()->user()->email;
            $userIp = $_SERVER['REMOTE_ADDR'];

            $hasUsedTrial = PackageTransaction::where('free_trial', 1)
                ->where(function ($query) use ($userId, $userEmail, $userIp) {
                    $query->where('user_id', $userId)->orWhere('email', $userEmail)->orWhere('ip', $userIp);
                })
                ->exists();
            if ($hasUsedTrial) {
                return redirect()->route('subscriptions.index')->with('error', __('Free trial has already been used.'));
            }

            $data['holder_name'] = $request->name;
            $data['subscription_id'] = $subscription->id;
            $data['amount'] = 0;
            $data['subscription_transactions_id'] = $packageTransId;
            $data['payment_type'] = 'Trial';
            $data['status'] = 'trial';
            $data['free_trial'] = 1;
            $data['ip'] = $_SERVER['REMOTE_ADDR'];
            $data['email'] = auth()->user()->email;

            PackageTransaction::transactionData($data);

            $trialDays = $subscription->free_trial_days ?? 0;

            $user = User::find(auth()->id());
            $user->subscription = $subscription->id;
            $user->subscription_expire_date = now()->addDays($trialDays);
            $user->save();

            return redirect()->route('subscriptions.index')->with('success', __('Free trial activated successfully.'));
        }
        return redirect()->route('subscriptions.index');
    }
}
