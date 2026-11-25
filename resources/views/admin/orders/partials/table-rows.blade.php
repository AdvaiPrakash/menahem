@forelse($orders as $order)
    <tr class="hover:bg-neutral-50 transition-colors">
        <td class="px-5 py-4">
            <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-bold text-neutral-900 hover:underline">
                {{ $order->order_number }}
            </a>
            <span class="text-[10px] text-neutral-400 block font-mono">{{ $order->items->count() }} items</span>
        </td>
        <td class="px-5 py-4">
            <span class="font-bold text-neutral-900 block">{{ $order->customer_name }}</span>
            <span class="text-xs text-neutral-500">{{ $order->customer_phone ?: $order->customer_email }}</span>
        </td>
        <td class="px-5 py-4 text-xs text-neutral-500 whitespace-nowrap">
            {{ $order->created_at->format('d M Y, h:i A') }}
        </td>
        <td class="px-5 py-4">
            <span class="font-bold text-neutral-900">₹{{ number_format($order->grand_total, 2) }}</span>
            @if($order->discount_amount > 0)
                <span class="text-[10px] text-neutral-500 block">Saved ₹{{ number_format($order->discount_amount, 2) }}</span>
            @endif
        </td>
        <td class="px-5 py-4">
            <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-none border
                {{ $order->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' :
                   ($order->payment_status === 'pending' ? 'bg-amber-50 text-amber-800 border-amber-300' :
                   ($order->payment_status === 'refunded' ? 'bg-blue-50 text-blue-800 border-blue-300' : 'bg-rose-50 text-rose-800 border-rose-300')) }}">
                {{ ucfirst($order->payment_status) }}
            </span>
        </td>
        <td class="px-5 py-4">
            <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-none border
                {{ $order->order_status === 'delivered' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' :
                   ($order->order_status === 'shipped' ? 'bg-blue-50 text-blue-800 border-blue-300' :
                   ($order->order_status === 'confirmed' ? 'bg-indigo-50 text-indigo-800 border-indigo-300' :
                   ($order->order_status === 'cancelled' ? 'bg-rose-50 text-rose-800 border-rose-300' : 'bg-amber-50 text-amber-800 border-amber-300'))) }}">
                {{ ucfirst($order->order_status) }}
            </span>
        </td>
        <td class="px-5 py-4 text-right">
            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('admin.orders.show', $order) }}"
                   class="px-2.5 py-1 text-xs font-semibold bg-neutral-900 hover:bg-black text-white">
                    View
                </a>
                <a href="{{ route('admin.orders.invoice', $order) }}"
                   class="px-2.5 py-1 text-xs font-semibold bg-white hover:bg-neutral-100 text-neutral-800 border border-neutral-300"
                   title="Download Invoice PDF">
                    Invoice
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="p-8 text-center text-neutral-400 text-sm">
            No orders matching your criteria.
        </td>
    </tr>
@endforelse
