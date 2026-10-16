<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Notification;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class InvoiceController extends Controller
{

    // Invoice of the current account; a tenant only sees invoices addressed to them.
    private function findOwnInvoice($id)
    {
        $query = Invoice::where('parent_id', parentId())->where('id', $id);
        if (\Auth::user()->type == 'tenant') {
            $query->where('tenant', optional(\Auth::user()->tenants)->id);
        }
        return scopeLandlord($query)->first();
    }

    private function isStaff()
    {
        return !in_array(\Auth::user()->type, ['tenant', 'maintainer', 'landlord']);
    }

    public function index(Request $request)
    {
        if (!\Auth::user()->can('manage invoice')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $user = \Auth::user();
        $query = Invoice::where('parent_id', parentId())->orderBy('id', 'desc');

        // Role scope
        if ($user->type == 'tenant') {
            $query->where('tenant', optional($user->tenants)->id);
        }
        scopeLandlord($query);

        if ($request->ajax()) {

            if (!empty($request->property)) {
                $query->where('property_id', $request->property);
            }

            if (!empty($request->unit)) {
                $query->where('unit_id', $request->unit);
            }

            if (!empty($request->tenant)) {
                $query->where('tenant', $request->tenant);
            }

            if (!empty($request->month)) {
                $query->whereMonth('invoice_month', \Carbon\Carbon::parse($request->month)->month)
                    ->whereYear('invoice_month', \Carbon\Carbon::parse($request->month)->year);
            }

            $invoices = $query->get();

            return response()->json([
                'html' => view('invoice.invoice_list', compact('invoices'))->render()
            ]);
        }

        $properties = scopeLandlord(Property::where('parent_id', parentId()), 'id')->pluck('name', 'id');
        $properties->prepend(__('Select Property'), '');

        $units = scopeLandlord(PropertyUnit::where('parent_id', parentId()))->pluck('name', 'id');
        $units->prepend(__('Select Unit'), '');

        $tenants = User::where('parent_id', parentId())->where('type', 'tenant')
            ->when(landlordPropertyIds() !== null, fn($q) => $q->whereIn('id', scopeLandlord(Tenant::where('parent_id', parentId()), 'property')->pluck('user_id')))
            ->get()->pluck('full_name', 'id');
        $tenants->prepend(__('Select Tenant'), '');

        return view('invoice.index', compact('properties', 'units', 'tenants'));
    }
    public function create()
    {
        if (\Auth::user()->can('create invoice')) {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
            $property->prepend(__('Select Property'), '');
            $types = Type::where('parent_id', parentId())->where('type', 'invoice')->get()->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');

            $invoiceNumber = invoiceNumber();
            return view('invoice.create', compact('types', 'property', 'invoiceNumber'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create invoice')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'property_id' => 'required',
                    'unit_id' => 'required',
                    'invoice_month' => 'required',
                    'end_date' => 'required',
                    'types' => 'required|array',
                    'types.*.invoice_type' => 'required',
                    'types.*.amount' => 'required|numeric',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if (!PropertyUnit::where('parent_id', parentId())->where('id', $request->unit_id)->where('property_id', $request->property_id)->exists()) {
                return redirect()->back()->with('error', __('Invalid property unit selected.'));
            }
            if (!empty($request->tenant) && !Tenant::where('parent_id', parentId())->where('id', $request->tenant)->exists()) {
                return redirect()->back()->with('error', __('Invalid tenant selected.'));
            }

            $invoice = new Invoice();
            $invoice->invoice_id = $request->invoice_id;
            $invoice->property_id = $request->property_id;
            $invoice->unit_id = $request->unit_id;
            $invoice->invoice_month = $request->invoice_month . '-01';
            $invoice->end_date = $request->end_date;
            $invoice->notes = $request->notes;
            $invoice->tenant = $request->tenant;
            $invoice->is_recurring = $request->has('is_recurring') ? 1 : 0;
            $invoice->recurring_date = $request->has('is_recurring') ? $request->recurring_date : 0;
            $invoice->status = 'unpaid';
            $invoice->parent_id = parentId();
            $invoice->save();
            $types = array_values($request->types);

            for ($i = 0; $i < count($types); $i++) {
                $invoiceItem = new InvoiceItem();
                $invoiceItem->invoice_id = $invoice->id;
                $invoiceItem->invoice_type = $types[$i]['invoice_type'];
                $invoiceItem->amount = $types[$i]['amount'];
                $invoiceItem->description = $types[$i]['description'] ?? null;
                $invoiceItem->save();
            }

            $setting = settings();

            triggerN8n('create_invoice', [
                'tenant_name' => !empty($invoice->tenants->user) ? $invoice->tenants->user->name : '',
                'tenant_mail' => !empty($invoice->tenants->user) ? $invoice->tenants->user->email : '',
                'tenant_phone' => !empty($invoice->tenants->user->phone_number) ? $invoice->tenants->user->phone_number : '',
                'invoice_number' => invoicePrefix() . $invoice->invoice_id,
                'issue_description' => $invoice->notes,
                'invoice_date' => dateFormat($invoice->created_at),
                'invoice_due_at' => dateFormat($invoice->end_date),
                'amount' => priceFormat($invoice->getInvoiceDueAmount()),
                'company_name' => $setting['company_name'],
                'company_email' => $setting['company_email'],
                'company_phone' => $setting['company_phone'],
                'company_address' => $setting['company_address']
            ]);
            if (!empty($invoice->tenants)) {
                \App\Support\Notify::push($invoice->tenants->user_id, __('New invoice'), invoicePrefix() . $invoice->invoice_id . ' · ' . priceFormat($invoice->getInvoiceDueAmount()) . ' · ' . __('due') . ' ' . dateFormat($invoice->end_date), route('invoice.show', \Crypt::encrypt($invoice->id)));
            }
            $module = 'invoice_create';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            $errorMessage = '';
            if (!empty($notification) && !empty($invoice->tenants->user)) {
                $notification_responce = MessageReplace($notification, $invoice->id);
                $notification_responce['sms_message'] = \App\Support\PayLink::appendTo($notification_responce['sms_message'], $invoice);
                $datas['subject'] = $notification_responce['subject'];
                $datas['message'] = $notification_responce['message'];
                $datas['module'] = $module;
                $datas['logo'] = $setting['company_logo'];
                $to = $invoice->tenants->user->email;
                if ($notification->enabled_email == 1) {
                    $response = commonEmailSend($to, $datas);
                    if ($response['status'] == 'error') {
                        $errorMessage = $response['message'];
                    }
                }

                if ($notification->enabled_sms == 1) {
                    $twilio_sid = getSettingsValByName('twilio_sid');
                    if (!empty($twilio_sid)) {
                        send_twilio_msg($invoice->tenants->user->phone_number, $notification_responce['sms_message']);
                    }
                }

                if ($notification->enabled_whatsapp == 1) {
                    $twilio_sid = getSettingsValByName('twilio_sid');
                    if (!empty($twilio_sid)) {
                        $params = [
                            'to' => $invoice->tenants->user->phone_number,
                            'body' => $notification_responce['sms_message'],
                        ];
                        sendTwilioWhatsappMsg($params);
                    }
                }
            }

            return redirect()->route('invoice.index')->with('success', __('Invoice successfully created.') . '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function show($ids)
    {
        if (\Auth::user()->can('show invoice')) {

            $id = Crypt::decrypt($ids);
            $invoice = $this->findOwnInvoice($id);
            if (!$invoice) {
                return redirect()->back()->with('error', __('Invoice not found.'));
            }
            $invoiceNumber = $invoice->invoice_id;
            $tenant = !empty($invoice->tenants) ? $invoice->tenants : Tenant::where('parent_id', $invoice->parent_id)->where('property', $invoice->property_id)->where('unit', $invoice->unit_id)->first();

            $invoicePaymentSettings = invoicePaymentSettings($invoice->parent_id);

            $notification = Notification::where('parent_id', parentId())->where('module', 'payment_reminder')->first();
            return view('invoice.show', compact('invoiceNumber', 'invoice', 'tenant', 'invoicePaymentSettings', 'notification'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function edit($ids)
    {
        if (\Auth::user()->can('edit invoice')) {
            $id = Crypt::decrypt($ids);
            $invoice = $this->findOwnInvoice($id);
            if (!$invoice || !$this->isStaff()) {
                return redirect()->back()->with('error', __('Invoice not found.'));
            }
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
            $property->prepend(__('Select Property'), '');
            $types = Type::where('parent_id', parentId())->where('type', 'invoice')->get()->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');
            $selectedTenant = $invoice->tenant;

            $invoiceNumber = $invoice->invoice_id;
            return view('invoice.edit', compact('types', 'property', 'invoiceNumber', 'invoice', 'selectedTenant'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function update(Request $request, Invoice $invoice)
    {
        if (\Auth::user()->can('edit invoice') && $invoice->parent_id == parentId() && $this->isStaff()) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'property_id' => 'required',
                    'unit_id' => 'required',
                    'invoice_month' => 'required',
                    'end_date' => 'required',
                    'tenant' => 'required',
                    'types' => 'required|array',
                    'types.*.invoice_type' => 'required',
                    'types.*.amount' => 'required|numeric',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if (!PropertyUnit::where('parent_id', parentId())->where('id', $request->unit_id)->where('property_id', $request->property_id)->exists()) {
                return redirect()->back()->with('error', __('Invalid property unit selected.'));
            }
            if (!empty($request->tenant) && !Tenant::where('parent_id', parentId())->where('id', $request->tenant)->exists()) {
                return redirect()->back()->with('error', __('Invalid tenant selected.'));
            }

            $invoice->property_id = $request->property_id;
            $invoice->tenant = $request->tenant;
            $invoice->unit_id = $request->unit_id;
            $invoice->invoice_month = $request->invoice_month . '-01';
            $invoice->end_date = $request->end_date;
            $invoice->notes = $request->notes;
            $invoice->is_recurring = $request->has('is_recurring') ? 1 : 0;
            $invoice->recurring_date = $request->has('is_recurring') ? $request->recurring_date : 0;
            $invoice->save();
            $types = array_values($request->types);

            for ($i = 0; $i < count($types); $i++) {
                // only items of this invoice can be edited; anything else becomes a new line
                $invoiceItem = InvoiceItem::where('invoice_id', $invoice->id)->find($types[$i]['id'] ?? null);
                if ($invoiceItem == null) {
                    $invoiceItem = new InvoiceItem();
                    $invoiceItem->invoice_id = $invoice->id;
                }

                $invoiceItem->invoice_type = $types[$i]['invoice_type'];
                $invoiceItem->amount = $types[$i]['amount'];
                $invoiceItem->description = $types[$i]['description'] ?? null;
                $invoiceItem->save();
            }

            // the total may have changed, recompute the status
            Invoice::refreshStatus($invoice->id);
            return redirect()->route('invoice.index')->with('success', __('Invoice successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function destroy(Invoice $invoice)
    {
        if (\Auth::user()->can('delete invoice') && $invoice->parent_id == parentId() && $this->isStaff()) {
            InvoiceItem::where('invoice_id', $invoice->id)->delete();
            InvoicePayment::where('invoice_id', $invoice->id)->delete();
            $invoice->delete();
            return redirect()->route('invoice.index')->with('success', __('Invoice successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }



    public function invoiceTypeDestroy(Request $request)
    {
        if (\Auth::user()->can('delete invoice type')) {
            $invoiceType = InvoiceItem::find($request->id);
            if (!$invoiceType || !$this->findOwnInvoice($invoiceType->invoice_id) || !$this->isStaff()) {
                return response()->json([
                    'status' => 'error',
                    'msg' => __('Permission Denied!'),
                ]);
            }
            $invoiceType->delete();

            Invoice::refreshStatus($invoiceType->invoice_id);

            return response()->json([
                'status' => 'success',
                'msg' => __('Property successfully updated.'),
            ]);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function invoicePaymentCreate($invoice_id)
    {
        $invoice = $this->findOwnInvoice($invoice_id);
        if (!\Auth::user()->can('create invoice payment') || !$invoice || !$this->isStaff()) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        return view('invoice.payment', compact('invoice_id', 'invoice'));
    }

    public function invoicePaymentStore(Request $request, $invoice_id)
    {

        if (\Auth::user()->can('create invoice payment') && $this->isStaff()) {
            $invoice = $this->findOwnInvoice($invoice_id);
            if (!$invoice) {
                return redirect()->back()->with('error', __('Invoice not found.'));
            }
            $dueAmount = $invoice->getInvoiceDueAmount();

            $validator = \Validator::make(
                $request->all(),
                [
                    'payment_date' => 'required',
                    'amount' => 'required|numeric|min:1|max:' . $dueAmount,
                ],

            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $payment = new InvoicePayment();
            $payment->invoice_id = $invoice_id;
            $payment->transaction_id = md5(time());
            $payment->payment_type = __('Manually');
            $payment->amount = $request->amount;
            $payment->payment_date = $request->payment_date;

            if ($request->hasFile('receipt')) {
                $uploadResult = handleFileUpload($request->file('receipt'), 'upload/receipt/');

                if ($uploadResult['flag'] == 0) {
                    return redirect()->back()->with('error', $uploadResult['msg']);
                }
                $payment->receipt = $uploadResult['filename'];
            }

            $payment->notes = $request->notes;
            $payment->parent_id = parentId();
            $payment->save();
            Invoice::refreshStatus($invoice_id);
            return redirect()->back()->with('success', __('Invoice payment successfully added.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function invoicePaymentDestroy($invoice_id, $id)
    {
        if (\Auth::user()->can('delete invoice payment')) {
            $invoice = $this->findOwnInvoice($invoice_id);
            $payment = InvoicePayment::where('invoice_id', $invoice_id)->find($id);
            // refunds are removed through their credit note, so the two never get out of step
            if (!$invoice || !$payment || !$this->isStaff() || $payment->amount < 0) {
                return redirect()->back()->with('error', __('Payment not found.'));
            }
            $payment->delete();

            Invoice::refreshStatus($invoice_id);
            return redirect()->back()->with('success', __('Invoice payment successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function invoicePaymentRemind($id)
    {
        if (!\Auth::user()->can('edit invoice') || !$this->isStaff() || !$this->findOwnInvoice($id)) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $notification = Notification::where('parent_id', parentId())->where('module', 'payment_reminder')->first();
        $short_code = $notification->short_code;
        $notification->short_code = json_decode($notification->short_code);

        $Notifications = Notification::$modules;
        $notification_option = [];
        foreach ($Notifications as $key => $value) {
            $notification_option[$key] = $value['name'];
        }
        return view('invoice.remind', compact('notification', 'notification_option', 'Notifications', 'id'));
    }

    public function invoicePaymentRemindData(Request $request, $id)
    {
        if (!\Auth::user()->can('edit invoice') || !$this->isStaff()) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $invoice = $this->findOwnInvoice($id);
        if (!$invoice) {
            return redirect()->back()->with('error', __('Invoice not found.'));
        }
        $tenant = !empty($invoice->tenants) ? $invoice->tenants : Tenant::where('parent_id', parentId())->where('property', $invoice->property_id)->where('unit', $invoice->unit_id)->first();
        $user = !empty($tenant) ? User::find($tenant->user_id) : null;
        if (empty($user)) {
            return redirect()->back()->with('error', __('No tenant found for this invoice.'));
        }
        $setting = settings();

        triggerN8n('payment_reminder', [
            'tenant_name' => $user->name,
            'tenant_mail' => $user->email,
            'tenant_phone' => $user->phone_number,
            'invoice_number' => invoicePrefix() . $invoice->invoice_id,
            'issue_description' => $invoice->notes,
            'invoice_date' => dateFormat($invoice->created_at),
            'invoice_due_at' => dateFormat($invoice->end_date),
            'amount' => priceFormat($invoice->getInvoiceDueAmount()),
            'company_name' => $setting['company_name'],
            'company_email' => $setting['company_email'],
            'company_phone' => $setting['company_phone'],
            'company_address' => $setting['company_address']
        ]);

        $notification = Notification::where('parent_id', parentId())->where('module', 'payment_reminder')->first();
        $module = 'payment_reminder';

        $errorMessage = '';
        if (!empty($notification)) {

            $return['subject'] = $request->subject;
            $return['message'] = $request->message;
            $return['sms_message'] = $request->sms_message;
            $settings = settings();

            if (!empty($request->subject) && !empty($request->message)) {
                $search = [];
                $replace = [];

                $user_name = $user->name;
                $invoice_number = invoicePrefix() . $invoice->invoice_id;
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{user_name}', '{invoice_number}', '{invoice_date}', '{invoice_due_date}', '{amount}', '{invoice_description}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $user_name, $invoice_number, $invoice->created_at, $invoice->end_date, priceFormat($invoice->getInvoiceDueAmount()), $invoice->notes];

                $return['subject'] = str_replace($search, $replace, $request->subject);
                $return['message'] = str_replace($search, $replace, $request->message);
                $return['sms_message'] = str_replace($search, $replace, $request->sms_message);
            }

            // one-tap pay link
            $return['sms_message'] = \App\Support\PayLink::appendTo($return['sms_message'], $invoice);
            if (\App\Support\PayLink::canPayOnline($invoice->parent_id)) {
                $payUrl = \App\Support\PayLink::url($invoice);
                $return['message'] = str_contains((string) $return['message'], '{payment_link}')
                    ? str_replace('{payment_link}', '<a href="' . $payUrl . '">' . $payUrl . '</a>', $return['message'])
                    : $return['message'] . '<p><a href="' . $payUrl . '">' . __('Pay now') . '</a></p>';
            }
            \App\Support\Notify::push($user->id, __('Rent reminder'), invoicePrefix() . $invoice->invoice_id . ' · ' . priceFormat($invoice->getInvoiceDueAmount()) . ' ' . __('is due'), \App\Support\PayLink::canPayOnline($invoice->parent_id) ? \App\Support\PayLink::url($invoice) : route('invoice.show', \Crypt::encrypt($invoice->id)));

            $datas['subject'] = $return['subject'];
            $datas['message'] = $return['message'];
            $datas['module'] = $module;
            $datas['logo'] = $setting['company_logo'];
            $to = $user->email;

            if ($notification->enabled_email == 1) {
                $response = commonEmailSend($to, $datas);
                if ($response['status'] == 'error') {
                    $errorMessage = $response['message'];
                }
            }

            if ($notification->enabled_sms == 1) {
                $twilio_sid = getSettingsValByName('twilio_sid');
                if (!empty($twilio_sid)) {
                    send_twilio_msg($user->phone_number, $return['sms_message']);
                }
            }

            if ($notification->enabled_whatsapp == 1) {
                $twilio_sid = getSettingsValByName('twilio_sid');
                if (!empty($twilio_sid)) {
                    $params = [
                        'to' => $user->phone_number,
                        'body' => $return['sms_message'],
                    ];
                    sendTwilioWhatsappMsg($params);
                }
            }
        }

        return redirect()->back()->with('success', __('Email successfully sent.') . '</br>' . $errorMessage);
    }

    public function unitByTenant(Request $request, $unitId)
    {
        $tenants = Tenant::where('parent_id', parentId())->where('unit', $unitId)
            ->whereHas('user', function ($q) {
                $q->where('is_active', 1);
            })
            ->with('user:id,first_name,last_name')
            ->get()
            ->map(function ($tenant) {

                $name = $tenant->user->full_name ?? '';

                if (empty($tenant->exit_date)) {
                    $status = 'Active';
                    $sort = 1;
                } else {
                    $status = 'Exit - ' . date('d-m-Y', strtotime($tenant->exit_date));
                    $sort = 2;
                }

                return [
                    'id' => $tenant->id,
                    'label' => $name . ' - ' . $status,
                    'sort' => $sort
                ];
            })
            ->sortBy('sort')
            ->mapWithKeys(function ($item) {
                return [$item['id'] => $item['label']];
            });

        return response()->json($tenants);
    }
}
