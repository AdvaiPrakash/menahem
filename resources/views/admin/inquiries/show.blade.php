@extends('admin.layouts.app')

@section('title', 'Inquiry Details: ' . $inquiry->name)
@section('header_title', 'Inquiry Details')

@section('header_actions')
    <a href="{{ route('admin.inquiries.index') }}" 
       class="inline-flex items-center gap-2 px-3 py-2 rounded-none border border-neutral-300 text-neutral-700 hover:bg-neutral-100 text-xs font-semibold uppercase tracking-wider transition-colors">
        ← Back to Inquiries
    </a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Customer Details & Message -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white p-6 rounded-none border border-neutral-200 shadow-none space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-neutral-200 pb-4">
                <div>
                    <h2 class="text-xl font-bold text-neutral-900">{{ $inquiry->name }}</h2>
                    <p class="text-xs text-neutral-400 mt-0.5">Submitted {{ $inquiry->created_at->format('F d, Y \a\t h:i A') }} ({{ $inquiry->created_at->diffForHumans() }})</p>
                </div>
                <div>
                    @if($inquiry->isNew())
                        <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-none bg-amber-50 text-amber-800 border border-amber-200">New Lead</span>
                    @elseif($inquiry->isContacted())
                        <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-none bg-blue-50 text-blue-800 border border-blue-200">Contacted</span>
                    @else
                        <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-none bg-neutral-100 text-neutral-700 border border-neutral-200">Resolved / Closed</span>
                    @endif
                </div>
            </div>

            <!-- Contact Channels -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-none bg-neutral-50 border border-neutral-200">
                    <span class="text-xs uppercase font-bold text-neutral-400 block mb-1">Phone Number</span>
                    @if($inquiry->phone)
                        <span class="text-base font-bold text-neutral-900 block">{{ $inquiry->phone }}</span>
                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $inquiry->phone);
                        @endphp
                        <a href="https://wa.me/{{ $cleanPhone }}?text=Hello%20{{ urlencode($inquiry->name) }},%20this%20is%20Menahem%20Luxury%20Home%20Essentials%20regarding%20your%20inquiry." 
                           target="_blank"
                           class="inline-flex items-center gap-1.5 mt-2 text-xs font-bold text-neutral-900 underline hover:text-black">
                            Chat on WhatsApp →
                        </a>
                    @else
                        <span class="text-xs text-neutral-400 italic">Not provided</span>
                    @endif
                </div>

                <div class="p-4 rounded-none bg-neutral-50 border border-neutral-200">
                    <span class="text-xs uppercase font-bold text-neutral-400 block mb-1">Email Address</span>
                    @if($inquiry->email)
                        <span class="text-base font-bold text-neutral-900 block truncate">{{ $inquiry->email }}</span>
                        <a href="mailto:{{ $inquiry->email }}?subject=Regarding%20your%20inquiry%20with%20Menahem" 
                           class="inline-flex items-center gap-1.5 mt-2 text-xs font-bold text-neutral-900 underline hover:text-black">
                            Send Email →
                        </a>
                    @else
                        <span class="text-xs text-neutral-400 italic">Not provided</span>
                    @endif
                </div>
            </div>

            <!-- Item & Message Body -->
            <div class="space-y-4">
                @if($inquiry->product_name)
                    <div>
                        <span class="text-xs uppercase font-bold text-neutral-400 block mb-1">Product Interested In</span>
                        <div class="p-3 bg-neutral-100 rounded-none border border-neutral-200 font-semibold text-neutral-900 text-sm">
                            {{ $inquiry->product_name }}
                        </div>
                    </div>
                @endif

                <div>
                    <span class="text-xs uppercase font-bold text-neutral-400 block mb-1">Customer Message</span>
                    <div class="p-4 bg-neutral-50 rounded-none border border-neutral-200 text-sm text-neutral-800 leading-relaxed whitespace-pre-wrap">
                        {{ $inquiry->message ?: 'No written message provided.' }}
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
        </div>
    </div>

    <!-- Status Updater & Staff Notes -->
    <div class="space-y-6">
        <div class="bg-white p-6 rounded-none border border-neutral-200 shadow-none space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-neutral-500 border-b border-neutral-200 pb-3">Lead Management</h3>

            @if(auth()->user()->canEditContent())
            <form action="{{ route('admin.inquiries.status', $inquiry) }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Update Status</label>
                    <select name="status" class="w-full px-3.5 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white">
                        <option value="new" {{ $inquiry->status === 'new' ? 'selected' : '' }}>New Lead (Uncontacted)</option>
                        <option value="contacted" {{ $inquiry->status === 'contacted' ? 'selected' : '' }}>Contacted / In Progress</option>
                        <option value="closed" {{ $inquiry->status === 'closed' ? 'selected' : '' }}>Resolved / Closed</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Internal Admin Notes</label>
                    <textarea name="admin_notes" rows="4" 
                              class="w-full px-3.5 py-2.5 rounded-none border border-neutral-300 text-xs focus:outline-none focus:border-neutral-900 bg-white"
                              placeholder="Record notes on customer conversation, quotes shared, etc...">{{ old('admin_notes', $inquiry->admin_notes) }}</textarea>
                </div>

                <button type="submit" 
                        class="w-full py-2.5 px-4 rounded-none bg-neutral-900 hover:bg-black text-white text-xs font-bold uppercase tracking-wider transition-colors shadow-none">
                    Save Lead Status
                </button>
            </form>
            @else
            <div class="text-xs text-neutral-500">
                <p><span class="font-bold">Status:</span> {{ ucfirst($inquiry->status) }}</p>
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
