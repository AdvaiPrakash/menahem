<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Menahem Accounts Ledger</title>
    <style>
        @page { margin: 20px 24px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #222; }
        .header { border-bottom: 2px solid #171717; padding-bottom: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; }
        h1 { font-size: 16px; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        .meta { font-size: 9px; color: #555; margin-top: 3px; }
        .summary-grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .summary-grid td { border: 1px solid #d4d4d4; padding: 6px 8px; background: #fafafa; }
        .label { font-size: 8px; text-transform: uppercase; color: #666; display: block; }
        .val { font-size: 12px; font-weight: bold; color: #111; }
        table.ledger { width: 100%; border-collapse: collapse; }
        table.ledger th { background: #171717; color: #fff; font-size: 8px; text-transform: uppercase; padding: 6px; text-align: left; }
        table.ledger td { border-bottom: 1px solid #e5e5e5; padding: 5px 6px; font-size: 9px; }
        .text-right { text-align: right; }
        .cancelled { color: #b91c1c; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Menahem – Accounts &amp; Billing Ledger</h1>
        <div class="meta">
            Generated: {{ $generatedAt->format('d M Y, h:i A') }}
            @if($filters['date_from'] || $filters['date_to'])
                &nbsp;|&nbsp; Period: {{ $filters['date_from'] ?: 'Start' }} to {{ $filters['date_to'] ?: 'Present' }}
            @endif
            @if($filters['payment_method'])
                &nbsp;|&nbsp; Method: {{ $filters['payment_method'] }}
            @endif
            @if($filters['status'])
                &nbsp;|&nbsp; Status: {{ ucfirst($filters['status']) }}
            @endif
        </div>
    </div>

    <table class="summary-grid">
        <tr>
            <td><span class="label">Active Bills</span><span class="val">{{ $summary['active_count'] }}</span></td>
            <td><span class="label">Net Collections</span><span class="val">&#8377;{{ number_format($summary['gross_sales'], 2) }}</span></td>
            <td><span class="label">Total Discounts</span><span class="val">&#8377;{{ number_format($summary['total_discount'], 2) }}</span></td>
            <td><span class="label">Total GST (Incl.)</span><span class="val">&#8377;{{ number_format($summary['total_gst'], 2) }}</span></td>
            <td><span class="label">UPI / Cash / Card+Online</span><span class="val">&#8377;{{ number_format($summary['upi_total'], 0) }} / &#8377;{{ number_format($summary['cash_total'], 0) }} / &#8377;{{ number_format($summary['card_total'] + $summary['online_total'], 0) }}</span></td>
            <td><span class="label">Cancelled</span><span class="val">{{ $summary['cancelled_count'] }} (&#8377;{{ number_format($summary['cancelled_amount'], 2) }})</span></td>
        </tr>
    </table>

    <table class="ledger">
        <thead>
            <tr>
                <th>Date</th>
                <th>Bill / Order #</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Payment Mode</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Taxable</th>
                <th class="text-right">GST</th>
                <th class="text-right">Grand Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                <tr class="{{ $order->isCancelled() ? 'cancelled' : '' }}">
                    <td>{{ $order->created_at?->format('d M Y') }}</td>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->customer_name }}</td>
                    <td>{{ $order->customer_phone }}</td>
                    <td>{{ $order->payment_method ?: 'Razorpay' }}</td>
                    <td class="text-right">&#8377;{{ number_format((float) $order->subtotal, 2) }}</td>
                    <td class="text-right">&#8377;{{ number_format((float) $order->discount_amount, 2) }}</td>
                    <td class="text-right">&#8377;{{ number_format((float) $order->taxable_amount, 2) }}</td>
                    <td class="text-right">&#8377;{{ number_format((float) $order->total_tax, 2) }}</td>
                    <td class="text-right"><strong>&#8377;{{ number_format((float) $order->grand_total, 2) }}</strong></td>
                    <td>{{ ucfirst((string) $order->order_status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
