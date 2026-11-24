<?php

namespace App\Http\Controllers;

use App\Mail\NewInquiryNotificationMail;
use App\Models\Inquiry;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InquirySubmissionController extends Controller
{
    /**
     * Handle public form submissions (newsletter, curtain measurements, contact).
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:255'],
            'product_name' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
            'details' => ['nullable', 'array'],
        ]);

        // Name fallback for newsletter/quick subscribes
        $name = $validated['name'] ?? ($validated['email'] ? explode('@', $validated['email'])[0] : 'Website Visitor');

        $inquiry = Inquiry::create([
            'name' => $name,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'type' => $validated['type'] ?? 'general',
            'subject' => $validated['subject'] ?? null,
            'product_name' => $validated['product_name'] ?? null,
            'message' => $validated['message'] ?? null,
            'details' => $validated['details'] ?? null,
            'status' => 'new',
        ]);

        $this->sendAdminEmailNotification($inquiry);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your inquiry has been received. Our team will get back to you shortly.',
                'inquiry_id' => $inquiry->id,
            ]);
        }

        return back()->with('success', 'Thank you! Your inquiry has been received. Our team will get back to you shortly.');
    }

    /**
     * Email newly submitted website enquiry details to the business mailbox.
     */
    protected function sendAdminEmailNotification(Inquiry $inquiry): void
    {
        $adminEmail = (string) config('mail.enquiry_from.address', 'info@menahem.co.in');
        $businessName = trim((string) Setting::get('seller_name', config('mail.enquiry_from.name', 'Menahem Home & Living')));

        try {
            Mail::to($adminEmail, $businessName)->send(
                new NewInquiryNotificationMail(
                    inquiry: $inquiry,
                    businessName: $businessName,
                    fromAddress: $adminEmail,
                )
            );
        } catch (\Throwable $e) {
            $safeError = $e->getMessage();
            $password = (string) config('mail.mailers.smtp.password', '');
            if ($password !== '') {
                $safeError = str_replace($password, '[REDACTED]', $safeError);
            }

            Log::warning('New enquiry admin email notification failed', [
                'inquiry_id' => $inquiry->id,
                'error' => mb_substr($safeError, 0, 500),
            ]);
        }
    }
}
