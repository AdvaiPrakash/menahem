<div class="relative flex items-center gap-2" id="admin-notification-controls">
    <!-- Voice Alert Toggle Button -->
    <button type="button"
            id="admin-voice-toggle-btn"
            title="Toggle voice notifications for enquiries and orders"
            class="inline-flex items-center gap-1.5 px-2.5 py-2 border border-neutral-300 bg-white hover:bg-neutral-50 text-neutral-800 text-[11px] font-bold uppercase tracking-wider transition-colors cursor-pointer">
        <span id="admin-voice-status-dot" class="w-2 h-2 bg-emerald-600 shrink-0"></span>
        <svg id="admin-voice-icon-on" class="w-3.5 h-3.5 text-neutral-800 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
        </svg>
        <svg id="admin-voice-icon-off" class="w-3.5 h-3.5 text-neutral-400 shrink-0 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
        </svg>
        <span id="admin-voice-toggle-label" class="hidden sm:inline">Voice On</span>
    </button>

    <!-- Notification Bell & Dropdown -->
    <div class="relative" id="admin-notification-bell-wrapper">
        <button type="button"
                id="admin-notification-bell-btn"
                aria-expanded="false"
                aria-haspopup="true"
                title="View enquiry & order notifications"
                class="relative inline-flex items-center justify-center w-9 h-9 border border-neutral-300 bg-white hover:bg-neutral-50 text-neutral-800 transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span id="admin-notification-unread-badge"
                  class="hidden absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 bg-rose-600 text-white text-[10px] font-bold flex items-center justify-center leading-none">
                0
            </span>
        </button>

        <!-- Dropdown Panel -->
        <div id="admin-notification-dropdown"
             class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white border border-neutral-300 shadow-lg z-50">
            <div class="px-4 py-3 border-b border-neutral-200 bg-neutral-50 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-neutral-900">Live Alerts</p>
                    <p class="text-[10px] text-neutral-500">Enquiries &amp; received orders</p>
                </div>
                <button type="button"
                        id="admin-notification-mark-read-btn"
                        class="text-[10px] font-bold uppercase tracking-wider text-neutral-600 hover:text-neutral-900 underline cursor-pointer">
                    Mark all read
                </button>
            </div>

            <div id="admin-notification-unlock-banner"
                 class="hidden px-4 py-2.5 bg-amber-50 border-b border-amber-200 flex items-center justify-between gap-2">
                <span class="text-[11px] font-semibold text-amber-900">Browser blocked audio until click.</span>
                <button type="button"
                        id="admin-notification-unlock-btn"
                        class="px-2 py-1 bg-neutral-900 text-white text-[10px] font-bold uppercase tracking-wider cursor-pointer shrink-0">
                    Enable Sound
                </button>
            </div>

            <div id="admin-notification-list" class="max-h-80 overflow-y-auto divide-y divide-neutral-200">
                <div class="p-4 text-center text-xs text-neutral-400">No recent notifications.</div>
            </div>

            <div class="px-4 py-2.5 border-t border-neutral-200 bg-neutral-50 flex items-center justify-between text-[10px] font-bold uppercase tracking-wider">
                <a href="{{ route('admin.inquiries.index') }}" class="text-neutral-700 hover:text-neutral-900 hover:underline">All Inquiries &rarr;</a>
                <a href="{{ route('admin.orders.index') }}" class="text-neutral-700 hover:text-neutral-900 hover:underline">All Orders &rarr;</a>
            </div>
        </div>
    </div>
</div>
