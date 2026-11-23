<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Auth;

class InvoiceDownloadController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Download invoice securely by public token (supports guest checkout).
     */
    public function downloadByToken(string $token)
    {
        $invoice = Invoice::where('pdf_token', $token)->firstOrFail();

        $pdf = $this->invoiceService->renderPdf($invoice);

        return $pdf->download("{$invoice->invoice_number}.pdf");
    }

    /**
     * Download invoice for authenticated user order.
     */
    public function downloadForCustomer(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        // Ensure user owns this order or is admin
        if (! Auth::check() || (Auth::id() !== $order->user_id && ! Auth::user()->isAdmin())) {
            abort(403, 'Unauthorized access to invoice.');
        }

        $invoice = $order->invoice;
        if (! $invoice) {
            abort(404, 'Invoice not found for this order.');
        }

        $pdf = $this->invoiceService->renderPdf($invoice);

        return $pdf->download("{$invoice->invoice_number}.pdf");
    }
}
