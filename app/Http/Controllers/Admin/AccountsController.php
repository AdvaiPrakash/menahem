<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountsController extends Controller
{
    public function __construct(protected StockService $stockService) {}

    /**
     * Display the Accounts ledger with date, status, and payment method filters.
     */
    public function index(Request $request): View
    {
        $query = $this->buildFilteredQuery($request);

        $orders = (clone $query)
            ->with(['items', 'invoice'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $summary = $this->computeSummary(clone $query);

        return view('admin.accounts.index', [
            'orders' => $orders,
            'summary' => $summary,
            'filters' => [
                'date_from' => (string) $request->input('date_from', ''),
                'date_to' => (string) $request->input('date_to', ''),
                'payment_method' => (string) $request->input('payment_method', ''),
                'status' => (string) $request->input('status', ''),
                'search' => (string) $request->input('search', ''),
            ],
        ]);
    }

    /**
     * Export filtered accounts ledger as an Excel-compatible CSV file.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $orders = $this->buildFilteredQuery($request)
            ->with('invoice')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $filename = 'menahem-accounts-'.Carbon::now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($orders): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM for clean Excel opening
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Date',
                'Order / Bill #',
                'Invoice #',
                'Customer Name',
                'Phone',
                'Email',
                'Payment Method',
                'Split Breakdown',
                'Subtotal (Incl. GST)',
                'Discount',
                'Taxable Value',
                'CGST',
                'SGST',
                'IGST',
                'Total GST',
                'Shipping',
                'Gateway Fee',
                'Grand Total',
                'Order Status',
                'Payment Status',
            ]);

            foreach ($orders as $order) {
                $splitText = '';
                if (is_array($order->payment_details) && count($order->payment_details) > 0) {
                    $splitText = collect($order->payment_details)
                        ->map(static fn (array $p): string => ($p['method'] ?? 'Payment').': '.number_format((float) ($p['amount'] ?? 0), 2))
                        ->implode(' | ');
                }

                fputcsv($handle, [
                    $order->created_at?->format('Y-m-d H:i') ?? '',
                    $order->order_number,
                    $order->invoice?->invoice_number ?? '',
                    $order->customer_name,
                    $order->customer_phone,
                    $order->customer_email,
                    $order->payment_method ?: 'Razorpay',
                    $splitText,
                    number_format((float) $order->subtotal, 2, '.', ''),
                    number_format((float) $order->discount_amount, 2, '.', ''),
                    number_format((float) $order->taxable_amount, 2, '.', ''),
                    number_format((float) $order->cgst_amount, 2, '.', ''),
                    number_format((float) $order->sgst_amount, 2, '.', ''),
                    number_format((float) $order->igst_amount, 2, '.', ''),
                    number_format((float) $order->total_tax, 2, '.', ''),
                    number_format((float) $order->shipping_fee, 2, '.', ''),
                    number_format((float) $order->gateway_fee, 2, '.', ''),
                    number_format((float) $order->grand_total, 2, '.', ''),
                    ucfirst((string) $order->order_status),
                    ucfirst((string) $order->payment_status),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Export filtered accounts ledger as a formatted A4 PDF report.
     */
    public function exportPdf(Request $request): Response
    {
        $query = $this->buildFilteredQuery($request);
        $orders = (clone $query)
            ->with('invoice')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
        $summary = $this->computeSummary(clone $query);

        $pdf = Pdf::loadView('admin.accounts.pdf', [
            'orders' => $orders,
            'summary' => $summary,
            'filters' => [
                'date_from' => (string) $request->input('date_from', ''),
                'date_to' => (string) $request->input('date_to', ''),
                'payment_method' => (string) $request->input('payment_method', ''),
                'status' => (string) $request->input('status', ''),
                'search' => (string) $request->input('search', ''),
            ],
            'generatedAt' => Carbon::now(),
        ])->setPaper('a4', 'landscape')->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('menahem-accounts-'.Carbon::now()->format('Ymd-His').'.pdf');
    }

    /**
     * Cancel a bill/order from Accounts and restore product stock if applicable.
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless(auth()->user()?->canEditContent(), 403);

        if ($order->isCancelled()) {
            return back()->with('error', "Bill #{$order->order_number} is already cancelled.");
        }

        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $reason = trim((string) ($validated['cancellation_reason'] ?? ''));
        $noteEntry = '[Cancelled on '.Carbon::now()->format('d M Y, h:i A').' by '.(auth()->user()?->name ?? 'Admin').']'
            .($reason !== '' ? " Reason: {$reason}" : '');

        DB::transaction(function () use ($order, $noteEntry): void {
            $wasPaid = $order->isPaid();

            $order->update([
                'order_status' => 'cancelled',
                'payment_status' => $wasPaid ? 'refunded' : 'failed',
                'notes' => trim(($order->notes ? $order->notes."\n" : '').$noteEntry),
            ]);

            if ($wasPaid) {
                $this->stockService->restoreStockForOrder($order, "Bill #{$order->order_number} cancelled from Accounts");
            }
        });

        return back()->with('success', "Bill #{$order->order_number} has been cancelled and stock restored.");
    }

    /**
     * Build the filtered orders query for index and exports.
     */
    protected function buildFilteredQuery(Request $request): Builder
    {
        $query = Order::query();

        $dateFrom = trim((string) $request->input('date_from', ''));
        if ($dateFrom !== '') {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        $dateTo = trim((string) $request->input('date_to', ''));
        if ($dateTo !== '') {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $paymentMethod = trim((string) $request->input('payment_method', ''));
        if ($paymentMethod !== '') {
            if ($paymentMethod === 'Split') {
                $query->where('payment_method', 'like', 'Split%');
            } else {
                $query->where(function (Builder $q) use ($paymentMethod): void {
                    $q->where('payment_method', $paymentMethod)
                        ->orWhere('payment_method', 'like', "%{$paymentMethod}%");
                });
            }
        }

        $status = trim((string) $request->input('status', ''));
        if ($status !== '') {
            if ($status === 'cancelled') {
                $query->where('order_status', 'cancelled');
            } elseif ($status === 'active') {
                $query->where('order_status', '!=', 'cancelled');
            } else {
                $query->where('order_status', $status);
            }
        }

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Compute aggregate financial summary for the filtered query.
     *
     * @return array<string, int|float>
     */
    protected function computeSummary(Builder $query): array
    {
        $allOrders = $query->get([
            'id',
            'subtotal',
            'discount_amount',
            'taxable_amount',
            'total_tax',
            'grand_total',
            'order_status',
            'payment_status',
            'payment_method',
            'payment_details',
        ]);

        $active = $allOrders->where('order_status', '!=', 'cancelled');
        $cancelled = $allOrders->where('order_status', 'cancelled');

        $cashTotal = 0.0;
        $upiTotal = 0.0;
        $cardTotal = 0.0;
        $onlineTotal = 0.0;

        foreach ($active as $ord) {
            if (is_array($ord->payment_details) && count($ord->payment_details) > 0) {
                foreach ($ord->payment_details as $entry) {
                    $m = strtolower((string) ($entry['method'] ?? ''));
                    $amt = (float) ($entry['amount'] ?? 0);
                    if ($m === 'cash') {
                        $cashTotal += $amt;
                    } elseif ($m === 'upi') {
                        $upiTotal += $amt;
                    } elseif ($m === 'card') {
                        $cardTotal += $amt;
                    } else {
                        $onlineTotal += $amt;
                    }
                }
            } else {
                $m = strtolower((string) ($ord->payment_method ?? ''));
                $amt = (float) $ord->grand_total;
                if ($m === 'cash') {
                    $cashTotal += $amt;
                } elseif ($m === 'upi') {
                    $upiTotal += $amt;
                } elseif ($m === 'card') {
                    $cardTotal += $amt;
                } else {
                    $onlineTotal += $amt;
                }
            }
        }

        return [
            'total_count' => $allOrders->count(),
            'active_count' => $active->count(),
            'cancelled_count' => $cancelled->count(),
            'gross_sales' => round((float) $active->sum('grand_total'), 2),
            'total_discount' => round((float) $active->sum('discount_amount'), 2),
            'total_taxable' => round((float) $active->sum('taxable_amount'), 2),
            'total_gst' => round((float) $active->sum('total_tax'), 2),
            'cancelled_amount' => round((float) $cancelled->sum('grand_total'), 2),
            'cash_total' => round($cashTotal, 2),
            'upi_total' => round($upiTotal, 2),
            'card_total' => round($cardTotal, 2),
            'online_total' => round($onlineTotal, 2),
        ];
    }
}
