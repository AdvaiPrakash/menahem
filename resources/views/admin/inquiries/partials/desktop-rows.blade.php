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
