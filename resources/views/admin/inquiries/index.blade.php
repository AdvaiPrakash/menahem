@extends('admin.layouts.app')

@section('title', 'Customer Enquiries')
@section('header_title', 'Customer Enquiries')

@section('content')
<!-- Filter Status Tabs & Search -->
<div class="bg-white p-4 rounded-none border border-neutral-200 shadow-none mb-6 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <!-- Status Tabs -->
        <div class="flex flex-wrap items-center gap-1.5 p-1 bg-neutral-100 rounded-none border border-neutral-200">
            <a href="{{ route('admin.inquiries.index') }}"
               class="px-3.5 py-1.5 rounded-none text-xs font-bold transition-all {{ empty($currentStatus) ? 'bg-neutral-900 text-white shadow-none' : 'text-neutral-600 hover:text-neutral-900' }}">
                All ({{ $statusCounts['all'] }})
            </a>
            <a href="{{ route('admin.inquiries.index', ['status' => 'pending']) }}"
               class="px-3.5 py-1.5 rounded-none text-xs font-bold transition-all {{ in_array($currentStatus, ['pending', 'new'], true) ? 'bg-neutral-900 text-white shadow-none' : 'text-neutral-600 hover:text-neutral-900' }}">
                Pending ({{ $statusCounts['pending'] }})
            </a>
            <a href="{{ route('admin.inquiries.index', ['status' => 'replied']) }}"
               class="px-3.5 py-1.5 rounded-none text-xs font-bold transition-all {{ $currentStatus === 'replied' ? 'bg-neutral-900 text-white shadow-none' : 'text-neutral-600 hover:text-neutral-900' }}">
                Replied ({{ $statusCounts['replied'] }})
            </a>
            <a href="{{ route('admin.inquiries.index', ['status' => 'failed']) }}"
               class="px-3.5 py-1.5 rounded-none text-xs font-bold transition-all {{ $currentStatus === 'failed' ? 'bg-neutral-900 text-white shadow-none' : 'text-neutral-600 hover:text-neutral-900' }}">
                Failed ({{ $statusCounts['failed'] }})
            </a>
            @if(($statusCounts['closed'] ?? 0) > 0 || $currentStatus === 'closed')
                <a href="{{ route('admin.inquiries.index', ['status' => 'closed']) }}"
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
            <tbody class="divide-y divide-neutral-200">
                @forelse($inquiries as $inq)
                <tr class="hover:bg-neutral-50 transition-colors">
                    <td class="px-5 py-4">
                        <span class="font-bold text-neutral-900 block">{{ $inq->name }}</span>
                        <div class="text-xs text-neutral-500 space-y-0.5 mt-0.5">
                            @if($inq->email)
                                <span class="block">{{ $inq->email }}</span>
                            @endif
                            @if($inq->phone)
                                <span class="block text-neutral-400">{{ $inq->phone }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <span class="block text-xs font-bold text-neutral-900 max-w-xs truncate">{{ $inq->resolvedSubject() }}</span>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded-none text-[10px] font-bold uppercase tracking-wider bg-neutral-100 text-neutral-700 border border-neutral-200">
                            {{ str_replace('_', ' ', $inq->type) }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-xs text-neutral-600 max-w-xs">
                        <p class="truncate">{{ $inq->message ?: 'No written message' }}</p>
                        @if(($inq->sent_replies_count ?? 0) > 0 || ($inq->failed_replies_count ?? 0) > 0)
                            <div class="mt-1 flex items-center gap-2 text-[11px]">
                                @if(($inq->sent_replies_count ?? 0) > 0)
                                    <span class="font-semibold text-emerald-700">{{ $inq->sent_replies_count }} {{ $inq->sent_replies_count === 1 ? 'reply' : 'replies' }} accepted</span>
                                @endif
                                @if(($inq->failed_replies_count ?? 0) > 0)
                                    <span class="font-semibold text-rose-700">{{ $inq->failed_replies_count }} failed</span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        @if($inq->isPending())
                            <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-none bg-amber-50 text-amber-800 border border-amber-200">Pending</span>
                        @elseif($inq->isReplied())
                            <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-none bg-emerald-50 text-emerald-800 border border-emerald-200">Replied</span>
                        @elseif($inq->isFailed())
                            <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-none bg-rose-50 text-rose-800 border border-rose-200">Failed</span>
                        @elseif($inq->isContacted())
                            <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-none bg-blue-50 text-blue-800 border border-blue-200">Contacted</span>
                        @else
                            <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-none bg-neutral-100 text-neutral-700 border border-neutral-200">Closed</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-xs text-neutral-500">
                        {{ $inq->created_at->format('M d, Y') }}
                        <span class="block text-[11px] text-neutral-400">{{ $inq->created_at->format('h:i A') }}</span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.inquiries.show', $inq) }}"
                               class="px-3 py-1 rounded-none bg-neutral-900 hover:bg-black text-white text-xs font-semibold transition-colors">
                                View / Reply
                            </a>
                            @if(auth()->user()->canEditContent())
                            <form action="{{ route('admin.inquiries.destroy', $inq) }}" method="POST" onsubmit="return confirm('Delete this inquiry?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1 rounded-none bg-rose-50 hover:bg-rose-700 hover:text-white text-rose-700 text-xs font-semibold transition-colors border border-rose-200 cursor-pointer">
                                    Delete
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-neutral-400">
                        No customer enquiries found in this view.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Stacked Card View -->
    <div class="block md:hidden divide-y divide-neutral-200">
        @forelse($inquiries as $inq)
        <div class="p-4 space-y-2.5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="font-bold text-neutral-900 text-sm">{{ $inq->name }}</h3>
                    <p class="text-xs text-neutral-500">{{ $inq->email ?: $inq->phone ?: 'No contact details' }}</p>
                    @if($inq->phone && $inq->email)
                        <p class="text-[11px] text-neutral-400">{{ $inq->phone }}</p>
                    @endif
                </div>
                @if($inq->isPending())
                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-none bg-amber-50 text-amber-800 border border-amber-200">Pending</span>
                @elseif($inq->isReplied())
                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-none bg-emerald-50 text-emerald-800 border border-emerald-200">Replied</span>
                @elseif($inq->isFailed())
                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-none bg-rose-50 text-rose-800 border border-rose-200">Failed</span>
                @elseif($inq->isContacted())
                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-none bg-blue-50 text-blue-800 border border-blue-200">Contacted</span>
                @else
                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-none bg-neutral-100 text-neutral-700 border border-neutral-200">Closed</span>
                @endif
            </div>

            <p class="text-xs font-semibold text-neutral-800">{{ $inq->resolvedSubject() }}</p>

            @if($inq->message)
                <p class="text-xs text-neutral-600 line-clamp-2 italic bg-neutral-50 p-2 rounded-none border border-neutral-200">"{{ $inq->message }}"</p>
            @endif

            <div class="flex items-center justify-between pt-2 border-t border-neutral-200">
                <span class="text-xs text-neutral-400">{{ $inq->created_at->format('M d, Y h:i A') }}</span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.inquiries.show', $inq) }}" class="px-3 py-1 rounded-none bg-neutral-900 text-white text-xs font-semibold">
                        View / Reply
                    </a>
                    @if(auth()->user()->canEditContent())
                    <form action="{{ route('admin.inquiries.destroy', $inq) }}" method="POST" onsubmit="return confirm('Delete this inquiry?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-2.5 py-1 rounded-none bg-rose-50 text-rose-700 text-xs font-semibold border border-rose-200">
                            Delete
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-sm text-neutral-400">
            No customer enquiries found.
        </div>
        @endforelse
    </div>

    @if($inquiries->hasPages())
    <div class="p-4 border-t border-neutral-200 bg-neutral-50">
        {{ $inquiries->links() }}
    </div>
    @endif
</div>
@endsection
