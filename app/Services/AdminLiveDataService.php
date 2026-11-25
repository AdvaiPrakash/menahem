<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminLiveDataService
{
    /**
     * Build dashboard analytics and recent activity data.
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(): array
    {
        $paidOrdersCount = Order::where('payment_status', 'paid')->count();
        $totalRevenue = (float) Order::where('payment_status', 'paid')->sum('grand_total');
        $totalInquiries = Inquiry::count();
        $newInquiriesCount = Inquiry::whereIn('status', ['new', 'pending'])->count();

        return [
            'paidOrdersCount' => $paidOrdersCount,
            'totalRevenue' => $totalRevenue,
            'totalInquiries' => $totalInquiries,
            'newInquiriesCount' => $newInquiriesCount,
            'recentOrders' => Order::with('invoice')->latest()->take(5)->get(),
            'recentInquiries' => Inquiry::latest()->take(5)->get(),
        ];
    }

    /**
     * Build filtered enquiries listing and status tab counts.
     *
     * @return array<string, mixed>
     */
    public function getInquiriesData(Request $request): array
    {
        $query = Inquiry::query()->withCount([
            'replies as sent_replies_count' => static fn ($q) => $q->whereIn('status', ['sent', 'accepted']),
            'replies as failed_replies_count' => static fn ($q) => $q->where('status', 'failed'),
        ]);

        $status = $request->input('status');
        if ($status) {
            if ($status === 'pending' || $status === 'new') {
                $query->whereIn('status', ['new', 'pending']);
            } else {
                $query->where('status', $status);
            }
        }

        $type = $request->input('type');
        if ($type) {
            $query->where('type', $type);
        }

        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('product_name', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $inquiries = $query->latest()->paginate(20)->withQueryString();

        $statusCounts = [
            'all' => Inquiry::count(),
            'pending' => Inquiry::whereIn('status', ['new', 'pending'])->count(),
            'replied' => Inquiry::where('status', 'replied')->count(),
            'failed' => Inquiry::where('status', 'failed')->count(),
            'closed' => Inquiry::where('status', 'closed')->count(),
        ];

        return [
            'inquiries' => $inquiries,
            'statusCounts' => $statusCounts,
            'currentStatus' => $status,
            'currentType' => $type,
            'search' => $search,
        ];
    }

    /**
     * Build filtered orders listing and KPI metrics.
     *
     * @return array<string, mixed>
     */
    public function getOrdersData(Request $request): array
    {
        $search = $request->input('search');
        $paymentStatus = $request->input('payment_status');
        $orderStatus = $request->input('order_status');

        $query = Order::with(['items', 'invoice', 'user'])->latest('created_at');

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('razorpay_payment_id', 'like', "%{$search}%");
            });
        }

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($orderStatus) {
            $query->where('order_status', $orderStatus);
        }

        $orders = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Order::count(),
            'paid' => Order::where('payment_status', 'paid')->count(),
            'processing' => Order::where('order_status', 'processing')->count(),
            'shipped' => Order::where('order_status', 'shipped')->count(),
            'delivered' => Order::where('order_status', 'delivered')->count(),
            'total_revenue' => (float) Order::where('payment_status', 'paid')->sum('grand_total'),
        ];

        return [
            'orders' => $orders,
            'stats' => $stats,
            'search' => $search,
            'paymentStatus' => $paymentStatus,
            'orderStatus' => $orderStatus,
        ];
    }
}
