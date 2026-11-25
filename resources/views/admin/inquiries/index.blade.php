@extends('admin.layouts.app')

@section('title', 'Customer Enquiries')
@section('header_title', 'Customer Enquiries')

@section('content')
<!-- Filter Status Tabs & Search -->
<div class="bg-white p-4 rounded-none border border-neutral-200 shadow-none mb-6 space-y-4" data-live-context="inquiries">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <!-- Status Tabs -->
        <div class="flex flex-wrap items-center gap-1.5 p-1 bg-neutral-100 rounded-none border border-neutral-200">
            <a href="{{ route('admin.inquiries.index') }}"
               id="live-inquiries-tab-all"
               class="px-3.5 py-1.5 rounded-none text-xs font-bold transition-all {{ empty($currentStatus) ? 'bg-neutral-900 text-white shadow-none' : 'text-neutral-600 hover:text-neutral-900' }}">
                All ({{ $statusCounts['all'] }})
            </a>
            <a href="{{ route('admin.inquiries.index', ['status' => 'pending']) }}"
               id="live-inquiries-tab-pending"
               class="px-3.5 py-1.5 rounded-none text-xs font-bold transition-all {{ in_array($currentStatus, ['pending', 'new'], true) ? 'bg-neutral-900 text-white shadow-none' : 'text-neutral-600 hover:text-neutral-900' }}">
                Pending ({{ $statusCounts['pending'] }})
            </a>
            <a href="{{ route('admin.inquiries.index', ['status' => 'replied']) }}"
               id="live-inquiries-tab-replied"
               class="px-3.5 py-1.5 rounded-none text-xs font-bold transition-all {{ $currentStatus === 'replied' ? 'bg-neutral-900 text-white shadow-none' : 'text-neutral-600 hover:text-neutral-900' }}">
                Replied ({{ $statusCounts['replied'] }})
            </a>
            <a href="{{ route('admin.inquiries.index', ['status' => 'failed']) }}"
               id="live-inquiries-tab-failed"
               class="px-3.5 py-1.5 rounded-none text-xs font-bold transition-all {{ $currentStatus === 'failed' ? 'bg-neutral-900 text-white shadow-none' : 'text-neutral-600 hover:text-neutral-900' }}">
                Failed ({{ $statusCounts['failed'] }})
            </a>
            @if(($statusCounts['closed'] ?? 0) > 0 || $currentStatus === 'closed')
                <a href="{{ route('admin.inquiries.index', ['status' => 'closed']) }}"
                   id="live-inquiries-tab-closed"
                   class="px-3.5 py-1.5 rounded-none text-xs font-bold transition-all {{ $currentStatus === 'closed' ? 'bg-neutral-900 text-white shadow-none' : 'text-neutral-600 hover:text-neutral-900' }}">
                    Closed ({{ $statusCounts['closed'] }})
                </a>
            @endif
        </div>

        <!-- Search Input -->
        <form method="GET" action="{{ route('admin.inquiries.index') }}" class="flex items-center gap-2">
            @if($currentStatus)
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search customer, email, subject..."
                       class="pl-8 pr-3 py-1.5 text-xs rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 bg-white">
                <svg class="w-3.5 h-3.5 text-neutral-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="px-3 py-1.5 rounded-none bg-neutral-900 hover:bg-black text-white text-xs font-semibold cursor-pointer">Search</button>
        </form>
    </div>
</div>

<!-- Responsive Content -->
<div class="bg-white rounded-none border border-neutral-200 shadow-none overflow-hidden">
    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-sm text-neutral-600">
            <thead class="bg-neutral-50 border-b border-neutral-200 text-[11px] uppercase font-bold tracking-wider text-neutral-500">
                <tr>
                    <th class="px-5 py-3.5">Customer</th>
                    <th class="px-5 py-3.5">Subject &amp; Type</th>
                    <th class="px-5 py-3.5">Enquiry Message</th>
                    <th class="px-5 py-3.5">Status</th>
                    <th class="px-5 py-3.5">Date</th>
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="live-inquiries-desktop-tbody" class="divide-y divide-neutral-200">
                @include('admin.inquiries.partials.desktop-rows', ['inquiries' => $inquiries])
            </tbody>
        </table>
    </div>

    <!-- Mobile Stacked Card View -->
    <div id="live-inquiries-mobile-cards" class="block md:hidden divide-y divide-neutral-200">
        @include('admin.inquiries.partials.mobile-cards', ['inquiries' => $inquiries])
    </div>

    @if($inquiries->hasPages())
    <div class="p-4 border-t border-neutral-200 bg-neutral-50">
        {{ $inquiries->links() }}
    </div>
    @endif
</div>
@endsection
