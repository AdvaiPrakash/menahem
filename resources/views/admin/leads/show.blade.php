@extends('admin.layouts.app')

@section('title', "Customer: {$lead->name}")
@section('header_title', "Customer Profile: {$lead->name}")

@section('header_actions')
    <a href="{{ route('admin.leads.download', $lead) }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-none bg-neutral-900 hover:bg-black text-white text-xs font-bold uppercase tracking-wider transition-colors">
        <span>Download Customer Details</span>
    </a>
    <a href="{{ route('admin.leads.index') }}" 
       class="px-3.5 py-2 rounded-none border border-neutral-300 text-neutral-700 text-xs font-semibold uppercase tracking-wider hover:bg-neutral-100 transition-colors">
        Back to Leads
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Customer Overview Card -->
    <div class="bg-white rounded-none border border-neutral-200 shadow-none p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b border-neutral-200">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-none bg-neutral-100 text-neutral-900 font-bold text-xl flex items-center justify-center border border-neutral-300">
                    {{ strtoupper(substr($lead->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-neutral-900">{{ $lead->name }}</h2>
                        <span class="px-2.5 py-0.5 rounded-none text-[10px] font-bold uppercase tracking-wider border
                            {{ $leadStatus === 'Ordered' ? 'bg-emerald-50 text-emerald-900 border-emerald-300' : ($leadStatus === 'Enquiry Only' ? 'bg-amber-50 text-amber-900 border-amber-300' : 'bg-neutral-100 text-neutral-800 border-neutral-300') }}">
                            {{ $leadStatus }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-none text-[10px] font-bold uppercase tracking-wider border
                            {{ $lead->customer_type === 'google' ? 'bg-purple-50 text-purple-900 border-purple-200' : ($lead->customer_type === 'registered' ? 'bg-blue-50 text-blue-900 border-blue-200' : 'bg-neutral-100 text-neutral-800 border-neutral-300') }}">
                            {{ ucfirst($lead->customer_type) }} Customer
                        </span>
                    </div>
                    <p class="text-xs text-neutral-500 mt-1">Customer ID: <span class="font-mono font-semibold">#CUS-{{ str_pad($lead->id, 4, '0', STR_PAD_LEFT) }}</span></p>
                </div>
            </div>

            <div class="flex gap-4 text-right">
                <div>
                    <span class="text-xs text-neutral-400 block uppercase tracking-wider">Total Orders</span>
                    <span class="text-lg font-bold text-neutral-900">{{ $lead->total_orders }}</span>
                </div>
                <div>
                    <span class="text-xs text-neutral-400 block uppercase tracking-wider">Total Spent</span>
                    <span class="text-lg font-bold text-neutral-900">₹{{ number_format($lead->total_purchase_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-6 text-xs">
            <div>
                <h4 class="font-bold uppercase tracking-wider text-neutral-400 mb-1">Contact Information</h4>
                <p class="font-medium text-neutral-900">{{ $lead->phone ?: 'No phone provided' }}</p>
                <p class="text-neutral-600">{{ (str_ends_with((string) $lead->email, '@enquiry.local') || str_ends_with((string) $lead->email, '@menahem.local')) ? 'No email provided' : $lead->email }}</p>
                @if($lead->customer_gstin)
                    <p class="mt-1 font-semibold text-neutral-900">GSTIN: {{ $lead->customer_gstin }}</p>
                @endif
            </div>

            <div>
                <h4 class="font-bold uppercase tracking-wider text-neutral-400 mb-1">Primary Delivery Address</h4>
                <p class="text-neutral-700 whitespace-pre-line">{{ $lead->delivery_address ?: 'No delivery address recorded yet.' }}</p>
            </div>

            <div>
                <h4 class="font-bold uppercase tracking-wider text-neutral-400 mb-1">Customer Timeline</h4>
                <p class="text-neutral-600">Registered: <strong>{{ $lead->registration_date ? $lead->registration_date->format('d M Y') : 'N/A' }}</strong></p>
                <p class="text-neutral-600 mt-1">First Purchase: <strong>{{ $lead->first_purchase_date ? $lead->first_purchase_date->format('d M Y') : 'N/A' }}</strong></p>
                <p class="text-neutral-600 mt-1">Last Purchase: <strong>{{ $lead->last_purchase_date ? $lead->last_purchase_date->format('d M Y') : 'N/A' }}</strong></p>
            </div>
        </div>
    </div>

    <!-- Connected Orders History -->
    <div class="bg-white rounded-none border border-neutral-200 shadow-none overflow-hidden">
        <div class="p-5 border-b border-neutral-200">
            <h3 class="text-base font-bold text-neutral-900">Purchase & Order History ({{ $orders->count() }})</h3>
            <p class="text-xs text-neutral-500">Every purchase placed under this customer's email</p>
        </div>

        <div class="divide-y divide-neutral-200">
            @forelse($orders as $order)
                <div class="p-5 hover:bg-neutral-50 transition-colors">
                    <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-3">
                        @php
                            $isManualBill = (($order->shipping_address['address_line1'] ?? '') === 'Manual Billing / Store Counter')
                                || in_array((string) $order->payment_method, ['Cash', 'UPI', 'Card'], true)
                                || str_starts_with((string) $order->payment_method, 'Split');
                        @endphp
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-bold text-sm text-neutral-900 hover:underline">{{ $order->order_number }}</a>
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-none border {{ $isManualBill ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-neutral-100 text-neutral-800 border-neutral-300' }}">
                                    {{ $isManualBill ? 'Manual Billing' : 'Website Order' }}
                                </span>
                                <span class="px-2 py-0.5 text-[11px] font-bold rounded-none border {{ $order->isPaid() ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </div>
                            <p class="text-xs text-neutral-500 mt-0.5">Placed on {{ $order->created_at->format('d M Y, h:i A') }} • Payment Ref: <span class="font-mono">{{ $order->razorpay_payment_id ?: 'Pending' }}</span></p>
                        </div>

                        <div class="text-right">
                            <span class="text-base font-bold text-neutral-900">₹{{ number_format($order->grand_total, 2) }}</span>
                            @if($order->invoice)
                                <a href="{{ route('admin.orders.invoice', $order) }}" 
                                   class="block mt-1 text-xs font-semibold text-neutral-900 underline hover:text-black">
                                    Download Invoice PDF &rarr;
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Items Preview -->
                    <div class="bg-neutral-50 rounded-none p-3 border border-neutral-200">
                        <div class="divide-y divide-neutral-200">
                            @foreach($order->items as $item)
                                <div class="py-2 first:pt-0 last:pb-0 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-neutral-800">{{ $item->product_name }}</span>
                                        @if($item->custom_size)
                                            <span class="text-[10px] px-1.5 py-0.2 bg-white rounded-none border border-neutral-200 text-neutral-600">{{ $item->custom_size }}</span>
                                        @endif
                                        <span class="text-neutral-400">× {{ $item->quantity }}</span>
                                    </div>
                                    <span class="font-medium text-neutral-700">₹{{ number_format($item->total_amount, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-neutral-400 text-sm">
                    No orders recorded for this customer lead yet.
                </div>
            @endforelse
        </div>
    </div>

    @if(isset($samePhoneLeads) && $samePhoneLeads->isNotEmpty())
        <!-- Other Customer Records Sharing the Same Phone Number -->
        <div class="bg-white rounded-none border border-neutral-200 shadow-none overflow-hidden">
            <div class="p-5 border-b border-neutral-200 bg-neutral-50/70">
                <h3 class="text-sm font-bold uppercase tracking-wider text-neutral-900">
                    Other Customer Profiles Sharing Phone ({{ $lead->phone }})
                </h3>
                <p class="text-xs text-neutral-500 mt-0.5">Individual customer names are preserved separately even when sharing a phone number.</p>
            </div>
            <div class="divide-y divide-neutral-200">
                @foreach($samePhoneLeads as $other)
                    <div class="px-5 py-3.5 flex items-center justify-between gap-4 text-xs">
                        <div>
                            <span class="font-bold text-neutral-900">{{ $other->name }}</span>
                            <span class="text-neutral-400 mx-1.5">•</span>
                            <span class="text-neutral-600">{{ (str_ends_with((string) $other->email, '@enquiry.local') || str_ends_with((string) $other->email, '@menahem.local')) ? 'No email provided' : $other->email }}</span>
                            <span class="text-neutral-400 mx-1.5">•</span>
                            <span class="text-neutral-600">{{ $other->total_orders }} order(s)</span>
                        </div>
                        <a href="{{ route('admin.leads.show', $other) }}" class="font-bold text-neutral-900 underline">
                            View Profile &rarr;
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Connected Enquiries History -->
    <div class="bg-white rounded-none border border-neutral-200 shadow-none overflow-hidden">
        <div class="p-5 border-b border-neutral-200">
            <h3 class="text-base font-bold text-neutral-900">Customer Enquiries History ({{ $inquiries->count() }})</h3>
            <p class="text-xs text-neutral-500">Every website enquiry submitted by this customer</p>
        </div>

        <div class="divide-y divide-neutral-200">
            @forelse($inquiries as $inq)
                <div class="p-5 hover:bg-neutral-50 transition-colors flex flex-col sm:flex-row justify-between items-start gap-4">
                    <div class="space-y-1 min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-bold text-neutral-900">{{ $inq->resolvedSubject() }}</span>
                            <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-none border bg-neutral-100 text-neutral-800 border-neutral-300">
                                {{ $inq->statusLabel() }}
                            </span>
                        </div>
                        <p class="text-xs text-neutral-500">
                            Submitted on {{ $inq->created_at->format('d M Y, h:i A') }}
                            @if($inq->phone) • {{ $inq->phone }} @endif
                        </p>
                        @if($inq->message)
                            <p class="text-xs text-neutral-700 bg-neutral-50 border border-neutral-200 p-2.5 mt-2">{{ $inq->message }}</p>
                        @endif
                    </div>
                    <a href="{{ route('admin.inquiries.show', $inq) }}"
                       class="px-3 py-1.5 bg-neutral-900 hover:bg-black text-white text-xs font-bold uppercase tracking-wider shrink-0">
                        View / Reply &rarr;
                    </a>
                </div>
            @empty
                <div class="p-8 text-center text-neutral-400 text-sm">
                    No enquiries recorded for this customer yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
