<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Menahem Order Invoice</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f7f8f6; padding: 20px; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
        <div style="background-color: #384230; padding: 24px; text-align: center; color: #ffffff;">
            <h1 style="margin: 0; font-size: 24px; letter-spacing: 2px;">MENAHEM</h1>
            <p style="margin: 6px 0 0; font-size: 12px; color: #B5C1A5;">The one that comforts you</p>
        </div>

        <div style="padding: 24px;">
            <h2 style="color: #2B3326; font-size: 18px; margin-top: 0;">Thank you for your purchase, {{ $invoice->customer_name }}!</h2>
            <p style="font-size: 14px; line-height: 1.5; color: #555;">
                We have received your payment of <strong>₹{{ number_format($invoice->grand_total, 2) }}</strong> for Order <strong>#{{ $invoice->order->order_number }}</strong>. Your GST tax invoice PDF is attached to this email.
            </p>

            <div style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 16px; margin: 20px 0;">
                <p style="margin: 4px 0; font-size: 13px;"><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Date:</strong> {{ $invoice->invoice_date->format('d M Y') }}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Total Amount Paid:</strong> ₹{{ number_format($invoice->grand_total, 2) }}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Payment Method:</strong> {{ $invoice->payment_method }}</p>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $downloadUrl }}" 
                   style="background-color: #74826B; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; font-size: 14px; display: inline-block;">
                    Download Official GST Tax Invoice (PDF)
                </a>
            </div>

            <p style="font-size: 12px; color: #777; line-height: 1.4;">
                If you have any questions or require assistance, please contact us at +91 8129 4620 45 or email <a href="mailto:info@menahem.co.in" style="color: #384230;">info@menahem.co.in</a>.
            </p>
        </div>

        <div style="background-color: #f1f5f9; padding: 16px; text-align: center; font-size: 11px; color: #64748b;">
            <strong>MENAHEM</strong> • GSTIN: 32DDLPP5032G1ZN<br>
            Karunya, No. 52/1307, St. James Road, Thykoodam, Kochi, Ernakulam, Kerala 682019, India.<br>
            &copy; {{ date('Y') }} MENAHEM. All rights reserved.
        </div>
    </div>
</body>
</html>
