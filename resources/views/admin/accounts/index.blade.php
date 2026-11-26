@extends('admin.layouts.app')

@section('title', 'Accounts & Billing Ledger')
@section('header_title', 'Accounts & Billing Ledger')

@section('header_actions')
    <a href="{{ route('admin.accounts.export-excel', request()->query()) }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-emerald-700 bg-emerald-50 text-emerald-900 hover:bg-emerald-700 hover:text-white text-xs font-bold uppercase tracking-wider transition-colors">
        <span>Export Excel</span>
    </a>
    <a href="{{ route('admin.accounts.export-pdf', request()->query()) }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-neutral-900 hover:bg-black text-white text-xs font-bold uppercase tracking-wider transition-colors">
        <span>Export PDF</span>
    </a>
    @if(auth()->user()->canEditContent())
        <a href="{{ route('admin.billing.create') }}"
           class="px-3.5 py-2 border border-neutral-300 text-neutral-800 hover:bg-neutral-100 text-xs font-bold uppercase tracking-wider transition-colors">
            + New Bill
        </a>
    @endif
@endsection

@section('content')
<div class="space-y-6">
    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white border border-neutral-200 p-4">
            <p class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Net Collections</p>
            <p class="text-xl font-black text-neutral-900 mt-1">₹{{ number_format($summary['gross_sales'], 2) }}</p>
            <p class="text-[11px] text-neutral-500 mt-0.5">{{ $summary['active_count'] }} active bill(s)</p>
        </div>

        <div class="bg-white border border-neutral-200 p-4">
            <p class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Total GST (Incl.)</p>
            <p class="text-xl font-black text-neutral-900 mt-1">₹{{ number_format($summary['total_gst'], 2) }}</p>
            <p class="text-[11px] text-neutral-500 mt-0.5">Taxable: ₹{{ number_format($summary['total_taxable'], 2) }}</p>
        </div>

        <div class="bg-white border border-neutral-200 p-4">
            <p class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Total Discounts</p>
            <p class="text-xl font-black text-emerald-700 mt-1">₹{{ number_format($summary['total_discount'], 2) }}</p>
            <p class="text-[11px] text-neutral-500 mt-0.5">Across active bills</p>
        </div>

        <div class="bg-white border border-neutral-200 p-4">
            <p class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Mode Breakdown</p>
            <div class="mt-1 space-y-0.5 text-[11px] text-neutral-700">
                <div class="flex justify-between"><span>UPI:</span><strong>₹{{ number_format($summary['upi_total'], 0) }}</strong></div>
                <div class="flex justify-between"><span>Cash:</span><strong>₹{{ number_format($summary['cash_total'], 0) }}</strong></div>
                <div class="flex justify-between"><span>Card / Online:</span><strong>₹{{ number_format($summary['card_total'] + $summary['online_total'], 0) }}</strong></div>
            </div>
        </div>

        <div class="bg-white border border-neutral-200 p-4 col-span-2 lg:col-span-1">
            <p class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Cancelled Bills</p>
            <p class="text-xl font-black text-rose-700 mt-1">{{ $summary['cancelled_count'] }}</p>
            <p class="text-[11px] text-neutral-500 mt-0.5">₹{{ number_format($summary['cancelled_amount'], 2) }} voided</p>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white border border-neutral-200 p-4">
        <form method="GET" action="{{ route('admin.accounts.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}"
                       class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}"
                       class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Payment Method</label>
                <select name="payment_method"
                        class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                    <option value="">All Methods</option>
                    <option value="UPI" {{ $filters['payment_method'] === 'UPI' ? 'selected' : '' }}>UPI</option>
                    <option value="Cash" {{ $filters['payment_method'] === 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Card" {{ $filters['payment_method'] === 'Card' ? 'selected' : '' }}>Card</option>
                    <option value="Split" {{ $filters['payment_method'] === 'Split' ? 'selected' : '' }}>Split Payment</option>
                    <option value="Razorpay" {{ $filters['payment_method'] === 'Razorpay' ? 'selected' : '' }}>Razorpay</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Status</label>
                <select name="status"
                        class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                    <option value="">All Bills</option>
                    <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Active (Not Cancelled)</option>
                    <option value="confirmed" {{ $filters['status'] === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="cancelled" {{ $filters['status'] === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] }}"
                       placeholder="Bill #, customer, phone..."
                       class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-neutral-900 hover:bg-black text-white text-xs font-bold uppercase tracking-wider cursor-pointer">
                    Filter
                </button>
                <a href="{{ route('admin.accounts.index') }}"
                   class="px-3 py-2 border border-neutral-300 text-neutral-700 hover:bg-neutral-100 text-xs font-bold uppercase tracking-wider">
                    Reset
                </a>
            </div>
        </form>

        <div class="mt-3 pt-3 border-t border-neutral-100 flex flex-wrap items-center gap-2 text-[11px]">
            <span class="text-neutral-500 font-semibold uppercase tracking-wider mr-1">Quick Dates:</span>
            <a href="{{ route('admin.accounts.index', array_merge(request()->except(['page', 'date_from', 'date_to']), ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString()])) }}"
               class="px-2.5 py-1 border border-neutral-300 hover:bg-neutral-100 font-semibold text-neutral-700">Today</a>
            <a href="{{ route('admin.accounts.index', array_merge(request()->except(['page', 'date_from', 'date_to']), ['date_from' => now()->startOfWeek()->toDateString(), 'date_to' => now()->toDateString()])) }}"
               class="px-2.5 py-1 border border-neutral-300 hover:bg-neutral-100 font-semibold text-neutral-700">This Week</a>
            <a href="{{ route('admin.accounts.index', array_merge(request()->except(['page', 'date_from', 'date_to']), ['date_from' => now()->startOfMonth()->toDateString(), 'date_to' => now()->toDateString()])) }}"
               class="px-2.5 py-1 border border-neutral-300 hover:bg-neutral-100 font-semibold text-neutral-700">This Month</a>
        </div>
    </div>

    <!-- Accounts Ledger Table -->
    <div class="bg-white border border-neutral-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-neutral-700">
                <thead class="bg-neutral-50 border-b border-neutral-200 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                    <tr>
                        <th class="py-3 px-4">Date &amp; Bill #</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Payment</th>
                        <th class="py-3 px-4 text-right">Subtotal / Disc.</th>
                        <th class="py-3 px-4 text-right">GST (Incl.)</th>
                        <th class="py-3 px-4 text-right">Grand Total</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200">
                    @forelse($orders as $order)
                        <tr class="hover:bg-neutral-50/70 {{ $order->isCancelled() ? 'bg-rose-50/30 opacity-75' : '' }}">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-bold font-mono text-neutral-900 hover:underline block">
                                    #{{ $order->order_number }}
                                </a>
                                <span class="text-[10px] text-neutral-500">{{ $order->created_at?->format('d M Y, h:i A') }}</span>
                                @if($order->invoice)
                                    <span class="block text-[10px] text-neutral-400 font-mono">{{ $order->invoice->invoice_number }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-neutral-900 block">{{ $order->customer_name }}</span>
                                <span class="text-[11px] text-neutral-500">{{ $order->customer_phone }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-neutral-900 block">{{ $order->payment_method ?: 'Razorpay' }}</span>
                                @if(is_array($order->payment_details) && count($order->payment_details) > 1)
                                    <div class="text-[10px] text-neutral-500 mt-0.5 space-y-0.5">
                                        @foreach($order->payment_details as $pSplit)
                                            <div>{{ $pSplit['method'] ?? '' }}: ₹{{ number_format((float) ($pSplit['amount'] ?? 0), 2) }}</div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <span class="block font-semibold text-neutral-900">₹{{ number_format((float) $order->subtotal, 2) }}</span>
                                @if((float) $order->discount_amount > 0)
                                    <span class="block text-[10px] font-semibold text-emerald-700">-₹{{ number_format((float) $order->discount_amount, 2) }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <span class="font-semibold text-neutral-800">₹{{ number_format((float) $order->total_tax, 2) }}</span>
                                <span class="block text-[10px] text-neutral-400">Taxable: ₹{{ number_format((float) $order->taxable_amount, 2) }}</span>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <span class="text-sm font-black text-neutral-900">₹{{ number_format((float) $order->grand_total, 2) }}</span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="inline-block px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border
                                    {{ $order->isCancelled()
                                        ? 'bg-rose-50 text-rose-800 border-rose-300'
                                        : 'bg-emerald-50 text-emerald-800 border-emerald-300' }}">
                                    {{ ucfirst((string) $order->order_status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                       class="px-2.5 py-1 border border-neutral-300 bg-white hover:bg-neutral-100 text-[10px] font-bold uppercase tracking-wider text-neutral-800">
                                        View
                                    </a>
                                    <a href="{{ route('admin.orders.invoice', $order) }}"
                                       class="px-2.5 py-1 border border-neutral-900 bg-neutral-900 hover:bg-black text-[10px] font-bold uppercase tracking-wider text-white">
                                        PDF
                                    </a>
                                    @if(! $order->isCancelled() && auth()->user()->canEditContent())
                                        <form action="{{ route('admin.accounts.cancel', $order) }}"
                                              method="POST"
                                              onsubmit="return confirm('Cancel bill #{{ $order->order_number }} and restore stock?');"
                                              class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-2.5 py-1 border border-rose-200 bg-rose-50 hover:bg-rose-700 hover:text-white text-[10px] font-bold uppercase tracking-wider text-rose-700 cursor-pointer">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-neutral-400">
                                No billing or account records found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-neutral-200">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
