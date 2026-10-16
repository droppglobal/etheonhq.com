<?php

namespace App\Http\Controllers;

use App\Models\DepositTransaction;
use App\Models\Tenant;
use App\Models\TenantDocument;
use Illuminate\Http\Request;

/**
 * Security deposit ledger and the tenant document vault, both shown on the tenant detail page.
 */
class TenantDepositController extends Controller
{
    private function findOwnTenant($id)
    {
        return Tenant::where('parent_id', parentId())->where('id', $id)->first();
    }

    // ---------------- Deposit ledger ----------------

    public function create($tenantId)
    {
        $tenant = $this->findOwnTenant($tenantId);
        if (!\Auth::user()->can('create deposit') || !$tenant) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $types = DepositTransaction::types();
        $balance = DepositTransaction::balance($tenant->id);
        $unitDeposit = !empty($tenant->units) ? $tenant->units->deposit_amount : 0;
        return view('deposit.create', compact('tenant', 'types', 'balance', 'unitDeposit'));
    }

    public function store(Request $request, $tenantId)
    {
        $tenant = $this->findOwnTenant($tenantId);
        if (!\Auth::user()->can('create deposit') || !$tenant) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $validator = \Validator::make($request->all(), [
            'type' => 'required|in:' . implode(',', array_keys(DepositTransaction::types())),
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        // deductions and refunds come out of what is held
        if ($request->type != 'received' && $request->amount > DepositTransaction::balance($tenant->id)) {
            return redirect()->back()->with('error', __('Amount is more than the deposit held.'));
        }

        DepositTransaction::create([
            'tenant_id' => $tenant->id,
            'property_id' => $tenant->property,
            'unit_id' => $tenant->unit,
            'type' => $request->type,
            'amount' => $request->amount,
            'date' => $request->date,
            'notes' => $request->notes,
            'parent_id' => parentId(),
        ]);

        return redirect()->back()->with('success', __('Deposit entry successfully added.'));
    }

    public function destroy($id)
    {
        $transaction = DepositTransaction::where('parent_id', parentId())->find($id);
        if (!\Auth::user()->can('delete deposit') || !$transaction) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        // removing a "received" entry must not leave more paid out than received
        if ($transaction->type == 'received' && DepositTransaction::balance($transaction->tenant_id) - $transaction->amount < 0) {
            return redirect()->back()->with('error', __('Delete the deductions or refunds first.'));
        }

        $transaction->delete();
        return redirect()->back()->with('success', __('Deposit entry successfully deleted.'));
    }

    // ---------------- Document vault ----------------

    public function documentCreate($tenantId)
    {
        $tenant = $this->findOwnTenant($tenantId);
        if (!\Auth::user()->can('edit tenant') || !$tenant) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $documentTypes = TenantDocument::documentTypes();
        $document = null;
        return view('tenant_document.form', compact('tenant', 'documentTypes', 'document'));
    }

    public function documentStore(Request $request, $tenantId)
    {
        $tenant = $this->findOwnTenant($tenantId);
        if (!\Auth::user()->can('edit tenant') || !$tenant) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $validator = \Validator::make($request->all(), [
            'title' => 'required|max:191',
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'expiry_date' => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $uploadResult = handleFileUpload($request->file('document'), 'upload/tenant/');
        if ($uploadResult['flag'] == 0) {
            return redirect()->back()->with('error', $uploadResult['msg']);
        }

        TenantDocument::create([
            'tenant_id' => $tenant->id,
            'property_id' => $tenant->property,
            'document' => $uploadResult['filename'],
            'title' => $request->title,
            'document_type' => array_key_exists($request->document_type, TenantDocument::documentTypes()) ? $request->document_type : 'other',
            'expiry_date' => $request->expiry_date ?: null,
            'parent_id' => parentId(),
        ]);

        return redirect()->back()->with('success', __('Document successfully uploaded.'));
    }

    public function documentEdit($id)
    {
        $document = TenantDocument::where('parent_id', parentId())->find($id);
        if (!\Auth::user()->can('edit tenant') || !$document) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $tenant = $this->findOwnTenant($document->tenant_id);
        $documentTypes = TenantDocument::documentTypes();
        return view('tenant_document.form', compact('tenant', 'documentTypes', 'document'));
    }

    public function documentUpdate(Request $request, $id)
    {
        $document = TenantDocument::where('parent_id', parentId())->find($id);
        if (!\Auth::user()->can('edit tenant') || !$document) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $validator = \Validator::make($request->all(), [
            'title' => 'required|max:191',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'expiry_date' => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        if ($request->hasFile('document')) {
            $uploadResult = handleFileUpload($request->file('document'), 'upload/tenant/');
            if ($uploadResult['flag'] == 0) {
                return redirect()->back()->with('error', $uploadResult['msg']);
            }
            deleteOldFile($document->document, 'upload/tenant/');
            $document->document = $uploadResult['filename'];
        }

        // a new expiry date needs a new reminder
        if ($document->expiry_date != $request->expiry_date) {
            $document->expiry_reminder_sent = 0;
        }
        $document->title = $request->title;
        $document->document_type = array_key_exists($request->document_type, TenantDocument::documentTypes()) ? $request->document_type : 'other';
        $document->expiry_date = $request->expiry_date ?: null;
        $document->save();

        return redirect()->back()->with('success', __('Document successfully updated.'));
    }
}
