<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\AdminNotificationRead;
use App\Models\Inquiry;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminNotificationService
{
    /**
     * Record a persistent notification when a customer submits an enquiry.
     */
    public function recordInquiryCreated(Inquiry $inquiry): AdminNotification
    {
        $occurredAt = $inquiry->created_at ?? Carbon::now();
        $subject = $inquiry->resolvedSubject();
        $typeLabel = str_replace('_', ' ', (string) ($inquiry->type ?: 'general'));

        return AdminNotification::firstOrCreate(
            ['event_key' => "inquiry:{$inquiry->id}"],
            [
                'type' => 'inquiry',
                'event_name' => 'inquiry.created',
                'subject_type' => Inquiry::class,
                'subject_id' => $inquiry->id,
                'title' => 'New Enquiry Received',
                'message' => "{$inquiry->name} • {$subject}",
                'detail' => $inquiry->message
                    ? Str::limit($inquiry->message, 90)
                    : ucwords($typeLabel).' • '.$occurredAt->format('d M Y, h:i A'),
                'url' => route('admin.inquiries.show', $inquiry),
                'payload' => [
                    'inquiry_id' => $inquiry->id,
                    'customer_name' => $inquiry->name,
                    'subject' => $subject,
                    'inquiry_type' => $inquiry->type ?: 'general',
                    'status' => $inquiry->status,
                    'submitted_at' => $occurredAt->format('d M Y, h:i A'),
                ],
                'occurred_at' => $occurredAt,
            ]
        );
    }

    /**
     * Record a persistent notification when a customer creates a new order.
     */
    public function recordOrderCreated(Order $order): AdminNotification
    {
        $occurredAt = $order->created_at ?? Carbon::now();
        $formattedAmount = '₹'.number_format((float) $order->grand_total, 2);
        $orderStatusLabel = ucfirst((string) ($order->order_status ?: 'placed'));
        $paymentStatusLabel = ucfirst((string) ($order->payment_status ?: 'pending'));

        return AdminNotification::firstOrCreate(
            ['event_key' => "order_created:{$order->id}"],
            [
                'type' => 'order',
                'event_name' => 'order.created',
                'subject_type' => Order::class,
                'subject_id' => $order->id,
                'title' => 'New Order Placed',
                'message' => "{$order->order_number} • {$formattedAmount}",
                'detail' => "{$order->customer_name} • Order: {$orderStatusLabel} • Payment: {$paymentStatusLabel}",
                'url' => route('admin.orders.show', $order),
                'payload' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'amount' => (float) $order->grand_total,
                    'formatted_amount' => $formattedAmount,
                    'order_status' => $order->order_status ?: 'placed',
                    'payment_status' => $order->payment_status ?: 'pending',
                    'order_date' => $occurredAt->format('d M Y, h:i A'),
                ],
                'occurred_at' => $occurredAt,
            ]
        );
    }

    /**
     * Record a persistent notification when an order payment is confirmed.
     */
    public function recordOrderPaid(Order $order): AdminNotification
    {
        $occurredAt = $order->paid_at ?? Carbon::now();
        $formattedAmount = '₹'.number_format((float) $order->grand_total, 2);
        $orderStatusLabel = ucfirst((string) ($order->order_status ?: 'confirmed'));

        return AdminNotification::firstOrCreate(
            ['event_key' => "order_paid:{$order->id}"],
            [
                'type' => 'order',
                'event_name' => 'order.paid',
                'subject_type' => Order::class,
                'subject_id' => $order->id,
                'title' => 'Order Payment Confirmed',
                'message' => "{$order->order_number} • {$formattedAmount}",
                'detail' => "{$order->customer_name} • Order: {$orderStatusLabel} • Payment: Paid",
                'url' => route('admin.orders.show', $order),
                'payload' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'amount' => (float) $order->grand_total,
                    'formatted_amount' => $formattedAmount,
                    'order_status' => $order->order_status ?: 'confirmed',
                    'payment_status' => 'paid',
                    'order_date' => ($order->created_at ?? $occurredAt)->format('d M Y, h:i A'),
                ],
                'occurred_at' => $occurredAt,
            ]
        );
    }

    /**
     * Build the initial notification state for the admin panel layout.
     *
     * @return array<string, mixed>
     */
    public function getInitialState(?int $userId = null): array
    {
        $latestId = (int) (AdminNotification::max('id') ?? 0);
        $recent = $this->getRecentNotifications($userId, 15);

        return [
            'cursor' => [
                'after_id' => $latestId,
            ],
            'unread_count' => $this->getUnreadCount($userId),
            'recent_notifications' => $recent,
            'counts' => $this->getBadgeCounts(),
            'sync_tokens' => $this->getSyncTokens(),
            'audio' => [
                'inquiry' => asset('audio/enquiry-notification.mp3'),
                'order' => asset('audio/order-notification.mp3'),
            ],
        ];
    }

    /**
     * Poll for new notifications after the provided monotonic notification ID cursor.
     *
     * @return array<string, mixed>
     */
    public function poll(int $afterId = 0, ?int $userId = null): array
    {
        $newItems = AdminNotification::query()
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit(30)
            ->get();

        $readMap = $this->getReadIdsMap($newItems->pluck('id'), $userId);

        $notifications = $newItems->map(
            fn (AdminNotification $notification): array => $this->formatNotification(
                $notification,
                isset($readMap[$notification->id])
            )
        )->values()->all();

        $nextAfterId = $newItems->isNotEmpty()
            ? (int) $newItems->max('id')
            : $afterId;

        $payload = [
            'notifications' => $notifications,
            'cursor' => [
                'after_id' => $nextAfterId,
            ],
            'unread_count' => $this->getUnreadCount($userId),
            'counts' => $this->getBadgeCounts(),
            'sync_tokens' => $this->getSyncTokens(),
        ];

        if ($newItems->isNotEmpty()) {
            $payload['recent_notifications'] = $this->getRecentNotifications($userId, 15);
        }

        return $payload;
    }

    /**
     * Mark a single notification as read for the given admin user.
     */
    public function markAsRead(AdminNotification $notification, int $userId): int
    {
        AdminNotificationRead::firstOrCreate(
            [
                'admin_notification_id' => $notification->id,
                'user_id' => $userId,
            ],
            [
                'read_at' => Carbon::now(),
            ]
        );

        return $this->getUnreadCount($userId);
    }

    /**
     * Mark all existing notifications as read for the given admin user.
     */
    public function markAllAsRead(int $userId): int
    {
        $unreadIds = AdminNotification::query()
            ->whereDoesntHave('reads', function ($query) use ($userId): void {
                $query->where('user_id', $userId);
            })
            ->pluck('id');

        if ($unreadIds->isNotEmpty()) {
            $now = Carbon::now();
            $rows = $unreadIds->map(static fn (int $id): array => [
                'admin_notification_id' => $id,
                'user_id' => $userId,
                'read_at' => $now,
            ])->all();

            foreach (array_chunk($rows, 200) as $chunk) {
                AdminNotificationRead::insertOrIgnore($chunk);
            }
        }

        return 0;
    }

    /**
     * Get the latest notifications with per-admin read status.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRecentNotifications(?int $userId = null, int $limit = 15): array
    {
        $items = AdminNotification::query()
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $readMap = $this->getReadIdsMap($items->pluck('id'), $userId);

        return $items->map(
            fn (AdminNotification $notification): array => $this->formatNotification(
                $notification,
                isset($readMap[$notification->id])
            )
        )->values()->all();
    }

    /**
     * Count unread notifications for the given admin user.
     */
    public function getUnreadCount(?int $userId = null): int
    {
        if (! $userId) {
            return AdminNotification::count();
        }

        return AdminNotification::query()
            ->whereDoesntHave('reads', function ($query) use ($userId): void {
                $query->where('user_id', $userId);
            })
            ->count();
    }

    /**
     * Get sidebar badge counts for orders and inquiries.
     *
     * @return array<string, int>
     */
    public function getBadgeCounts(): array
    {
        return [
            'new_inquiries' => Inquiry::whereIn('status', ['new', 'pending'])->count(),
            'pending_orders' => Order::whereIn('order_status', ['placed', 'processing', 'confirmed'])->count(),
        ];
    }

    /**
     * Return lightweight change-detection tokens for live page synchronization.
     *
     * @return array<string, string|int|null>
     */
    public function getSyncTokens(): array
    {
        $inquiryMaxId = (int) (Inquiry::max('id') ?? 0);
        $inquiryUpdated = Inquiry::max('updated_at');
        $orderMaxId = (int) (Order::max('id') ?? 0);
        $orderUpdated = Order::max('updated_at');

        return [
            'inquiry' => "{$inquiryMaxId}:".($inquiryUpdated ? (string) $inquiryUpdated : '0'),
            'order' => "{$orderMaxId}:".($orderUpdated ? (string) $orderUpdated : '0'),
        ];
    }

    /**
     * Format an AdminNotification model into a clean JSON payload.
     *
     * @return array<string, mixed>
     */
    public function formatNotification(AdminNotification $notification, bool $isRead = false): array
    {
        $occurredAt = $notification->occurred_at ?? $notification->created_at ?? Carbon::now();

        return [
            'id' => $notification->id,
            'key' => $notification->event_key,
            'type' => $notification->type,
            'event_name' => $notification->event_name,
            'title' => $notification->title,
            'message' => $notification->message,
            'detail' => $notification->detail,
            'url' => $notification->url,
            'payload' => $notification->payload ?? [],
            'is_read' => $isRead,
            'audio_url' => $notification->type === 'order'
                ? asset('audio/order-notification.mp3')
                : asset('audio/enquiry-notification.mp3'),
            'timestamp' => $occurredAt->toIso8601String(),
            'timestamp_unix' => $occurredAt->timestamp,
            'time_ago' => $occurredAt->diffForHumans(),
        ];
    }

    /**
     * Fetch a lookup map of notification IDs already read by the user.
     *
     * @param  Collection<int, int>  $notificationIds
     * @return array<int, true>
     */
    protected function getReadIdsMap(Collection $notificationIds, ?int $userId): array
    {
        if (! $userId || $notificationIds->isEmpty()) {
            return [];
        }

        return AdminNotificationRead::query()
            ->where('user_id', $userId)
            ->whereIn('admin_notification_id', $notificationIds)
            ->pluck('admin_notification_id')
            ->mapWithKeys(static fn (int $id): array => [$id => true])
            ->all();
    }
}
