<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AdminNotificationService
{
    /**
     * Build the initial notification state for the admin panel layout.
     *
     * @return array<string, mixed>
     */
    public function getInitialState(): array
    {
        $latestInquiryId = (int) (Inquiry::max('id') ?? 0);
        $sinceOrderAt = Carbon::now()->subSeconds(2)->toIso8601String();

        $recentInquiries = Inquiry::latest('id')->take(10)->get();
        $recentOrders = Order::where('payment_status', 'paid')
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        $seenKeys = [];
        $recentNotifications = [];

        foreach ($recentInquiries as $inquiry) {
            $formatted = $this->formatInquiryNotification($inquiry);
            $seenKeys[] = $formatted['key'];
            $recentNotifications[] = $formatted;
        }

        foreach ($recentOrders as $order) {
            $formatted = $this->formatOrderNotification($order);
            $seenKeys[] = $formatted['key'];
            $recentNotifications[] = $formatted;
        }

        usort($recentNotifications, static function (array $a, array $b): int {
            return $b['timestamp_unix'] <=> $a['timestamp_unix'];
        });

        return [
            'cursor' => [
                'after_inquiry_id' => $latestInquiryId,
                'since_order_at' => $sinceOrderAt,
            ],
            'seen_keys' => $seenKeys,
            'recent_notifications' => array_slice($recentNotifications, 0, 12),
            'counts' => $this->getBadgeCounts(),
            'audio' => [
                'inquiry' => asset('audio/enquiry-notification.mp3'),
                'order' => asset('audio/order-notification.mp3'),
            ],
        ];
    }

    /**
     * Poll for new inquiries and newly received (paid) orders after the provided cursor.
     *
     * @return array<string, mixed>
     */
    public function poll(int $afterInquiryId = 0, ?string $sinceOrderAt = null): array
    {
        $sinceCarbon = $this->parseSinceTimestamp($sinceOrderAt);

        $newInquiries = Inquiry::where('id', '>', $afterInquiryId)
            ->orderBy('id')
            ->limit(20)
            ->get();

        $newOrders = Order::where('payment_status', 'paid')
            ->where(function ($query) use ($sinceCarbon): void {
                $query->where('paid_at', '>', $sinceCarbon)
                    ->orWhere(function ($subQuery) use ($sinceCarbon): void {
                        $subQuery->whereNull('paid_at')
                            ->where('created_at', '>', $sinceCarbon);
                    });
            })
            ->limit(20)
            ->get()
            ->sortBy(static function (Order $order): int {
                return ($order->paid_at ?? $order->created_at)?->timestamp ?? 0;
            })
            ->values();

        $notifications = [];

        foreach ($newInquiries as $inquiry) {
            $notifications[] = $this->formatInquiryNotification($inquiry);
        }

        foreach ($newOrders as $order) {
            $notifications[] = $this->formatOrderNotification($order);
        }

        usort($notifications, static function (array $a, array $b): int {
            return $a['timestamp_unix'] <=> $b['timestamp_unix'];
        });

        $nextInquiryId = $newInquiries->isNotEmpty()
            ? (int) $newInquiries->max('id')
            : $afterInquiryId;

        return [
            'notifications' => $notifications,
            'cursor' => [
                'after_inquiry_id' => $nextInquiryId,
                'since_order_at' => Carbon::now()->subSeconds(2)->toIso8601String(),
            ],
            'counts' => $this->getBadgeCounts(),
        ];
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
            'pending_orders' => Order::whereIn('order_status', ['processing', 'confirmed'])->count(),
        ];
    }

    /**
     * Format an Inquiry model into a notification payload.
     *
     * @return array<string, mixed>
     */
    protected function formatInquiryNotification(Inquiry $inquiry): array
    {
        $occurredAt = $inquiry->created_at ?? Carbon::now();
        $typeLabel = $inquiry->type ?: 'general';
        $summaryParts = array_filter([
            $inquiry->name,
            $typeLabel,
            $inquiry->product_name,
        ]);

        return [
            'key' => "inquiry-{$inquiry->id}",
            'id' => $inquiry->id,
            'type' => 'inquiry',
            'title' => 'New Enquiry Received',
            'message' => implode(' • ', $summaryParts),
            'detail' => $inquiry->message ? Str::limit($inquiry->message, 90) : ($inquiry->email ?: $inquiry->phone ?: 'Website consultation form'),
            'url' => route('admin.inquiries.show', $inquiry),
            'audio_url' => asset('audio/enquiry-notification.mp3'),
            'status' => $inquiry->status,
            'timestamp' => $occurredAt->toIso8601String(),
            'timestamp_unix' => $occurredAt->timestamp,
            'time_ago' => $occurredAt->diffForHumans(),
        ];
    }

    /**
     * Format an Order model into a notification payload.
     *
     * @return array<string, mixed>
     */
    protected function formatOrderNotification(Order $order): array
    {
        $occurredAt = $order->paid_at ?? $order->created_at ?? Carbon::now();
        $formattedAmount = '₹'.number_format((float) $order->grand_total, 2);

        return [
            'key' => "order-{$order->id}",
            'id' => $order->id,
            'type' => 'order',
            'title' => 'New Order Received',
            'message' => "{$order->order_number} • {$formattedAmount}",
            'detail' => "Customer: {$order->customer_name}",
            'url' => route('admin.orders.show', $order),
            'audio_url' => asset('audio/order-notification.mp3'),
            'status' => $order->order_status,
            'timestamp' => $occurredAt->toIso8601String(),
            'timestamp_unix' => $occurredAt->timestamp,
            'time_ago' => $occurredAt->diffForHumans(),
        ];
    }

    /**
     * Parse the order polling cursor safely.
     */
    protected function parseSinceTimestamp(?string $sinceOrderAt): Carbon
    {
        if (! $sinceOrderAt) {
            return Carbon::now()->subMinutes(5);
        }

        try {
            return Carbon::parse($sinceOrderAt);
        } catch (\Throwable) {
            return Carbon::now()->subMinutes(5);
        }
    }
}
