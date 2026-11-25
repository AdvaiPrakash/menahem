<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Services\AdminLiveDataService;
use App\Services\AdminNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationController extends Controller
{
    public function __construct(
        protected AdminNotificationService $notificationService,
        protected AdminLiveDataService $liveDataService
    ) {}

    /**
     * Return newly arrived notifications and sync tokens for admin live alerts.
     */
    public function poll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'after_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $payload = $this->notificationService->poll(
            (int) ($validated['after_id'] ?? 0),
            $request->user()?->id
        );

        return response()->json($payload);
    }

    /**
     * Stream real-time admin notifications over Server-Sent Events (SSE).
     */
    public function stream(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'after_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $lastEventIdHeader = $request->header('Last-Event-ID');
        $afterId = is_numeric($lastEventIdHeader)
            ? max(0, (int) $lastEventIdHeader)
            : (int) ($validated['after_id'] ?? 0);

        $userId = $request->user()?->id;

        // Release PHP session lock immediately so concurrent admin requests never block.
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        return response()->stream(function () use ($afterId, $userId): void {
            if (function_exists('set_time_limit')) {
                @set_time_limit(0);
            }

            $cursorId = $afterId;
            $lastSyncTokens = null;
            $maxIterations = 20;

            for ($i = 0; $i < $maxIterations; $i++) {
                if (connection_aborted()) {
                    break;
                }

                $payload = $this->notificationService->poll($cursorId, $userId);
                $hasNotifications = ! empty($payload['notifications']);
                $syncChanged = $lastSyncTokens !== null && $payload['sync_tokens'] !== $lastSyncTokens;

                if ($hasNotifications || $syncChanged || $i === 0) {
                    $cursorId = (int) ($payload['cursor']['after_id'] ?? $cursorId);
                    $lastSyncTokens = $payload['sync_tokens'];

                    echo "id: {$cursorId}\n";
                    echo "event: notification\n";
                    echo 'data: '.json_encode($payload)."\n\n";
                } elseif ($i % 10 === 0) {
                    echo ": keepalive\n\n";
                }

                if (ob_get_level() > 0) {
                    @ob_flush();
                }
                flush();

                if ($i < $maxIterations - 1) {
                    sleep(1);
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Mark a single notification as read for the authenticated admin user.
     */
    public function markRead(Request $request, AdminNotification $notification): JsonResponse
    {
        $unreadCount = $this->notificationService->markAsRead(
            $notification,
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark all notifications as read for the authenticated admin user.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $unreadCount = $this->notificationService->markAllAsRead(
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Return fresh live-rendered HTML fragments and metrics for the active admin page.
     */
    public function liveData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'context' => ['required', 'string', 'in:dashboard,inquiries,orders'],
        ]);

        $context = $validated['context'];

        if ($context === 'dashboard') {
            $data = $this->liveDataService->getDashboardData();

            return response()->json([
                'context' => 'dashboard',
                'metrics' => [
                    'total_revenue' => '₹'.number_format((float) $data['totalRevenue']),
                    'paid_orders_label' => "{$data['paidOrdersCount']} Confirmed Orders",
                    'total_inquiries' => (string) $data['totalInquiries'],
                    'new_inquiries_label' => "{$data['newInquiriesCount']} Awaiting Reply",
                ],
                'html' => [
                    'recent_orders' => view('admin.dashboard.partials.recent-orders', [
                        'recentOrders' => $data['recentOrders'],
                    ])->render(),
                    'recent_inquiries' => view('admin.dashboard.partials.recent-inquiries', [
                        'recentInquiries' => $data['recentInquiries'],
                    ])->render(),
                ],
            ]);
        }

        if ($context === 'inquiries') {
            $data = $this->liveDataService->getInquiriesData($request);

            return response()->json([
                'context' => 'inquiries',
                'status_counts' => $data['statusCounts'],
                'html' => [
                    'desktop_rows' => view('admin.inquiries.partials.desktop-rows', [
                        'inquiries' => $data['inquiries'],
                    ])->render(),
                    'mobile_cards' => view('admin.inquiries.partials.mobile-cards', [
                        'inquiries' => $data['inquiries'],
                    ])->render(),
                ],
            ]);
        }

        $data = $this->liveDataService->getOrdersData($request);

        return response()->json([
            'context' => 'orders',
            'stats' => [
                'total' => number_format((int) ($data['stats']['total'] ?? 0)),
                'total_revenue' => '₹'.number_format((float) ($data['stats']['total_revenue'] ?? 0), 2),
                'paid_label' => ($data['stats']['paid'] ?? 0).' paid orders',
                'processing' => (string) ($data['stats']['processing'] ?? 0),
                'delivered' => (string) ($data['stats']['delivered'] ?? 0),
            ],
            'html' => [
                'table_rows' => view('admin.orders.partials.table-rows', [
                    'orders' => $data['orders'],
                ])->render(),
            ],
        ]);
    }
}
