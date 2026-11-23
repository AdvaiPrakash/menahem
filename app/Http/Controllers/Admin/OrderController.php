<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(protected InvoiceService $invoiceService) {}

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $paymentStatus = $request->input('payment_status');
        $orderStatus = $request->input('order_status');

        $query = Order::with(['items', 'invoice', 'user'])->latest('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
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

        // Metrics for dashboard counters
        $stats = [
            'total' => Order::count(),
            'paid' => Order::where('payment_status', 'paid')->count(),
            'processing' => Order::where('order_status', 'processing')->count(),
            'shipped' => Order::where('order_status', 'shipped')->count(),
            'delivered' => Order::where('order_status', 'delivered')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('grand_total'),
        ];

        return view('admin.orders.index', [
            'orders' => $orders,
            'stats' => $stats,
            'search' => $search,
            'paymentStatus' => $paymentStatus,
            'orderStatus' => $orderStatus,
        ]);
    }

    /**
     * Display the specified order details.
     */
    public function show(Order $order): View
    {
        $order->load(['items.product', 'invoice', 'coupon', 'user']);

        return view('admin.orders.show', [
            'order' => $order,
            'invoice' => $order->invoice,
        ]);
    }

    /**
     * Update order status, payment status, and admin notes.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'order_status' => ['required', 'string', 'in:processing,confirmed,shipped,delivered,cancelled'],
            'payment_status' => ['required', 'string', 'in:paid,pending,failed,refunded'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order->order_status = $validated['order_status'];
        $order->payment_status = $validated['payment_status'];

        if ($validated['payment_status'] === 'paid' && ! $order->paid_at) {
            $order->paid_at = now();
        }

        if (isset($validated['notes'])) {
            $order->notes = $validated['notes'];
        }

        $order->save();

        // Auto-generate invoice if order is marked paid and doesn't have an invoice yet
        if ($order->payment_status === 'paid' && ! $order->invoice) {
            $this->invoiceService->generateForOrder($order);
        }

        return back()->with('success', "Order #{$order->order_number} status updated successfully.");
    }

    /**
     * Download or generate invoice PDF directly from order screen.
     */
    public function downloadInvoice(Order $order): Response|RedirectResponse
    {
        $invoice = $order->invoice;

        if (! $invoice) {
            // Auto generate invoice if needed
            $invoice = $this->invoiceService->generateForOrder($order);
        }

        $order->load('items');
        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'order' => $order,
        ]);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}
