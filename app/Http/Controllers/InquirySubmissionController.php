<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your inquiry has been received. Our team will get back to you shortly.',
                'inquiry_id' => $inquiry->id,
            ]);
        }

        return back()->with('success', 'Thank you! Your inquiry has been received. Our team will get back to you shortly.');
    }
}
