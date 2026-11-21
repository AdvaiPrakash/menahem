@extends('admin.layouts.app')

@section('title', 'Customer Leads & Inquiries')
@section('header_title', 'Customer Leads & Inquiries')

@section('content')
<!-- Filter Status Tabs & Search -->
<div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs mb-6 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <!-- Status Tabs -->
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl">
            <a href="{{ route('admin.inquiries.index') }}" 
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ empty($currentStatus) ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                All ({{ $statusCounts['all'] }})
            </a>
            <a href="{{ route('admin.inquiries.index', ['status' => 'new']) }}" 
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $currentStatus === 'new' ? 'bg-amber-500 text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                New ({{ $statusCounts['new'] }})
            </a>
            <a href="{{ route('admin.inquiries.index', ['status' => 'contacted']) }}" 
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $currentStatus === 'contacted' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Contacted ({{ $statusCounts['contacted'] }})
            </a>
            <a href="{{ route('admin.inquiries.index', ['status' => 'closed']) }}" 
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $currentStatus === 'closed' ? 'bg-slate-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Closed ({{ $statusCounts['closed'] }})
            </a>
        </div>

        <!-- Search Input -->
        <form method="GET" action="{{ route('admin.inquiries.index') }}" class="flex items-center gap-2">
            @if($currentStatus)
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search customer, phone..." 
                       class="pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#74826B]">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-800 text-white text-xs font-semibold">Search</button>
        </form>
    </div>
</div>

<!-- Responsive Content -->
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-bold tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3.5">Customer</th>
                    <th class="px-5 py-3.5">Type & Interest</th>
                    <th class="px-5 py-3.5">Message Snippet</th>
                    <th class="px-5 py-3.5">Status</th>
                    <th class="px-5 py-3.5">Date</th>
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($inquiries as $inq)
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="px-5 py-4">
                        <span class="font-bold text-slate-900 block">{{ $inq->name }}</span>
                        <div class="text-xs text-slate-500 flex items-center gap-2 mt-0.5">
                            @if($inq->phone)
                                <span>{{ $inq->phone }}</span>
                            @endif
                            @if($inq->email)
                                <span>• {{ $inq->email }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700">
                            {{ str_replace('_', ' ', $inq->type) }}
                        </span>
                        @if($inq->product_name)
                            <span class="block text-xs font-medium text-slate-900 mt-1 truncate max-w-xs">{{ $inq->product_name }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-xs text-slate-600 max-w-xs">
                        <p class="truncate">{{ $inq->message ?: 'No written message' }}</p>
                    </td>
                    <td class="px-5 py-4">
                        @if($inq->isNew())
                            <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-full bg-amber-100 text-amber-900 border border-amber-200">New</span>
                        @elseif($inq->isContacted())
                            <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-full bg-blue-100 text-blue-900 border border-blue-200">Contacted</span>
                        @else
                            <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-full bg-slate-100 text-slate-700 border border-slate-200">Closed</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-xs text-slate-500">
                        {{ $inq->created_at->format('M d, Y') }}
                        <span class="block text-[11px] text-slate-400">{{ $inq->created_at->format('H:i') }}</span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.inquiries.show', $inq) }}" 
                               class="px-3 py-1 rounded-md bg-[#74826B] hover:bg-[#636F5A] text-white text-xs font-semibold transition-colors">
                                Review
                            </a>
                            @if(auth()->user()->canEditContent())
                            <form action="{{ route('admin.inquiries.destroy', $inq) }}" method="POST" onsubmit="return confirm('Delete this inquiry?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1 rounded-md bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-600 text-xs font-semibold transition-colors">
                                    Delete
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                        No customer leads found in this view.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Stacked Card View -->
    <div class="block md:hidden divide-y divide-slate-100">
        @forelse($inquiries as $inq)
        <div class="p-4 space-y-2.5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">{{ $inq->name }}</h3>
                    <p class="text-xs text-slate-500">{{ $inq->phone ?: $inq->email ?: 'No contact details' }}</p>
                </div>
                @if($inq->isNew())
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800">New</span>
                @elseif($inq->isContacted())
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-800">Contacted</span>
                @else
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-700">Closed</span>
                @endif
            </div>

            @if($inq->product_name)
                <p class="text-xs font-semibold text-slate-800">Item: {{ $inq->product_name }}</p>
            @endif

            @if($inq->message)
                <p class="text-xs text-slate-600 line-clamp-2 italic bg-slate-50 p-2 rounded-lg">"{{ $inq->message }}"</p>
            @endif

            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                <span class="text-xs text-slate-400">{{ $inq->created_at->diffForHumans() }}</span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.inquiries.show', $inq) }}" class="px-3 py-1 rounded-lg bg-[#74826B] text-white text-xs font-semibold">
                        View Lead
                    </a>
                    @if(auth()->user()->canEditContent())
                    <form action="{{ route('admin.inquiries.destroy', $inq) }}" method="POST" onsubmit="return confirm('Delete this inquiry?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-600 text-xs font-semibold">
                            Delete
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-sm text-slate-400">
            No customer inquiries found.
        </div>
        @endforelse
    </div>

    @if($inquiries->hasPages())
    <div class="p-4 border-t border-slate-200/80 bg-slate-50">
        {{ $inquiries->links() }}
    </div>
    @endif
</div>
@endsection
