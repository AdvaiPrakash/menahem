<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Order Placed</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background-color: #f7f8f6; padding: 24px 12px; margin: 0; color: #2B3326;">
    <div style="max-width: 620px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0;">
        <div style="background-color: #384230; padding: 24px; text-align: center; color: #ffffff;">
            <h1 style="margin: 0; font-size: 22px; letter-spacing: 3px; font-weight: 700;">MENAHEM</h1>
            <p style="margin: 6px 0 0; font-size: 12px; color: #B5C1A5;">New Order Placed</p>
        </div>

        <div style="padding: 26px 24px;">
            <p style="margin: 0 0 16px; font-size: 14px; color: #333333; line-height: 1.5;">
                A new order has been placed on the Menahem website.
            </p>

            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; margin-bottom: 20px;">
                <p style="margin: 4px 0; font-size: 13px;"><strong>Order Number:</strong> #{{ $order->order_number }}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Customer Name:</strong> {{ $order->customer_name }}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Email:</strong> {{ $order->customer_email ?: 'Not provided' }}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Phone:</strong> {{ $order->customer_phone ?: 'Not provided' }}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Order Status:</strong> {{ ucfirst((string) $order->order_status) }}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Payment Status:</strong> {{ ucfirst((string) $order->payment_status) }} ({{ $order->payment_method ?: 'Online' }})</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Placed At:</strong> {{ $order->created_at?->format('d M Y, h:i A') ?? now()->format('d M Y, h:i A') }}</p>
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
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align: right; padding: 10px 8px; font-weight: bold;">Order Total (Incl. GST):</td>
                        <td style="text-align: right; padding: 10px 8px; font-weight: bold;">&#8377;{{ number_format((float) $order->grand_total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div style="text-align: center; margin: 26px 0 8px;">
                <a href="{{ route('admin.orders.show', $order) }}"
                   style="background-color: #384230; color: #ffffff; text-decoration: none; padding: 12px 24px; font-weight: bold; font-size: 13px; display: inline-block; letter-spacing: 0.5px; text-transform: uppercase;">
                    View Order in Admin Panel
                </a>
            </div>
        </div>

        <div style="background-color: #f1f5f9; padding: 14px 24px; text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #e2e8f0;">
            &copy; {{ date('Y') }} {{ $businessName }}. Automated website order alert.
        </div>
    </div>
</body>
</html>
