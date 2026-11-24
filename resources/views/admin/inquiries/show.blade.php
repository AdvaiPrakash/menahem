@extends('admin.layouts.app')

@section('title', 'Enquiry Details: ' . $inquiry->name)
@section('header_title', 'Enquiry & Email Reply')

@section('header_actions')
    <a href="{{ route('admin.inquiries.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-none border border-neutral-300 text-neutral-700 hover:bg-neutral-100 text-xs font-semibold uppercase tracking-wider transition-colors">
        &larr; Back to Enquiries
    </a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Original Enquiry, Conversation History & Direct Reply -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Customer Summary & Original Enquiry -->
        <div class="bg-white p-6 rounded-none border border-neutral-200 shadow-none space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-neutral-200 pb-4">
                <div>
                    <h2 class="text-xl font-bold text-neutral-900">{{ $inquiry->name }}</h2>
                    <p class="text-xs text-neutral-500 mt-0.5">
                        Submitted {{ $inquiry->created_at->format('F d, Y \a\t h:i A') }} ({{ $inquiry->created_at->diffForHumans() }})
                    </p>
                </div>
                <div>
                    @if($inquiry->isPending())
                        <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-none bg-amber-50 text-amber-800 border border-amber-200">Pending</span>
                    @elseif($inquiry->isReplied())
                        <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-none bg-emerald-50 text-emerald-800 border border-emerald-200">Replied</span>
                    @elseif($inquiry->isFailed())
                        <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-none bg-rose-50 text-rose-800 border border-rose-200">Failed</span>
                    @elseif($inquiry->isContacted())
                        <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-none bg-blue-50 text-blue-800 border border-blue-200">Contacted</span>
                    @else
                        <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-none bg-neutral-100 text-neutral-700 border border-neutral-200">Closed</span>
                    @endif
                </div>
            </div>

            <!-- Contact Channels -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-none bg-neutral-50 border border-neutral-200">
                    <span class="text-xs uppercase font-bold text-neutral-400 block mb-1">Email Address</span>
                    @if($inquiry->email)
                        <span class="text-sm font-bold text-neutral-900 block break-all">{{ $inquiry->email }}</span>
                        <span class="inline-block mt-1 text-[11px] text-neutral-500">Replies sent via {{ $senderEmail }}</span>
                    @else
                        <span class="text-xs text-neutral-400 italic">Not provided</span>
                    @endif
                </div>

                <div class="p-4 rounded-none bg-neutral-50 border border-neutral-200">
                    <span class="text-xs uppercase font-bold text-neutral-400 block mb-1">Phone Number</span>
                    @if($inquiry->phone)
                        <span class="text-sm font-bold text-neutral-900 block">{{ $inquiry->phone }}</span>
                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $inquiry->phone);
                        @endphp
                        <a href="https://wa.me/{{ $cleanPhone }}?text=Hello%20{{ urlencode($inquiry->name) }},%20this%20is%20Menahem%20Luxury%20Home%20Essentials%20regarding%20your%20inquiry."
                           target="_blank"
                           class="inline-flex items-center gap-1.5 mt-1.5 text-xs font-bold text-neutral-900 underline hover:text-black">
                            Chat on WhatsApp &rarr;
                        </a>
                    @else
                        <span class="text-xs text-neutral-400 italic">Not provided</span>
                    @endif
                </div>
            </div>

            <!-- Subject & Product Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <span class="text-xs uppercase font-bold text-neutral-400 block mb-1">Enquiry Subject</span>
                    <div class="p-3 bg-neutral-50 rounded-none border border-neutral-200 font-semibold text-neutral-900 text-xs">
                        {{ $inquiry->resolvedSubject() }}
                    </div>
                </div>
                <div>
                    <span class="text-xs uppercase font-bold text-neutral-400 block mb-1">Category / Product</span>
                    <div class="p-3 bg-neutral-50 rounded-none border border-neutral-200 font-semibold text-neutral-900 text-xs">
                        {{ $inquiry->product_name ?: ucwords(str_replace('_', ' ', $inquiry->type)) }}
                    </div>
                </div>
            </div>

            @if(!empty($inquiry->details))
                <div>
                    <span class="text-xs uppercase font-bold text-neutral-400 block mb-1">Custom Measurement / Specification Details</span>
                    <div class="p-4 bg-neutral-50 rounded-none border border-neutral-200 space-y-2 text-xs">
                        @foreach($inquiry->details as $key => $val)
                            <div class="flex items-center justify-between py-1 border-b border-neutral-200 last:border-0">
                                <span class="font-bold text-neutral-600 uppercase">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                <span class="font-semibold text-neutral-900">{{ is_array($val) ? json_encode($val) : $val }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Conversation History -->
        <div class="bg-white p-6 rounded-none border border-neutral-200 shadow-none space-y-5">
            <div class="flex items-center justify-between border-b border-neutral-200 pb-3">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-neutral-900">Conversation History</h3>
                    <p class="text-[11px] text-neutral-500 mt-0.5">Original customer enquiry and all email replies in chronological order.</p>
                </div>
                <span class="px-2.5 py-0.5 text-[11px] font-bold bg-neutral-100 border border-neutral-200 text-neutral-700">
                    {{ 1 + $inquiry->replies->count() }} {{ $inquiry->replies->count() === 0 ? 'Message' : 'Messages' }}
                </span>
            </div>

            <div class="space-y-4">
                <!-- Original Customer Message -->
                <div class="p-4 bg-neutral-50 border border-neutral-200 border-l-4 border-l-neutral-900 space-y-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-neutral-900 text-white">Customer</span>
                            <span class="font-bold text-neutral-900">{{ $inquiry->name }}</span>
                            @if($inquiry->email)
                                <span class="text-neutral-500">&lt;{{ $inquiry->email }}&gt;</span>
                            @endif
                        </div>
                        <span class="text-[11px] text-neutral-500">{{ $inquiry->created_at->format('M d, Y, h:i A') }}</span>
                    </div>
                    <p class="text-[11px] font-semibold text-neutral-600">
                        Subject: {{ $inquiry->resolvedSubject() }}
                    </p>
                    <div class="text-sm text-neutral-800 leading-relaxed whitespace-pre-wrap pt-1">{{ $inquiry->message ?: 'No written message provided.' }}</div>
                </div>

                <!-- Chronological Admin Replies -->
                @forelse($inquiry->replies as $reply)
                    <div class="p-4 border border-neutral-200 border-l-4 {{ $reply->isSent() ? 'bg-emerald-50/30 border-l-emerald-700' : 'bg-rose-50/40 border-l-rose-700' }} space-y-2">
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $reply->isSent() ? 'bg-emerald-700 text-white' : 'bg-rose-700 text-white' }}">
                                    Admin Reply
                                </span>
                                <span class="font-bold text-neutral-900">{{ $reply->sender_name }}</span>
                                <span class="text-neutral-500">({{ $reply->sender_email }} &rarr; {{ $reply->recipient_email }})</span>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($reply->isSent())
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-300">
                                        Sent
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-rose-100 text-rose-900 border border-rose-300">
                                        Failed
                                    </span>
                                @endif
                                <span class="text-[11px] text-neutral-500">
                                    {{ ($reply->sent_at ?? $reply->created_at)->format('M d, Y, h:i A') }}
                                </span>
                            </div>
                        </div>

                        <p class="text-[11px] font-semibold text-neutral-600">
                            Subject: {{ $reply->subject }}
                        </p>

                        <div class="text-sm text-neutral-800 leading-relaxed whitespace-pre-wrap pt-1">{{ $reply->message }}</div>

                        @if($reply->isFailed() && $reply->error_message)
                            <div class="mt-2 p-2.5 bg-rose-50 border border-rose-200 text-[11px] text-rose-800">
                                <strong>Delivery Error:</strong> {{ $reply->error_message }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-4 bg-neutral-50 border border-dashed border-neutral-300 text-xs text-neutral-500 text-center">
                        No email replies sent yet. Use the reply form below to respond directly to the customer.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Direct Reply Form -->
        <div class="bg-white p-6 rounded-none border border-neutral-200 shadow-none space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-neutral-200 pb-3">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-neutral-900">Send Email Reply</h3>
                    <p class="text-[11px] text-neutral-500 mt-0.5">
                        Sent directly via Hostinger SMTP from <strong class="text-neutral-800">{{ $senderEmail }}</strong>
                    </p>
                </div>
            </div>

            @if(!auth()->user()->canEditContent())
                <div class="p-4 bg-neutral-50 border border-neutral-200 text-xs text-neutral-500 italic">
                    Viewers have read-only access and cannot send email replies.
                </div>
            @elseif(empty($inquiry->email) || ! filter_var($inquiry->email, FILTER_VALIDATE_EMAIL))
                <div class="p-4 bg-amber-50 border border-amber-200 text-xs text-amber-900 font-semibold">
                    Cannot send email reply because this enquiry does not contain a valid customer email address.
                </div>
            @else
                <form action="{{ route('admin.inquiries.reply', $inquiry) }}"
                      method="POST"
                      x-data="{ submitting: false }"
                      @submit="if (submitting) { $event.preventDefault(); return; } submitting = true;"
                      class="space-y-4">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-neutral-50 border border-neutral-200 text-xs">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-400 block">From</span>
                            <span class="font-mono font-bold text-neutral-900">{{ $senderEmail }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-400 block">To</span>
                            <span class="font-mono font-bold text-neutral-900 break-all">{{ $inquiry->email }}</span>
                        </div>
                    </div>

                    <div>
                        <label for="reply-subject" class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                            Email Subject
                        </label>
                        <input id="reply-subject"
                               type="text"
                               name="subject"
                               value="{{ old('subject', $defaultReplySubject) }}"
                               required
                               class="w-full px-3.5 py-2.5 rounded-none border border-neutral-300 text-xs font-semibold text-neutral-900 focus:outline-none focus:border-neutral-900 bg-white">
                    </div>

                    <div>
                        <label for="reply-message" class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                            Reply Message <span class="text-rose-600">*</span>
                        </label>
                        <textarea id="reply-message"
                                  name="message"
                                  rows="6"
                                  required
                                  placeholder="Type your response to {{ $inquiry->name }}..."
                                  class="w-full px-3.5 py-2.5 rounded-none border border-neutral-300 text-sm text-neutral-900 focus:outline-none focus:border-neutral-900 bg-white">{{ old('message') }}</textarea>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[11px] text-neutral-500">
                            The reply will be emailed to the customer and saved to conversation history.
                        </span>
                        <button type="submit"
                                :disabled="submitting"
                                class="px-6 py-2.5 rounded-none bg-neutral-900 hover:bg-black text-white text-xs font-bold uppercase tracking-wider transition-colors disabled:opacity-60 cursor-pointer shrink-0">
                            <span x-show="!submitting">Send Reply</span>
                            <span x-show="submitting" x-cloak>Sending Reply...</span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Right Sidebar: Status Updater & Staff Notes -->
    <div class="space-y-6">
        <div class="bg-white p-6 rounded-none border border-neutral-200 shadow-none space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-neutral-500 border-b border-neutral-200 pb-3">Enquiry Management</h3>

            @if(auth()->user()->canEditContent())
            <form action="{{ route('admin.inquiries.status', $inquiry) }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Enquiry Status</label>
                    <select name="status" class="w-full px-3.5 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white">
                        <option value="pending" {{ $inquiry->isPending() ? 'selected' : '' }}>Pending (Awaiting Response)</option>
                        <option value="replied" {{ $inquiry->status === 'replied' ? 'selected' : '' }}>Replied</option>
                        <option value="failed" {{ $inquiry->status === 'failed' ? 'selected' : '' }}>Failed (Email Delivery Failed)</option>
                        <option value="closed" {{ $inquiry->status === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Internal Admin Notes</label>
                    <textarea name="admin_notes" rows="4"
                              class="w-full px-3.5 py-2.5 rounded-none border border-neutral-300 text-xs focus:outline-none focus:border-neutral-900 bg-white"
                              placeholder="Record internal notes on customer conversation, quotes shared, etc...">{{ old('admin_notes', $inquiry->admin_notes) }}</textarea>
                </div>

                <button type="submit"
                        class="w-full py-2.5 px-4 rounded-none bg-neutral-900 hover:bg-black text-white text-xs font-bold uppercase tracking-wider transition-colors shadow-none cursor-pointer">
                    Save Status &amp; Notes
                </button>
            </form>
            @else
            <div class="text-xs text-neutral-500">
                <p><span class="font-bold">Status:</span> {{ $inquiry->statusLabel() }}</p>
                @if($inquiry->admin_notes)
                    <p class="mt-2"><span class="font-bold">Notes:</span> {{ $inquiry->admin_notes }}</p>
                @endif
                <p class="mt-4 italic">Viewers have read-only access.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
