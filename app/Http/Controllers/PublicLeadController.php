<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadViewing;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\RentalApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * Public (not logged in) forms on an owner's website: enquiry / viewing request and rental application.
 */
class PublicLeadController extends Controller
{
    private function owner($code)
    {
        return User::where('code', $code)->where('type', 'owner')->firstOrFail();
    }

    // the honeypot field is hidden from people; bots fill it in
    private function isSpam(Request $request)
    {
        return !empty($request->input('website'));
    }

    private function notifyOwner($owner, $subject, $html)
    {
        $settings = settingsById($owner->id);
        commonEmailSend($owner->email, [
            'module' => 'lead_notification',
            'subject' => $subject,
            'message' => $html,
            'logo' => $settings['company_logo'] ?? '',
            'parent_id' => $owner->id,
        ]);
    }

    // store an applicant upload under a random name (original names often contain personal details)
    private function storePrivateUpload(UploadedFile $file)
    {
        $renamed = new UploadedFile($file->getRealPath(), Str::random(32) . '.' . strtolower($file->getClientOriginalExtension()), $file->getMimeType(), null, true);
        return handleFileUpload($renamed, 'upload/application/', ['mimes:jpg,jpeg,png,pdf', 'max:10240']);
    }

    public function enquiryStore(Request $request, $code)
    {
        $owner = $this->owner($code);
        if ($this->isSpam($request)) {
            return redirect()->back()->with('success', __('Thank you! We will get back to you soon.'));
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|max:191',
            'email' => 'required|email|max:191',
            'phone' => 'nullable|max:50',
            'message' => 'nullable|max:2000',
            'property_id' => 'required',
            'viewing_date' => 'nullable|date|after_or_equal:today',
            'viewing_time' => 'nullable|max:20',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('error', $validator->getMessageBag()->first());
        }

        $property = Property::where('parent_id', $owner->id)->where('id', $request->property_id)->first();
        if (!$property) {
            return redirect()->back()->with('error', __('Property not found.'));
        }
        // when the owner publishes viewing hours, only a free time can be booked
        $booking = !empty(\App\Support\WebsiteSite::visitors($owner->id)['viewings']) && !empty($request->viewing_date);
        if ($booking && !\App\Support\ViewingSlots::isFree($owner->id, $request->viewing_date, $request->viewing_time)) {
            return redirect()->back()->withInput()->with('error', __('Sorry, that viewing time is no longer free. Please pick another time.'));
        }
        $unitId = 0;
        if (!empty($request->unit_id)) {
            $unitId = (int) PropertyUnit::where('parent_id', $owner->id)->where('property_id', $property->id)->where('id', $request->unit_id)->value('id');
        }

        $lead = Lead::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'property_id' => $property->id,
            'unit_id' => $unitId,
            'source' => 'website',
            'status' => !empty($request->viewing_date) ? 'viewing' : 'new',
            'message' => $request->message,
            'last_activity_at' => now(),
            'parent_id' => $owner->id,
        ]);

        $viewingText = '';
        if (!empty($request->viewing_date)) {
            LeadViewing::create([
                'lead_id' => $lead->id,
                'property_id' => $property->id,
                'unit_id' => $unitId,
                'viewing_date' => $request->viewing_date,
                'viewing_time' => $booking ? substr($request->viewing_time, 0, 5) : $request->viewing_time,
                'status' => $booking ? 'scheduled' : 'requested',
                'parent_id' => $owner->id,
            ]);
            $viewingText = '<p><strong>' . __('Viewing requested') . ':</strong> ' . e($request->viewing_date) . ' ' . e($request->viewing_time) . '</p>';
        }

        $this->notifyOwner($owner, __('New enquiry for') . ' ' . $property->name,
            '<p>' . __('You have a new enquiry from your website.') . '</p>'
            . '<p><strong>' . e($lead->name) . '</strong><br>' . e($lead->email) . '<br>' . e($lead->phone) . '</p>'
            . $viewingText
            . (!empty($lead->message) ? '<p>' . nl2br(e($lead->message)) . '</p>' : ''));

        if ($booking) {
            $when = dateFormat($request->viewing_date) . ' ' . substr($request->viewing_time, 0, 5);
            $settings = settingsById($owner->id);
            commonEmailSend($lead->email, [
                'module' => 'lead_notification',
                'subject' => __('Your viewing is booked') . ': ' . $property->name,
                'message' => '<p>' . e(__('Hello :name,', ['name' => $lead->name])) . '</p>'
                    . '<p>' . e(__('Your viewing of :property is booked for :when.', ['property' => $property->name, 'when' => $when])) . '</p>'
                    . '<p>' . e(trim(implode(', ', array_filter([$property->address, $property->city])))) . '</p>'
                    . '<p>' . e(__('If you cannot come, please reply to this email.')) . '</p>',
                'logo' => $settings['company_logo'] ?? '',
                'parent_id' => $owner->id,
            ]);
            return redirect()->back()->with('success', __('Your viewing is booked for :when. We have sent you a confirmation email.', ['when' => $when]));
        }

        return redirect()->back()->with('success', __('Thank you! We will get back to you soon.'));
    }

    public function applyForm($code, $propertyId)
    {
        $owner = $this->owner($code);
        try {
            $propertyId = Crypt::decrypt($propertyId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }
        $property = Property::where('parent_id', $owner->id)->where('is_active', 1)->where('id', $propertyId)->firstOrFail();
        $units = PropertyUnit::where('property_id', $property->id)->where('is_occupied', 0)->pluck('name', 'id');
        $settings = settingsById($owner->id);
        $user = $owner;
        return view('theme.apply', compact('owner', 'user', 'property', 'units', 'settings', 'code'));
    }

    public function applyStore(Request $request, $code)
    {
        $owner = $this->owner($code);
        if ($this->isSpam($request)) {
            return redirect()->back()->with('success', __('Thank you! Your application has been submitted.'));
        }

        $validator = \Validator::make($request->all(), [
            'property_id' => 'required',
            'unit_id' => 'required',
            'first_name' => 'required|max:191',
            'last_name' => 'nullable|max:191',
            'email' => 'required|email|max:191',
            'phone' => 'required|max:50',
            'current_address' => 'nullable|max:1000',
            'employer' => 'nullable|max:191',
            'monthly_income' => 'nullable|numeric|min:0',
            'family_member' => 'nullable|integer|min:1|max:50',
            'move_in_date' => 'nullable|date',
            'id_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'income_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'message' => 'nullable|max:2000',
            'consent' => 'accepted',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('error', $validator->getMessageBag()->first());
        }

        $unit = PropertyUnit::where('parent_id', $owner->id)->where('property_id', $request->property_id)->where('id', $request->unit_id)->first();
        if (!$unit || $unit->is_occupied == 1) {
            return redirect()->back()->withInput()->with('error', __('This unit is no longer available.'));
        }

        $documents = [];
        foreach (['id_document', 'income_document'] as $field) {
            if ($request->hasFile($field)) {
                $upload = $this->storePrivateUpload($request->file($field));
                if ($upload['flag'] == 0) {
                    return redirect()->back()->withInput()->with('error', $upload['msg']);
                }
                $documents[$field] = $upload['filename'];
            }
        }

        // continue an existing open lead for this person and property, otherwise start one
        $lead = Lead::where('parent_id', $owner->id)->where('email', $request->email)->where('property_id', $unit->property_id)
            ->whereIn('status', Lead::openStatuses())->orderBy('id', 'desc')->first();
        if (!$lead) {
            $lead = Lead::create([
                'name' => trim($request->first_name . ' ' . $request->last_name),
                'email' => $request->email,
                'phone' => $request->phone,
                'property_id' => $unit->property_id,
                'unit_id' => $unit->id,
                'source' => 'website',
                'status' => 'application',
                'parent_id' => $owner->id,
            ]);
        }
        $lead->status = 'application';
        $lead->unit_id = $unit->id;
        $lead->touchActivity();

        $application = RentalApplication::create([
            'lead_id' => $lead->id,
            'property_id' => $unit->property_id,
            'unit_id' => $unit->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'current_address' => $request->current_address,
            'employer' => $request->employer,
            'monthly_income' => $request->monthly_income ?: 0,
            'family_member' => $request->family_member ?: 1,
            'move_in_date' => $request->move_in_date ?: null,
            'id_document' => $documents['id_document'] ?? null,
            'income_document' => $documents['income_document'] ?? null,
            'message' => $request->message,
            'status' => 'pending',
            'parent_id' => $owner->id,
        ]);

        $propertyName = optional($unit->properties)->name;
        $this->notifyOwner($owner, __('New rental application for') . ' ' . $propertyName,
            '<p>' . __('A new rental application has been submitted.') . '</p>'
            . '<p><strong>' . e($application->full_name) . '</strong><br>' . e($application->email) . '<br>' . e($application->phone) . '</p>'
            . '<p>' . e($propertyName) . ' - ' . e($unit->name) . '</p>');

        return redirect()->to(\App\Support\WebsiteSite::propertyUrl($owner, Property::find($unit->property_id)))
            ->with('success', __('Thank you! Your application has been submitted.'));
    }
}
