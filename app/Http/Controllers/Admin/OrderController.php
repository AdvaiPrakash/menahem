<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AdminLiveDataService;
use App\Services\InvoiceService;
use App\Services\OrderEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService,
        protected AdminLiveDataService $liveDataService,
        protected OrderEmailService $orderEmailService
    ) {}

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): View
    {
        return view('admin.orders.index', $this->liveDataService->getOrdersData($request));
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

        $previousOrderStatus = (string) $order->order_status;

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

        // Send fulfillment status email to customer only when order_status changed
        if ($previousOrderStatus !== (string) $order->order_status) {
            $this->orderEmailService->sendOrderStatusUpdate($order, $previousOrderStatus);
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

        $pdf = $this->invoiceService->renderPdf($invoice);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}
