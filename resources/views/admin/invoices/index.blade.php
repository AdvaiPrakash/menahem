@extends('admin.layouts.app')

@section('title', 'Invoice Management')
@section('header_title', 'Invoice Management')

@section('content')
<!-- Metric Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
    <div class="bg-white rounded-none p-4 border border-neutral-200 flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Total Invoices</p>
            <h3 class="text-xl font-black text-neutral-900 mt-0.5">{{ $stats['total_invoices'] }}</h3>
        </div>
        <div class="w-9 h-9 rounded-none bg-neutral-100 text-neutral-800 border border-neutral-200 flex items-center justify-center font-bold text-sm">
            📄
        </div>
    </div>

    <div class="bg-white rounded-none p-4 border border-neutral-200 flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Total Paid Revenue</p>
            <h3 class="text-xl font-black text-emerald-700 mt-0.5">₹{{ number_format($stats['total_revenue']) }}</h3>
        </div>
        <div class="w-9 h-9 rounded-none bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center font-bold text-sm">
            ₹
        </div>
    </div>

    <div class="bg-white rounded-none p-4 border border-neutral-200 flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Total GST Collected</p>
            <h3 class="text-xl font-black text-neutral-900 mt-0.5">₹{{ number_format($stats['total_gst_collected']) }}</h3>
        </div>
        <div class="w-9 h-9 rounded-none bg-blue-50 text-blue-800 border border-blue-200 flex items-center justify-center font-bold text-sm">
            🏛️
        </div>
    </div>
</div>

<!-- Search & Filters -->
<div class="bg-white rounded-none border border-neutral-200 p-4 mb-5">
    <form action="{{ route('admin.invoices.index') }}" method="GET" class="flex flex-col lg:flex-row gap-2.5">
        <div class="flex-1 relative">
            <input type="text" 
                   name="search" 
                   value="{{ $search }}" 
                   placeholder="Search by invoice number, order ID, customer name, phone, or email..." 
                   class="w-full pl-9 pr-4 py-2 text-xs rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900 bg-white">
            <svg class="w-4 h-4 text-neutral-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <div class="sm:w-44">
            <select name="payment_status" class="w-full px-3 py-2 text-xs rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 bg-white">
                <option value="">All Payment Statuses</option>
                <option value="paid" {{ $statusFilter === 'paid' ? 'selected' : '' }}>Paid Only</option>
                <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ $statusFilter === 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
        </div>

        <div class="flex gap-2">
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="px-2.5 py-1.5 text-xs rounded-none border border-neutral-300 text-neutral-800 bg-white">
            <input type="date" name="date_to" value="{{ $dateTo }}" class="px-2.5 py-1.5 text-xs rounded-none border border-neutral-300 text-neutral-800 bg-white">
            <button type="submit" class="px-4 py-2 bg-neutral-900 hover:bg-neutral-800 text-white text-xs font-bold rounded-none uppercase tracking-wider transition-colors border border-neutral-900 cursor-pointer">
                Search
            </button>
            @if($search || $statusFilter || $dateFrom || $dateTo)
                <a href="{{ route('admin.invoices.index') }}" class="px-3 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-xs font-bold rounded-none flex items-center justify-center border border-neutral-300">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Invoices Table -->
<div class="bg-white rounded-none border border-neutral-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-neutral-50 text-[10px] uppercase tracking-wider text-neutral-600 font-bold border-b border-neutral-200">
                <tr>
                    <th class="py-3 px-4 sm:px-6">Invoice #</th>
                    <th class="py-3 px-4">Order ID</th>
                    <th class="py-3 px-4">Customer</th>
                    <th class="py-3 px-4">Date</th>
                    <th class="py-3 px-4">Taxable</th>
                    <th class="py-3 px-4">GST</th>
                    <th class="py-3 px-4">Final Amount</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 sm:px-6 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200">
                @forelse($invoices as $inv)
                    @php $gstTotal = $inv->cgst_amount + $inv->sgst_amount + $inv->igst_amount; @endphp
                    <tr class="hover:bg-neutral-50 transition-colors">
                        <td class="py-3 px-4 sm:px-6 font-mono font-bold text-neutral-900 text-xs">
                            <a href="{{ route('admin.invoices.show', $inv) }}" class="text-neutral-900 hover:underline">
                                {{ $inv->invoice_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4 font-mono text-xs text-neutral-600">
                            {{ $inv->order ? $inv->order->order_number : 'N/A' }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-neutral-900 block text-xs">{{ $inv->customer_name }}</span>
                            <span class="text-[10px] text-neutral-500">{{ $inv->customer_phone ?: $inv->customer_email }}</span>
                        </td>
                        <td class="py-3 px-4 text-xs text-neutral-600">
                            {{ $inv->invoice_date ? $inv->invoice_date->format('d M Y') : $inv->created_at->format('d M Y') }}
                        </td>
                        <td class="py-3 px-4 text-xs font-medium text-neutral-800">
                            ₹{{ number_format($inv->taxable_amount, 2) }}
                        </td>
                        <td class="py-3 px-4 text-xs font-semibold text-neutral-700">
                            ₹{{ number_format($gstTotal, 2) }}
                        </td>
                        <td class="py-3 px-4 text-xs font-black text-neutral-900">
                            ₹{{ number_format($inv->grand_total, 2) }}
                        </td>
                        <td class="py-3 px-4">
                            @if($inv->payment_status === 'paid')
                                <span class="px-1.5 py-0.2 rounded-none text-[9px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-300">
                                    Paid
                                </span>
                            @else
                                <span class="px-1.5 py-0.2 rounded-none text-[9px] font-bold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                                    {{ ucfirst($inv->payment_status) }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 sm:px-6 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.invoices.show', $inv) }}" 
                                   class="px-2 py-0.5 text-[11px] font-bold rounded-none bg-neutral-100 text-neutral-800 hover:bg-neutral-200 border border-neutral-300 transition-colors">
                                    View
                                </a>
                                <a href="{{ route('admin.invoices.download', $inv) }}" 
                                   class="px-2 py-0.5 text-[11px] font-bold rounded-none bg-neutral-900 text-white hover:bg-neutral-800 transition-colors flex items-center gap-1 border border-neutral-900">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>PDF</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-neutral-400">
                            No invoices generated yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->hasPages())
        <div class="p-3 border-t border-neutral-200 bg-neutral-50">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
