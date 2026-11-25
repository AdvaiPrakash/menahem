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
