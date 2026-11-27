@extends('layouts.app')

@section('title', 'My Orders · Menahem')

@section('content')
<div class="min-h-screen bg-[#FBFBF9] pt-28 pb-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Account Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-6 border-b border-neutral-200 mb-8 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#2B3326]">My Order History</h1>
                <p class="text-xs sm:text-sm text-[#5C6654] mt-1">
                    Welcome back, {{ $user->name }} • {{ $user->email }}
                </p>
            </div>
            <form action="{{ route('customer.logout') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-lg border border-neutral-300 text-xs font-bold text-[#2B3326] hover:bg-neutral-100 transition-colors cursor-pointer">
                    Sign Out
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Orders Listing -->
        <div class="space-y-6">
            @forelse($orders as $order)
                <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6 overflow-hidden">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 border-b border-neutral-100 gap-3 text-xs">
                        <div>
                            <span class="font-mono font-bold text-sm text-[#2B3326]">#{{ $order->order_number }}</span>
                            <span class="text-slate-400 mx-2">•</span>
                            <span class="text-slate-500">{{ $order->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($order->isSimulatedPayment())
                                <span class="px-2.5 py-0.5 rounded-full font-bold text-[10px] uppercase tracking-wider bg-amber-100 text-amber-900">
                                    Simulated Test
                                </span>
                            @endif
                            <span class="px-2.5 py-0.5 rounded-full font-bold text-[11px] {{ $order->isPaid() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-slate-100 text-slate-700">
                                {{ ucfirst($order->order_status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="divide-y divide-neutral-100 my-4 text-xs">
                        @foreach($order->items as $item)
                            <div class="py-3 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    @if($item->image)
                                        <img src="{{ $item->image }}" alt="{{ $item->product_name }}" class="w-12 h-12 rounded-lg object-cover bg-neutral-100 border border-neutral-200">
                                    @endif
                                    <div>
                                        <h4 class="font-bold text-[#2B3326]">{{ $item->product_name }}</h4>
                                        @if($item->custom_size)
                                            <p class="text-[11px] text-[#74826B]">Custom Size: {{ $item->custom_size }}</p>
                                        @endif
                                        <p class="text-[11px] text-[#5C6654]">Qty: {{ $item->quantity }} × ₹{{ number_format($item->unit_price, 2) }}</p>
                                    </div>
                                </div>
                                <span class="font-bold text-[#2B3326]">₹{{ number_format($item->total_amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Summary & Invoice Action -->
                    <div class="pt-4 border-t border-neutral-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 text-xs">
                        <div>
                            @php $s = $order->shipping_address ?? []; @endphp
                            <p class="text-slate-500">
                                Delivering to: <strong class="text-slate-800">{{ $s['city'] ?? '' }}, {{ $s['state'] ?? '' }} - {{ $s['pin_code'] ?? '' }}</strong>
                            </p>
                        </div>
                        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
                            <div>
                                <span class="text-slate-500 text-[11px] block text-right">Grand Total</span>
                                <span class="text-base font-bold text-[#2B3326]">₹{{ number_format($order->grand_total, 2) }}</span>
                            </div>

                            @if($order->invoice)
                                <a href="{{ route('account.orders.invoice', ['orderNumber' => $order->order_number]) }}" 
                                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#384230] hover:bg-[#2C3424] text-white text-xs font-bold transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Download GST Invoice</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-neutral-200 p-12 text-center">
                    <p class="text-base font-serif font-bold text-[#2B3326]">No Orders Yet</p>
                    <p class="text-xs text-[#5C6654] mt-1 mb-6">Discover our curated collection of luxury home essentials.</p>
                    <a href="{{ route('products.index') }}" class="px-6 py-2.5 rounded-lg bg-[#74826B] hover:bg-[#636F5A] text-white text-xs font-bold transition-colors">
                        Explore Collections &rarr;
                    </a>
                </div>
            @endforelse
        </div>

        @if($orders->hasPages())
            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
