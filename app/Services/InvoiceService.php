<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceService
{
    /**
     * Generate a GST Tax Invoice for a paid order.
     */
    public function generateForOrder(Order $order): Invoice
    {
        // Check if invoice already exists
        if ($order->invoice) {
            return $order->invoice;
        }

        $year = Carbon::now()->year;
        $count = Invoice::whereYear('created_at', $year)->count() + 1;
        $invoiceNumber = sprintf('INV-%d-%05d', $year, $count);
        $pdfToken = Str::random(48);

        $officialAddress = "Karunya,\nNo. 52/1307, St. James Road,\nThykoodam, Kochi,\nErnakulam, Kerala.\nKochi 682019, Kerala, India.";

        $sellerName = Setting::get('seller_name', 'MENAHEM') ?: 'MENAHEM';
        $sellerGstin = Setting::get('seller_gstin', '32DDLPP5032G1ZN') ?: '32DDLPP5032G1ZN';
        $sellerAddress = Setting::get('seller_address', $officialAddress) ?: $officialAddress;
        $sellerState = Setting::get('seller_state', 'Kerala') ?: 'Kerala';
        $sellerStateCode = Setting::get('seller_state_code', '32') ?: '32';
        $sellerPhone = Setting::get('seller_phone', '+91 8129 4620 45') ?: '+91 8129 4620 45';
        $sellerEmail = Setting::get('seller_email', 'info@menahem.co.in') ?: 'info@menahem.co.in';

        $invoice = Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => Carbon::now()->toDateString(),
            'user_id' => $order->user_id,
            'customer_type' => $order->customer_type,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'shipping_address' => $order->shipping_address,
            'billing_address' => $order->billing_address ?: $order->shipping_address,
            'customer_gstin' => $order->customer_gstin,
            'seller_name' => $sellerName,
            'seller_gstin' => $sellerGstin,
            'seller_address' => $sellerAddress,
            'seller_state' => $sellerState,
            'seller_state_code' => $sellerStateCode,
            'seller_phone' => $sellerPhone,
            'seller_email' => $sellerEmail,
            'subtotal' => $order->subtotal,
            'discount' => $order->discount_amount,
            'taxable_amount' => $order->taxable_amount,
            'cgst_amount' => $order->cgst_amount,
            'sgst_amount' => $order->sgst_amount,
            'igst_amount' => $order->igst_amount,
            'shipping_charges' => $order->shipping_fee,
            'gateway_charges' => $order->gateway_fee,
            'grand_total' => $order->grand_total,
            'amount_paid' => $order->grand_total,
            'payment_method' => $order->payment_method ?: 'Razorpay',
            'payment_reference' => $order->razorpay_payment_id ?: $order->order_number,
            'payment_status' => 'paid',
            'pdf_token' => $pdfToken,
        ]);

        // Generate and save PDF
        try {
            $pdf = $this->renderPdf($invoice);
            $fileName = "invoices/{$invoiceNumber}.pdf";
            Storage::disk('public')->put($fileName, $pdf->output());
            $invoice->update(['pdf_path' => $fileName]);
        } catch (\Throwable $e) {
            Log::error('Invoice PDF generation failed: '.$e->getMessage());
        }

        // Email invoice to customer
        $this->emailInvoice($invoice);

        return $invoice;
    }

    /**
     * Render the DomPDF object for an invoice.
     */
    public function renderPdf(Invoice $invoice)
    {
        $order = $invoice->order()->with('items')->first();

        return Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'order' => $order,
        ])->setPaper('a4', 'portrait')->setOption('defaultFont', 'DejaVu Sans');
    }

    /**
     * Send email notification with invoice details and PDF attachment to customer.
     */
    protected function emailInvoice(Invoice $invoice): void
    {
        try {
            $customerEmail = $invoice->customer_email;
            if (empty($customerEmail) || ! filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
                return;
            }

            $downloadUrl = route('invoices.download.token', ['token' => $invoice->pdf_token]);
            $pdfFullPath = $invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)
                ? Storage::disk('public')->path($invoice->pdf_path)
                : null;

            Mail::send('emails.invoice', [
                'invoice' => $invoice,
                'downloadUrl' => $downloadUrl,
            ], function ($message) use ($invoice, $customerEmail, $pdfFullPath) {
                $message->to($customerEmail, $invoice->customer_name)
                    ->subject("Tax Invoice #{$invoice->invoice_number} for Order #{$invoice->order->order_number} - MENAHEM");

                if ($pdfFullPath && is_file($pdfFullPath)) {
                    $message->attach($pdfFullPath, [
                        'as' => "{$invoice->invoice_number}.pdf",
                        'mime' => 'application/pdf',
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::info('Invoice email sending skipped or logged: '.$e->getMessage());
        }
    }
}
