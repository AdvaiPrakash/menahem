<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerLead;
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
        $query = CustomerLead::query();

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

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $leads = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $stats = [
            'total' => CustomerLead::count(),
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
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    /**
     * Display the specified customer lead and their complete order history.
     */
    public function show(CustomerLead $lead): View
    {
        $orders = Order::where('customer_email', $lead->email)
            ->with(['items', 'invoice'])
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.leads.show', [
            'lead' => $lead,
            'orders' => $orders,
        ]);
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
