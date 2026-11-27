<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerLead;
use App\Models\Inquiry;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerLeadController extends Controller
{
    /**
     * Unified Customer Leads Management.
     */
    public function index(Request $request): View
    {
        $query = CustomerLead::query()->withCount(['orders', 'inquiries']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('customer_type')) {
            $query->where('customer_type', $type);
        }

        if ($status = $request->input('status')) {
            if ($status === 'ordered') {
                $query->where(function ($q): void {
                    $q->where('total_orders', '>', 0)->orWhereHas('orders');
                });
            } elseif ($status === 'enquiry_only') {
                $query->where('total_orders', 0)
                    ->whereDoesntHave('orders')
                    ->whereHas('inquiries');
            } elseif ($status === 'lead') {
                $query->where('total_orders', 0)
                    ->whereDoesntHave('orders')
                    ->whereDoesntHave('inquiries');
            }
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $leads = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $stats = [
            'total' => CustomerLead::count(),
            'ordered' => CustomerLead::where(function ($q): void {
                $q->where('total_orders', '>', 0)->orWhereHas('orders');
            })->count(),
            'enquiry_only' => CustomerLead::where('total_orders', 0)
                ->whereDoesntHave('orders')
                ->whereHas('inquiries')
                ->count(),
            'guest' => CustomerLead::where('customer_type', 'guest')->count(),
            'registered' => CustomerLead::where('customer_type', 'registered')->count(),
            'google' => CustomerLead::where('customer_type', 'google')->count(),
            'total_revenue' => (float) CustomerLead::sum('total_purchase_amount'),
        ];

        return view('admin.leads.index', [
            'leads' => $leads,
            'stats' => $stats,
            'search' => $search,
            'typeFilter' => $type,
            'statusFilter' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    /**
     * Display the specified customer lead, their enquiries, and their complete order history.
     */
    public function show(CustomerLead $lead): View
    {
        $orders = $this->getOrdersForLead($lead);
        $inquiries = $this->getInquiriesForLead($lead);

        $samePhoneLeads = ! empty($lead->phone)
            ? CustomerLead::where('phone', $lead->phone)
                ->where('id', '!=', $lead->id)
                ->orderBy('id', 'desc')
                ->get()
            : collect();

        $leadStatus = $lead->resolvedStatus(
            max($orders->count(), (int) $lead->total_orders),
            $inquiries->count()
        );

        return view('admin.leads.show', [
            'lead' => $lead,
            'orders' => $orders,
            'inquiries' => $inquiries,
            'samePhoneLeads' => $samePhoneLeads,
            'leadStatus' => $leadStatus,
        ]);
    }

    /**
     * Download complete details, order history, manual bills, and invoices for a single customer.
     */
    public function download(CustomerLead $lead): StreamedResponse
    {
        $orders = $this->getOrdersForLead($lead);
        $inquiries = $this->getInquiriesForLead($lead);
        $isSynthetic = str_ends_with((string) $lead->email, '@menahem.local')
            || str_ends_with((string) $lead->email, '@enquiry.local');

        $filename = 'customer-profile-'.$lead->id.'-'.Carbon::now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($lead, $orders, $inquiries, $isSynthetic): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            // Customer Profile Summary Table
            fputcsv($handle, ['CUSTOMER PROFILE']);
            fputcsv($handle, ['Customer ID', 'Name', 'Type', 'Phone', 'Email', 'Address', 'Total Orders', 'Total Spent']);
            fputcsv($handle, [
                '#CUS-'.str_pad((string) $lead->id, 4, '0', STR_PAD_LEFT),
                $lead->name,
                ucfirst((string) $lead->customer_type),
                $lead->phone ?: 'N/A',
                $isSynthetic ? 'Not provided' : $lead->email,
                $lead->delivery_address ?: 'N/A',
                $orders->count(),
                number_format((float) $lead->total_purchase_amount, 2, '.', ''),
            ]);

            fputcsv($handle, []);

            // Orders & Manual Billing Table
            fputcsv($handle, ['ORDERS & MANUAL BILLING HISTORY']);
            fputcsv($handle, [
                'Date',
                'Order / Bill #',
                'Source',
                'Invoice #',
                'Products & Quantities',
                'Payment Method',
                'Subtotal',
                'Discount',
                'Taxable',
                'CGST',
                'SGST',
                'Grand Total',
                'Order Status',
                'Payment Status',
            ]);

            foreach ($orders as $order) {
                $isManual = (($order->shipping_address['address_line1'] ?? '') === 'Manual Billing / Store Counter')
                    || in_array((string) $order->payment_method, ['Cash', 'UPI', 'Card'], true)
                    || str_starts_with((string) $order->payment_method, 'Split');

                $itemsSummary = $order->items
                    ->map(static fn ($item): string => "{$item->product_name} x {$item->quantity}")
                    ->implode(' | ');

                fputcsv($handle, [
                    $order->created_at?->format('Y-m-d H:i') ?? '',
                    $order->order_number,
                    $isManual ? 'Manual Billing' : 'Website Order',
                    $order->invoice?->invoice_number ?? 'N/A',
                    $itemsSummary,
                    $order->payment_method ?: 'Online',
                    number_format((float) $order->subtotal, 2, '.', ''),
                    number_format((float) $order->discount_amount, 2, '.', ''),
                    number_format((float) $order->taxable_amount, 2, '.', ''),
                    number_format((float) $order->cgst_amount, 2, '.', ''),
                    number_format((float) $order->sgst_amount, 2, '.', ''),
                    number_format((float) $order->grand_total, 2, '.', ''),
                    ucfirst((string) $order->order_status),
                    ucfirst((string) $order->payment_status),
                ]);
            }

            if ($inquiries->isNotEmpty()) {
                fputcsv($handle, []);
                fputcsv($handle, ['CUSTOMER ENQUIRIES']);
                fputcsv($handle, ['Date', 'Subject', 'Status', 'Message']);
                foreach ($inquiries as $inq) {
                    fputcsv($handle, [
                        $inq->created_at?->format('Y-m-d H:i') ?? '',
                        $inq->resolvedSubject(),
                        $inq->statusLabel(),
                        $inq->message ?? '',
                    ]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Fetch all orders and manual bills belonging to a customer lead.
     */
    protected function getOrdersForLead(CustomerLead $lead)
    {
        $isSynthetic = str_ends_with((string) $lead->email, '@menahem.local')
            || str_ends_with((string) $lead->email, '@enquiry.local');

        return Order::query()
            ->where(function ($q) use ($lead, $isSynthetic): void {
                if (! $isSynthetic) {
                    $q->where('customer_email', $lead->email);
                }
                if ($lead->user_id) {
                    $q->orWhere('user_id', $lead->user_id);
                }
                if (! empty($lead->phone) && ! empty($lead->name)) {
                    $q->orWhere(function ($sub) use ($lead): void {
                        $sub->where('customer_phone', $lead->phone)
                            ->whereRaw('LOWER(TRIM(customer_name)) = ?', [strtolower(trim((string) $lead->name))]);
                    });
                }
            })
            ->with(['items', 'invoice'])
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Fetch all website enquiries belonging to a customer lead.
     */
    protected function getInquiriesForLead(CustomerLead $lead)
    {
        $isSynthetic = str_ends_with((string) $lead->email, '@menahem.local')
            || str_ends_with((string) $lead->email, '@enquiry.local');

        return Inquiry::query()
            ->where(function ($q) use ($lead, $isSynthetic): void {
                if (! $isSynthetic) {
                    $q->where('email', $lead->email);
                } elseif (! empty($lead->phone)) {
                    $q->whereNull('email')->where('phone', $lead->phone);
                }
            })
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Export customer leads to a CSV file.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = CustomerLead::query();

        if ($type = $request->input('customer_type')) {
            $query->where('customer_type', $type);
        }

        $leads = $query->orderBy('id', 'desc')->get();

        $filename = 'menahem_customer_leads_'.Carbon::now()->format('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($leads) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");

            // CSV Header
            fputcsv($file, [
                'Customer ID',
                'Customer Name',
                'Email Address',
                'Mobile Number',
                'Customer Type',
                'Delivery Address',
                'City',
                'State',
                'PIN Code',
                'Customer GSTIN',
                'Registration Date',
                'First Purchase Date',
                'Last Purchase Date',
                'Total Orders',
                'Total Purchase Amount (INR)',
            ]);

            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->id,
                    $lead->name,
                    $lead->email,
                    $lead->phone,
                    ucfirst($lead->customer_type),
                    $lead->delivery_address,
                    $lead->city,
                    $lead->state,
                    $lead->pin_code,
                    $lead->customer_gstin,
                    $lead->registration_date ? $lead->registration_date->format('Y-m-d H:i') : 'N/A',
                    $lead->first_purchase_date ? $lead->first_purchase_date->format('Y-m-d H:i') : 'N/A',
                    $lead->last_purchase_date ? $lead->last_purchase_date->format('Y-m-d H:i') : 'N/A',
                    $lead->total_orders,
                    number_format($lead->total_purchase_amount, 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
