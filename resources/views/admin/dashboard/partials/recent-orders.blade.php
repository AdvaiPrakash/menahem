@forelse($recentOrders as $ord)
    <div class="p-3.5 hover:bg-neutral-50 transition-colors flex items-center justify-between gap-4 text-xs">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.orders.show', $ord) }}" class="font-mono font-bold text-neutral-900 hover:underline">{{ $ord->order_number }}</a>
                <span class="px-1.5 py-0.5 rounded-none font-bold text-[9px] uppercase tracking-wider {{ $ord->isPaid() ? 'bg-emerald-100 text-emerald-900 border border-emerald-300' : 'bg-amber-100 text-amber-900 border border-amber-300' }}">
                    {{ $ord->payment_status }}
                </span>
                <span class="px-1.5 py-0.5 rounded-none font-bold text-[9px] uppercase tracking-wider bg-neutral-100 text-neutral-700 border border-neutral-200">
                    {{ $ord->order_status }}
                </span>
                <span class="text-neutral-500 text-[11px]">({{ ucfirst($ord->customer_type) }})</span>
            </div>
            <p class="text-neutral-500 text-[11px] mt-0.5">{{ $ord->customer_name }} • {{ $ord->customer_email }}</p>
        </div>
        <div class="text-right shrink-0">
            <span class="text-xs font-black text-neutral-900 block">₹{{ number_format($ord->grand_total, 2) }}</span>
            @if($ord->invoice)
                <a href="{{ route('admin.orders.invoice', $ord) }}" class="text-neutral-800 hover:underline font-bold text-[11px]">
                    Download PDF
                </a>
            @else
                <span class="text-neutral-400 text-[10px]">{{ $ord->created_at->diffForHumans() }}</span>
            @endif
        </div>
    </div>
@empty
    <div class="p-6 text-center text-neutral-400 text-xs">
        No customer orders placed yet.
    </div>
@endforelse
