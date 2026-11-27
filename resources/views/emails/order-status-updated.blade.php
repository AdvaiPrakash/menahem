<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Status Update</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background-color: #f7f8f6; padding: 24px 12px; margin: 0; color: #2B3326;">
    <div style="max-width: 620px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0;">
        <div style="background-color: #384230; padding: 24px; text-align: center; color: #ffffff;">
            <h1 style="margin: 0; font-size: 22px; letter-spacing: 3px; font-weight: 700;">MENAHEM</h1>
            <p style="margin: 6px 0 0; font-size: 12px; color: #B5C1A5;">Order Fulfillment Update</p>
        </div>

        <div style="padding: 26px 24px;">
            <h2 style="margin: 0 0 12px; font-size: 18px; color: #2B3326;">Hello {{ $order->customer_name }},</h2>
            <p style="margin: 0 0 16px; font-size: 14px; color: #333333; line-height: 1.5;">
                Your order <strong>#{{ $order->order_number }}</strong> status has been updated to:
                <strong style="color: #384230;">{{ ucfirst((string) $order->order_status) }}</strong>.
            </p>

            @if($order->order_status === 'shipped' && ! empty($order->notes))
                <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-left: 3px solid #384230; padding: 14px 16px; margin-bottom: 20px;">
                    <p style="margin: 0 0 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; color: #384230;">
                        Shipping &amp; Tracking Details
                    </p>
                    <p style="margin: 0; font-size: 13px; color: #1e293b; white-space: pre-line;">{{ $order->notes }}</p>
                </div>
            @endif

            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; margin-bottom: 20px;">
                <p style="margin: 4px 0; font-size: 13px;"><strong>Order Number:</strong> #{{ $order->order_number }}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>New Status:</strong> {{ ucfirst((string) $order->order_status) }}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Payment Status:</strong> {{ ucfirst((string) $order->payment_status) }}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Order Total:</strong> &#8377;{{ number_format((float) $order->grand_total, 2) }}</p>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
                <thead>
                    <tr style="background-color: #f4f5f3; border-bottom: 1px solid #ddd;">
                        <th style="text-align: left; padding: 8px;">Product</th>
                        <th style="text-align: center; padding: 8px;">Qty</th>
                        <th style="text-align: right; padding: 8px;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 8px;">{{ $item->product_name }}</td>
                            <td style="text-align: center; padding: 8px;">{{ $item->quantity }}</td>
                            <td style="text-align: right; padding: 8px;">&#8377;{{ number_format((float) $item->total_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p style="font-size: 12px; color: #64748b; line-height: 1.5;">
                If you have any questions about your order, please contact us at +91 8129 4620 45 or email <a href="mailto:info@menahem.co.in" style="color: #384230;">info@menahem.co.in</a>.
            </p>
        </div>

        <div style="background-color: #f1f5f9; padding: 14px 24px; text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #e2e8f0;">
            &copy; {{ date('Y') }} {{ $businessName }}. Thank you for shopping with Menahem.
        </div>
    </div>
</body>
</html>
