<?php

namespace App\Http\Controllers;

use App\Models\RentIncrease;
use App\Models\Tenant;
use App\Models\TenantChecklistItem;
use App\Support\MoveInChecklist;
use App\Support\Notify;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Tenant move-in checklist ticks and rent increase notices.
 */
class TenantLifecycleController extends Controller
{
    private function tenantFor($id)
    {
        if (!\Auth::user()->can('edit tenant') || in_array(\Auth::user()->type, ['tenant', 'maintainer', 'landlord'])) {
            return null;
        }
        return Tenant::where('parent_id', parentId())->where('id', (int) $id)->first();
    }

    // ------------------------------------------------------------------ move-in checklist

    public function toggleChecklist(Request $request, $tenantId, $item)
    {
        $tenant = $this->tenantFor($tenantId);
        if (!$tenant || !array_key_exists($item, MoveInChecklist::definitions())) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $row = TenantChecklistItem::firstOrNew(['tenant_id' => $tenant->id, 'item' => $item]);
        $row->parent_id = parentId();
        if ($row->done_at) {
            $row->done_at = null;
            $row->done_by = null;
            $row->note = null;
        } else {
            $row->done_at = now();
            $row->done_by = \Auth::id();
            $row->note = mb_substr(trim((string) $request->note), 0, 190) ?: null;
        }
        $row->save();
        return redirect()->to(route('tenant.show', \Crypt::encrypt($tenant->id)) . '#movein')->with('success', __('Checklist updated.'));
    }

    // ------------------------------------------------------------------ rent increases

    public function increaseCreate($tenantId)
    {
        $tenant = $this->tenantFor($tenantId);
        if (!$tenant || !$tenant->units) {
            return response(__('This tenant has no unit.'), 422);
        }
        return view('tenant.rent_increase_create', ['tenant' => $tenant, 'unit' => $tenant->units]);
    }

    public function increaseStore(Request $request, $tenantId)
    {
        $tenant = $this->tenantFor($tenantId);
        $unit = $tenant ? $tenant->units : null;
        if (!$tenant || !$unit) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $newRent = round((float) $request->new_rent, 2);
        if ($newRent <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->effective_date) || $request->effective_date <= date('Y-m-d')) {
            return redirect()->back()->with('error', __('Enter the new rent and an effective date in the future.'));
        }
        if (RentIncrease::where('tenant_id', $tenant->id)->where('status', 'scheduled')->exists()) {
            return redirect()->back()->with('error', __('This tenant already has a scheduled rent change. Cancel it first.'));
        }

        $increase = RentIncrease::create([
            'parent_id' => parentId(),
            'tenant_id' => $tenant->id,
            'unit_id' => $unit->id,
            'old_rent' => (float) $unit->rent,
            'new_rent' => $newRent,
            'effective_date' => $request->effective_date,
            'notes' => mb_substr(trim((string) $request->notes), 0, 2000) ?: null,
            'status' => 'scheduled',
            'created_by' => \Auth::id(),
        ]);

        $message = __('Rent change scheduled for :date.', ['date' => dateFormat($increase->effective_date)]);
        if ($request->boolean('notify')) {
            $this->sendNotice($increase);
            $message .= ' ' . __('The tenant has been notified.');
        }
        $days = Carbon::today()->diffInDays(Carbon::parse($increase->effective_date));
        if ($days < 30) {
            $message .= ' ' . __('Note: that is only :days days notice; check the notice period your lease and local rules require.', ['days' => $days]);
        }
        return redirect()->to(route('tenant.show', \Crypt::encrypt($tenant->id)) . '#rent')->with('success', $message);
    }

    private function sendNotice(RentIncrease $increase)
    {
        $tenant = $increase->tenant;
        $user = optional($tenant)->user;
        if (!$user) {
            return;
        }
        $settings = settings();
        $line = __('Your rent for :unit will change from :old to :new from :date.', [
            'unit' => trim(optional($tenant->properties)->name . ' ' . optional($increase->unit)->name),
            'old' => priceFormat($increase->old_rent),
            'new' => priceFormat($increase->new_rent),
            'date' => dateFormat($increase->effective_date),
        ]);
        commonEmailSend($user->email, [
            'module' => 'rent_increase',
            'subject' => __('Rent change notice'),
            'message' => '<p>' . __('Dear :name,', ['name' => e($user->name)]) . '</p><p>' . e($line) . '</p>'
                . ($increase->notes ? '<p>' . nl2br(e($increase->notes)) . '</p>' : '')
                . '<p>' . __('If you have any questions, please contact us at :email.', ['email' => e($settings['company_email'] ?? '')]) . '</p>'
                . '<p>' . e($settings['company_name'] ?? '') . '</p>',
            'logo' => $settings['company_logo'] ?? '',
            'parent_id' => parentId(),
        ]);
        if ($user->phone_number) {
            send_twilio_msg($user->phone_number, $line, parentId());
        }
        Notify::push($user->id, __('Rent change notice'), $line, route('dashboard'));
        $increase->notice_sent_at = now();
        $increase->save();
    }

    public function increaseCancel($tenantId, $increaseId)
    {
        $tenant = $this->tenantFor($tenantId);
        $increase = $tenant ? RentIncrease::where('tenant_id', $tenant->id)->where('id', (int) $increaseId)->where('status', 'scheduled')->first() : null;
        if (!$increase) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $increase->status = 'cancelled';
        $increase->save();
        return redirect()->to(route('tenant.show', \Crypt::encrypt($tenant->id)) . '#rent')->with('success', __('Rent change cancelled.'));
    }

    // printable notice letter
    public function increaseLetter($tenantId, $increaseId)
    {
        $tenant = $this->tenantFor($tenantId);
        $increase = $tenant ? RentIncrease::where('tenant_id', $tenant->id)->where('id', (int) $increaseId)->first() : null;
        if (!$increase) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $settings = settings();
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('tenant.rent_increase_pdf', compact('tenant', 'increase', 'settings'))
            ->setPaper('a4')->download('rent-change-notice-' . $increase->id . '.pdf');
    }
}
