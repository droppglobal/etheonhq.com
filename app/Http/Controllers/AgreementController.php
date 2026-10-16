<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AgreementController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage agreement')) {
            if (!\Auth::user()->can('manage invoice')) {
                return redirect()->back()->with('error', __('Permission Denied!'));
            }

            $user = \Auth::user();
            $query = Agreement::where('parent_id', parentId())->orderBy('id', 'desc');

            // Role scope
            if ($user->type == 'tenant') {
                $query->where('tenant_id', optional($user->tenants)->id);
            }
            if ($request->ajax()) {

                if (!empty($request->property))
                    $query->where('property_id', $request->property);

                if (!empty($request->unit))
                    $query->where('unit_id', $request->unit);

                $agreements = $query->get();

                return response()->json([
                    'html' => view('agreement.agreement_list', compact('agreements'))->render()
                ]);
            }

            $properties = Property::where('parent_id', parentId())->pluck('name', 'id');
            $properties->prepend(__('Select Property'), '');

            $units = PropertyUnit::where('parent_id', parentId())->pluck('name', 'id');
            $units->prepend(__('Select Unit'), '');


            return view('agreement.index', compact('properties', 'units'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create agreement')) {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
            $property->prepend(__('Select Property'), 0);
            $status = Agreement::status();
            $setting = settings(\Auth::user()->id);
            return view('agreement.create', compact('property', 'status', 'setting'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create agreement')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'property_id' => 'required',
                    'unit_id' => 'required',
                    'status' => 'required',
                    'terms_condition' => 'required',
                    'description' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $agreement = new Agreement();
            $agreement->agreement_id = ($this)->agreementNumber();
            $agreement->property_id = $request->property_id;
            $agreement->tenant_id = $request->tenant_id;
            $agreement->unit_id = $request->unit_id;
            $agreement->date = now();
            $agreement->status = $request->status;
            $agreement->terms_condition = $request->terms_condition;
            $agreement->agreement_start_date = $request->agreement_start_date;
            $agreement->agreement_end_date = $request->agreement_end_date;
            $agreement->description = $request->description;
            $agreement->parent_id = parentId();
            $agreement->save();


            return redirect()->back()->with('success', __('Agreement successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    // Agreement of the current account; a tenant only sees their own.
    private function findVisibleAgreement($id)
    {
        $query = Agreement::where('parent_id', parentId())->where('id', $id);
        if (\Auth::user()->type == 'tenant') {
            $query->where('tenant_id', optional(\Auth::user()->tenants)->id);
        }
        return $query->first();
    }

    public function show($ids)
    {

        if (\Auth::user()->can('show agreement')) {

            $id = Crypt::decrypt($ids);
            $agreement = $this->findVisibleAgreement($id);
            if (!$agreement) {
                return redirect()->back()->with('error', __('Agreement not found.'));
            }

            return view('agreement.show', compact('agreement'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function edit(Agreement $agreement)
    {
        if (\Auth::user()->can('edit agreement')) {

            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
            $property->prepend(__('Select Property'), 0);

            $status = Agreement::status();

            return view('agreement.edit', compact('property', 'status', 'agreement'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function update(Request $request, Agreement $agreement)
    {

        if (\Auth::user()->can('edit agreement')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'property_id' => 'required',
                    'unit_id' => 'required',
                    'date' => 'required',
                    'status' => 'required',
                    'terms_condition' => 'required',
                    'description' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $agreement->property_id = $request->property_id;
            $agreement->tenant_id = $request->tenant_id;
            $agreement->unit_id = $request->unit_id;
            $agreement->date = $request->date;
            $agreement->status = $request->status;
            $agreement->terms_condition = $request->terms_condition;
            $agreement->description = $request->description;
            $agreement->agreement_start_date = $request->agreement_start_date;
            $agreement->agreement_end_date = $request->agreement_end_date;
            $agreement->parent_id = parentId();
            $agreement->save();

            return redirect()->back()->with('success', __('Agreement successfully update.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function destroy(Agreement $agreement)
    {
        if (\Auth::user()->can('delete agreement')) {

            if ($agreement) {
                if (!empty($agreement->attachment)) {
                    deleteOldFile($agreement->attachment, 'upload/attachment/');
                }

                $agreement->delete();
            }
            return redirect()->back()->with('success', __('Agreement successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function agreementNumber()
    {
        $latestAgreement = Agreement::where('parent_id', parentId())->latest()->first();
        if ($latestAgreement == null) {
            return 1;
        } else {
            return $latestAgreement->agreement_id + 1;
        }
    }

    // AgreementController.php
    public function saveTenantSignature(Request $request, $id)
    {
        $agreement = $this->findVisibleAgreement($id);
        // only the tenant named on the agreement can add the tenant signature
        if (!$agreement || \Auth::user()->type != 'tenant' || $agreement->tenant_id != optional(\Auth::user()->tenants)->id) {
            return response()->json([
                'status' => 'false',
                'message' => __('Permission Denied!')
            ], 403);
        }

        $landlordSignature = getSettingsValByName('landlord_signature');
        if (empty($landlordSignature)) {
            return response()->json([
                'status' => 'false',
                'message' => 'Landlord signature is required before tenant can sign'
            ]);
        }

        $request->validate([
            'tenant_signature' => 'required',
        ]);

        if (!empty($agreement->tenant_signature)) {
            return response()->json([
                'status' => 'false',
                'message' => __('This agreement has already been signed.')
            ]);
        }

        $agreement->landlord_signature = $landlordSignature;
        $agreement->landlord_signature_date = \Carbon\Carbon::now(
            getSettingsValByName('timezone')
        )->format('Y-m-d H:i:s');

        $agreement->tenant_signature = $request->tenant_signature;
        $agreement->tenant_signature_date = \Carbon\Carbon::now(
            getSettingsValByName('timezone')
        )->format('Y-m-d H:i:s');
        // audit trail for the electronic signature
        $agreement->tenant_signature_ip = $request->ip();
        $agreement->tenant_signature_agent = substr((string) $request->userAgent(), 0, 500);

        $agreement->save();

        $this->sendSignedCopy($agreement);

        return response()->json(['status' => 'true','message' => 'Saved']);
    }

    private function agreementPdf($agreement)
    {
        $settings = settingsById($agreement->parent_id);
        $tenantUser = optional($agreement->tenants)->user;
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('agreement.pdf', compact('agreement', 'settings', 'tenantUser'))->setPaper('a4')->setOption('isFontSubsettingEnabled', true);
    }

    private function pdfFileName($agreement)
    {
        return 'agreement-' . preg_replace('/[^A-Za-z0-9\-]/', '', agreementPrefix() . $agreement->agreement_id) . '.pdf';
    }

    public function downloadPdf($ids)
    {
        if (!\Auth::user()->can('show agreement')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $agreement = $this->findVisibleAgreement(Crypt::decrypt($ids));
        if (!$agreement) {
            return redirect()->back()->with('error', __('Agreement not found.'));
        }
        return $this->agreementPdf($agreement)->download($this->pdfFileName($agreement));
    }

    // Emails the signed agreement PDF to the tenant and the owner.
    private function sendSignedCopy($agreement)
    {
        try {
            $settings = settingsById($agreement->parent_id);
            $data = [
                'module' => 'agreement_signed',
                'subject' => __('Signed rental agreement') . ' ' . agreementPrefix() . $agreement->agreement_id,
                'message' => '<p>' . __('The rental agreement has been signed by both parties. A copy is attached to this email.') . '</p>',
                'logo' => $settings['company_logo'] ?? '',
                'parent_id' => $agreement->parent_id,
                'settings' => settings(),
                'pdf' => $this->agreementPdf($agreement)->output(),
                'pdf_name' => $this->pdfFileName($agreement),
            ];

            $recipients = array_filter(array_unique([
                optional(optional($agreement->tenants)->user)->email,
                optional(\App\Models\User::find($agreement->parent_id))->email,
            ]));
            if (empty($recipients)) {
                return;
            }

            emailSettings($agreement->parent_id);
            \Illuminate\Support\Facades\Mail::to($recipients)->send(new \App\Mail\AgreementSigned($data));

            $agreement->signed_copy_sent_at = now();
            $agreement->save();
        } catch (\Exception $e) {
            // signing must succeed even when mail is not configured
            \Log::info('Signed agreement email not sent: ' . $e->getMessage());
        }
    }
}
