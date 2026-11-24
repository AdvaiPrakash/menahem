<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(protected AdminNotificationService $notificationService) {}

    /**
     * Return newly arrived inquiries and received orders for admin live alerts.
     */
    public function poll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'after_inquiry_id' => ['nullable', 'integer', 'min:0'],
            'since_order_at' => ['nullable', 'string', 'max:64'],
        ]);

        $payload = $this->notificationService->poll(
            (int) ($validated['after_inquiry_id'] ?? 0),
            $validated['since_order_at'] ?? null
        );

        return response()->json($payload);
    }
}
