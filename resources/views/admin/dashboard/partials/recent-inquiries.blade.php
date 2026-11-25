@forelse($recentInquiries as $inq)
    <div class="p-3.5 hover:bg-neutral-50 transition-colors flex items-start justify-between gap-4">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 mb-1">
                <span class="font-bold text-xs text-neutral-900">{{ $inq->name }}</span>
                @if($inq->isPending())
                    <span class="px-1.5 py-0.2 text-[9px] uppercase font-bold tracking-wider rounded-none bg-amber-100 text-amber-900 border border-amber-300">Pending</span>
                @elseif($inq->isReplied())
                    <span class="px-1.5 py-0.2 text-[9px] uppercase font-bold tracking-wider rounded-none bg-emerald-100 text-emerald-900 border border-emerald-300">Replied</span>
                @elseif($inq->isFailed())
                    <span class="px-1.5 py-0.2 text-[9px] uppercase font-bold tracking-wider rounded-none bg-rose-100 text-rose-900 border border-rose-300">Failed</span>
                @elseif($inq->isContacted())
                    <span class="px-1.5 py-0.2 text-[9px] uppercase font-bold tracking-wider rounded-none bg-blue-100 text-blue-900 border border-blue-300">Contacted</span>
                @else
                    <span class="px-1.5 py-0.2 text-[9px] uppercase font-bold tracking-wider rounded-none bg-neutral-100 text-neutral-800 border border-neutral-300">Closed</span>
                @endif
            </div>
            <p class="text-[11px] text-neutral-500 truncate">
                {{ $inq->phone ?: $inq->email ?: 'No contact' }}
                @if($inq->product_name) • <span class="font-medium text-neutral-800">{{ $inq->product_name }}</span>@endif
            </p>
            @if($inq->message)
                <p class="text-xs text-neutral-600 mt-1 line-clamp-1 italic">"{{ $inq->message }}"</p>
            @endif
        </div>
        <div class="text-right shrink-0">
            <span class="text-[10px] text-neutral-400 block">{{ $inq->created_at->diffForHumans() }}</span>
            <a href="{{ route('admin.inquiries.show', $inq) }}" class="inline-block mt-1 text-xs font-bold text-neutral-900 hover:underline">
                Details
            </a>
        </div>
    </div>
@empty
    <div class="p-6 text-center text-xs text-neutral-400">
        No inquiries recorded yet.
    </div>
@endforelse
