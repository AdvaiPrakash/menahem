<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Display a listing of generated GST invoices.
     */
    public function index(Request $request): View
    {
        $query = Invoice::query()->with(['order']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_number', 'like', "%{$search}%")
                            ->orWhere('razorpay_payment_id', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('payment_status')) {
            $query->where('payment_status', $status);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('invoice_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('invoice_date', '<=', $dateTo);
        }

        $invoices = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total_invoices' => Invoice::count(),
            'total_revenue' => (float) Invoice::where('payment_status', 'paid')->sum('grand_total'),
            'total_gst_collected' => (float) Invoice::where('payment_status', 'paid')->sum(DB::raw('cgst_amount + sgst_amount + igst_amount')),
        ];

        return view('admin.invoices.index', [
            'invoices' => $invoices,
            'stats' => $stats,
            'search' => $search,
            'statusFilter' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    /**
     * Display the specified invoice details.
     */
    public function show(Invoice $invoice): View
    {
        $order = $invoice->order()->with('items')->first();

        return view('admin.invoices.show', [
            'invoice' => $invoice,
            'order' => $order,
        ]);
    }

    /**
     * Download the invoice PDF.
     */
    public function download(Invoice $invoice)
    {
        $pdf = $this->invoiceService->renderPdf($invoice);

        return $pdf->download("{$invoice->invoice_number}.pdf");
    }
}
