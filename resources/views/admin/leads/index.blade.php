@extends('admin.layouts.app')

@section('title', 'Customer Leads & CRM')
@section('header_title', 'Customer Leads Management')

@section('header_actions')
    <a href="{{ route('admin.leads.export', request()->query()) }}" 
       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-none bg-neutral-900 hover:bg-neutral-800 text-white text-xs font-bold uppercase tracking-wider transition-all border border-neutral-900">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <span>Export to CSV</span>
    </a>
@endsection

@section('content')
<!-- KPI Metric Cards -->
<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-5">
    <div class="bg-white rounded-none p-4 border border-neutral-200">
        <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Total Leads</p>
        <h3 class="text-xl font-black text-neutral-900 mt-1">{{ $stats['total'] }}</h3>
    </div>

    <div class="bg-white rounded-none p-4 border border-neutral-200">
        <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Guest Checkout</p>
        <h3 class="text-xl font-black text-amber-700 mt-1">{{ $stats['guest'] }}</h3>
    </div>

    <div class="bg-white rounded-none p-4 border border-neutral-200">
        <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Registered Users</p>
        <h3 class="text-xl font-black text-blue-700 mt-1">{{ $stats['registered'] }}</h3>
    </div>

    <div class="bg-white rounded-none p-4 border border-neutral-200">
        <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Google OAuth</p>
        <h3 class="text-xl font-black text-purple-700 mt-1">{{ $stats['google'] }}</h3>
    </div>

    <div class="bg-white rounded-none p-4 border border-neutral-200 col-span-2 sm:col-span-1">
        <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Total Revenue</p>
        <h3 class="text-xl font-black text-emerald-700 mt-1">₹{{ number_format($stats['total_revenue']) }}</h3>
    </div>
</div>

<!-- Filters Bar -->
<div class="bg-white rounded-none border border-neutral-200 p-4 mb-5">
    <form action="{{ route('admin.leads.index') }}" method="GET" class="flex flex-col lg:flex-row gap-2.5">
        <div class="flex-1 relative">
            <input type="text" 
                   name="search" 
                   value="{{ $search }}" 
                   placeholder="Search by customer name, email, mobile number, or city..." 
                   class="w-full pl-9 pr-4 py-2 text-xs rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900 bg-white">
            <svg class="w-4 h-4 text-neutral-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <div class="sm:w-44">
            <select name="customer_type" class="w-full px-3 py-2 text-xs rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 bg-white">
                <option value="">All Customer Types</option>
                <option value="guest" {{ $typeFilter === 'guest' ? 'selected' : '' }}>Guest Customers</option>
                <option value="registered" {{ $typeFilter === 'registered' ? 'selected' : '' }}>Registered Users</option>
                <option value="google" {{ $typeFilter === 'google' ? 'selected' : '' }}>Google Sign-In</option>
            </select>
        </div>

        <div class="flex gap-2">
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="px-2.5 py-1.5 text-xs rounded-none border border-neutral-300 text-neutral-800 bg-white">
            <input type="date" name="date_to" value="{{ $dateTo }}" class="px-2.5 py-1.5 text-xs rounded-none border border-neutral-300 text-neutral-800 bg-white">
            <button type="submit" class="px-4 py-2 bg-neutral-900 hover:bg-neutral-800 text-white text-xs font-bold rounded-none uppercase tracking-wider transition-colors border border-neutral-900 cursor-pointer">
                Search
            </button>
            @if($search || $typeFilter || $dateFrom || $dateTo)
                <a href="{{ route('admin.leads.index') }}" class="px-3 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-xs font-bold rounded-none flex items-center justify-center border border-neutral-300">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Customer Leads Table -->
<div class="bg-white rounded-none border border-neutral-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-neutral-50 text-[10px] uppercase tracking-wider text-neutral-600 font-bold border-b border-neutral-200">
                <tr>
                    <th class="py-3 px-4 sm:px-6">Customer ID & Name</th>
                    <th class="py-3 px-4">Type</th>
                    <th class="py-3 px-4">Contact Details</th>
                    <th class="py-3 px-4">Delivery City / State</th>
                    <th class="py-3 px-4 text-center">Total Orders</th>
                    <th class="py-3 px-4">Lifetime Spend</th>
                    <th class="py-3 px-4">Last Activity</th>
                    <th class="py-3 px-4 sm:px-6 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200">
                @forelse($leads as $lead)
                    <tr class="hover:bg-neutral-50 transition-colors">
                        <td class="py-3 px-4 sm:px-6">
                            <span class="font-bold text-neutral-900 block text-xs">{{ $lead->name }}</span>
                            <span class="font-mono text-[10px] text-neutral-400">#CUS-{{ str_pad($lead->id, 4, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="py-3 px-4">
                            @if($lead->customer_type === 'google')
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded-none font-bold text-[9px] uppercase bg-purple-100 text-purple-900 border border-purple-300">
                                    Google
                                </span>
                            @elseif($lead->customer_type === 'registered')
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded-none font-bold text-[9px] uppercase bg-blue-100 text-blue-900 border border-blue-300">
                                    Registered
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded-none font-bold text-[9px] uppercase bg-amber-100 text-amber-900 border border-amber-300">
                                    Guest
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <p class="font-semibold text-neutral-800">{{ $lead->phone ?: '—' }}</p>
                            <p class="text-[11px] text-neutral-500">{{ $lead->email }}</p>
                        </td>
                        <td class="py-3 px-4 text-neutral-700">
                            {{ $lead->city ? $lead->city . ', ' . $lead->state : ($lead->delivery_address ? Str::limit($lead->delivery_address, 25) : '—') }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-neutral-900">
                            {{ $lead->total_orders }}
                        </td>
                        <td class="py-3 px-4 font-black text-neutral-900">
                            ₹{{ number_format($lead->total_purchase_amount, 2) }}
                        </td>
                        <td class="py-3 px-4 text-neutral-500 whitespace-nowrap text-[11px]">
                            {{ $lead->last_purchase_date ? $lead->last_purchase_date->diffForHumans() : ($lead->registration_date ? $lead->registration_date->diffForHumans() : $lead->created_at->diffForHumans()) }}
                        </td>
                        <td class="py-3 px-4 sm:px-6 text-right">
                            <a href="{{ route('admin.leads.show', $lead) }}" 
                               class="px-2.5 py-1 text-[11px] font-bold rounded-none bg-neutral-900 text-white hover:bg-neutral-800 transition-colors uppercase tracking-wider border border-neutral-900">
                                View Profile
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-neutral-400">
                            No customer leads recorded yet. Every customer purchase automatically records here.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($leads->hasPages())
        <div class="p-3 border-t border-neutral-200 bg-neutral-50">
            {{ $leads->links() }}
        </div>
    @endif
</div>
@endsection
