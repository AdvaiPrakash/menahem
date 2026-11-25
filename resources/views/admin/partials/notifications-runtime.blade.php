@inject('adminNotificationService', 'App\Services\AdminNotificationService')
@php
    $initialNotificationState = $adminNotificationService->getInitialState(auth()->id());
@endphp

<!-- Live Notification Toast Stack -->
<div id="admin-notification-toast-container"
     aria-live="polite"
     aria-atomic="false"
     class="fixed top-20 right-4 sm:right-6 z-50 w-[calc(100vw-2rem)] sm:w-96 space-y-2.5 pointer-events-none"></div>

<!-- Preloaded Voice Notification Audio Elements -->
<audio id="admin-audio-inquiry" src="{{ $initialNotificationState['audio']['inquiry'] }}" preload="auto"></audio>
<audio id="admin-audio-order" src="{{ $initialNotificationState['audio']['order'] }}" preload="auto"></audio>

<script>
    (function () {
        if (window.__menahemAdminNotificationsInitialized) {
            return;
        }
        window.__menahemAdminNotificationsInitialized = true;

        const initialState = @json($initialNotificationState);
        const pollUrl = @json(route('admin.notifications.poll'));
        const streamUrl = @json(route('admin.notifications.stream'));
        const liveDataUrl = @json(route('admin.notifications.live-data'));
        const markReadUrlTemplate = @json(route('admin.notifications.read', ['notification' => '__ID__']));
        const markAllReadUrl = @json(route('admin.notifications.read-all'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const STORAGE_SOUND_KEY = 'menahem_admin_voice_enabled';
        const SESSION_PLAYED_AUDIO_KEY = 'menahem_admin_played_audio_ids';
        const POLL_FALLBACK_INTERVAL_MS = 3500;
        const TOAST_TTL_MS = 12000;

        const inquiryAudio = document.getElementById('admin-audio-inquiry');
        const orderAudio = document.getElementById('admin-audio-order');
        const toastContainer = document.getElementById('admin-notification-toast-container');

        const voiceToggleBtn = document.getElementById('admin-voice-toggle-btn');
        const voiceStatusDot = document.getElementById('admin-voice-status-dot');
        const voiceIconOn = document.getElementById('admin-voice-icon-on');
        const voiceIconOff = document.getElementById('admin-voice-icon-off');
        const voiceToggleLabel = document.getElementById('admin-voice-toggle-label');

        const bellWrapper = document.getElementById('admin-notification-bell-wrapper');
        const bellBtn = document.getElementById('admin-notification-bell-btn');
        const dropdown = document.getElementById('admin-notification-dropdown');
        const unreadBadge = document.getElementById('admin-notification-unread-badge');
        const markReadBtn = document.getElementById('admin-notification-mark-read-btn');
        const unlockBanner = document.getElementById('admin-notification-unlock-banner');
        const unlockBtn = document.getElementById('admin-notification-unlock-btn');
        const notificationList = document.getElementById('admin-notification-list');
        const realtimeStatusDot = document.getElementById('admin-realtime-status-dot');
        const realtimeStatusText = document.getElementById('admin-realtime-status-text');

        const ordersSidebarBadge = document.getElementById('admin-sidebar-orders-badge');
        const inquiriesSidebarBadge = document.getElementById('admin-sidebar-inquiries-badge');

        let soundEnabled = readSoundPreference();
        let audioUnlocked = false;
        let audioBlocked = false;
        let isPlayingAudio = false;
        let isPolling = false;
        let isSyncingLiveData = false;
        let lastBlockedAudio = null;

        let eventSource = null;
        let fallbackPollTimer = null;
        let sseReconnectTimer = null;
        let sseRetryCount = 0;

        let cursorAfterId = Number(initialState.cursor?.after_id || 0);
        let unreadCount = Number(initialState.unread_count || 0);
        let currentSyncTokens = initialState.sync_tokens || {};

        let recentNotifications = Array.isArray(initialState.recent_notifications)
            ? initialState.recent_notifications.slice(0, 15)
            : [];

        const seenIds = new Set(recentNotifications.map((item) => Number(item.id)));
        const playedAudioIds = new Set(readJsonArray(sessionStorage, SESSION_PLAYED_AUDIO_KEY).map(Number));
        const audioQueue = [];

        function readJsonArray(storage, key) {
            try {
                const raw = storage.getItem(key);
                const parsed = raw ? JSON.parse(raw) : [];
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) {
                return [];
            }
        }

        function writeJsonArray(storage, key, setObj, maxItems) {
            try {
                const items = Array.from(setObj).slice(-maxItems);
                storage.setItem(key, JSON.stringify(items));
            } catch (e) {}
        }

        function readSoundPreference() {
            try {
                const val = localStorage.getItem(STORAGE_SOUND_KEY);
                return val === null ? true : val === 'true';
            } catch (e) {
                return true;
            }
        }

        function saveSoundPreference(enabled) {
            try {
                localStorage.setItem(STORAGE_SOUND_KEY, enabled ? 'true' : 'false');
            } catch (e) {}
        }

        function setConnectionStatus(mode) {
            if (!realtimeStatusDot || !realtimeStatusText) {
                return;
            }
            if (mode === 'sse') {
                realtimeStatusDot.className = 'w-1.5 h-1.5 bg-emerald-600 inline-block';
                realtimeStatusText.textContent = 'Enquiries & orders • Live';
            } else if (mode === 'poll') {
                realtimeStatusDot.className = 'w-1.5 h-1.5 bg-amber-500 inline-block';
                realtimeStatusText.textContent = 'Enquiries & orders • Syncing';
            } else {
                realtimeStatusDot.className = 'w-1.5 h-1.5 bg-rose-600 inline-block';
                realtimeStatusText.textContent = 'Connection lost • Reconnecting...';
            }
        }

        function updateVoiceToggleUI() {
            if (!voiceToggleBtn) {
                return;
            }
            if (soundEnabled) {
                voiceIconOn?.classList.remove('hidden');
                voiceIconOff?.classList.add('hidden');
                if (voiceToggleLabel) {
                    voiceToggleLabel.textContent = audioBlocked ? 'Click for Voice' : 'Voice On';
                }
                if (voiceStatusDot) {
                    voiceStatusDot.className = audioBlocked
                        ? 'w-2 h-2 bg-amber-500 animate-pulse shrink-0'
                        : 'w-2 h-2 bg-emerald-600 shrink-0';
                }
            } else {
                voiceIconOn?.classList.add('hidden');
                voiceIconOff?.classList.remove('hidden');
                if (voiceToggleLabel) {
                    voiceToggleLabel.textContent = 'Muted';
                }
                if (voiceStatusDot) {
                    voiceStatusDot.className = 'w-2 h-2 bg-neutral-400 shrink-0';
                }
            }

            if (unlockBanner) {
                unlockBanner.classList.toggle('hidden', !(soundEnabled && audioBlocked));
            }
        }

        function unlockAudioElements() {
            if (audioUnlocked) {
                return;
            }
            const targets = [inquiryAudio, orderAudio].filter(Boolean);
            targets.forEach((audioEl) => {
                try {
                    const prevMuted = audioEl.muted;
                    audioEl.muted = true;
                    const playPromise = audioEl.play();
                    if (playPromise && typeof playPromise.then === 'function') {
                        playPromise.then(() => {
                            audioEl.pause();
                            audioEl.currentTime = 0;
                            audioEl.muted = prevMuted;
                            audioUnlocked = true;
                            audioBlocked = false;
                            updateVoiceToggleUI();
                            flushRecentBlockedAudio();
                        }).catch(() => {
                            audioEl.muted = prevMuted;
                        });
                    }
                } catch (e) {}
            });
        }

        function flushRecentBlockedAudio() {
            if (!soundEnabled || !lastBlockedAudio) {
                return;
            }
            if (Date.now() - lastBlockedAudio.blockedAt <= 60000) {
                audioQueue.push(lastBlockedAudio.type);
                processAudioQueue();
            }
            lastBlockedAudio = null;
        }

        function enqueueAudio(notificationId, type) {
            if (!soundEnabled) {
                return;
            }
            const numericId = Number(notificationId);
            if (numericId && playedAudioIds.has(numericId)) {
                return;
            }
            if (numericId) {
                playedAudioIds.add(numericId);
                writeJsonArray(sessionStorage, SESSION_PLAYED_AUDIO_KEY, playedAudioIds, 200);
            }
            audioQueue.push(type);
            processAudioQueue();
        }

        function processAudioQueue() {
            if (isPlayingAudio || audioQueue.length === 0 || !soundEnabled) {
                return;
            }

            const type = audioQueue.shift();
            const audioEl = type === 'order' ? orderAudio : inquiryAudio;
            if (!audioEl) {
                processAudioQueue();
                return;
            }

            isPlayingAudio = true;

            const finish = () => {
                audioEl.removeEventListener('ended', finish);
                audioEl.removeEventListener('error', finish);
                isPlayingAudio = false;
                processAudioQueue();
            };

            audioEl.addEventListener('ended', finish);
            audioEl.addEventListener('error', finish);

            try {
                audioEl.pause();
                audioEl.currentTime = 0;
                audioEl.muted = false;
                const playPromise = audioEl.play();
                if (playPromise && typeof playPromise.then === 'function') {
                    playPromise.then(() => {
                        audioUnlocked = true;
                        audioBlocked = false;
                        updateVoiceToggleUI();
                    }).catch(() => {
                        audioEl.removeEventListener('ended', finish);
                        audioEl.removeEventListener('error', finish);
                        isPlayingAudio = false;
                        audioBlocked = true;
                        lastBlockedAudio = { type, blockedAt: Date.now() };
                        updateVoiceToggleUI();
                    });
                }
            } catch (e) {
                finish();
            }
        }

        function isSafeUrl(url) {
            if (typeof url !== 'string' || !url.length) {
                return false;
            }
            return url.startsWith('/') || url.startsWith(window.location.origin);
        }

        function updateSidebarBadges(counts) {
            if (!counts) {
                return;
            }
            if (ordersSidebarBadge && typeof counts.pending_orders === 'number') {
                ordersSidebarBadge.textContent = String(counts.pending_orders);
                ordersSidebarBadge.classList.toggle('hidden', counts.pending_orders <= 0);
            }
            if (inquiriesSidebarBadge && typeof counts.new_inquiries === 'number') {
                inquiriesSidebarBadge.textContent = String(counts.new_inquiries);
                inquiriesSidebarBadge.classList.toggle('hidden', counts.new_inquiries <= 0);
            }
        }

        async function markNotificationRead(id) {
            if (!id) {
                return;
            }
            recentNotifications = recentNotifications.map((item) =>
                Number(item.id) === Number(id) ? { ...item, is_read: true } : item
            );
            unreadCount = Math.max(0, unreadCount - 1);
            renderDropdown();

            try {
                const url = markReadUrlTemplate.replace('__ID__', String(id));
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    keepalive: true,
                });
                if (response.ok) {
                    const data = await response.json();
                    if (typeof data.unread_count === 'number') {
                        unreadCount = data.unread_count;
                        renderDropdown();
                    }
                }
            } catch (e) {}
        }

        async function markAllRead() {
            recentNotifications = recentNotifications.map((item) => ({ ...item, is_read: true }));
            unreadCount = 0;
            renderDropdown();

            try {
                const response = await fetch(markAllReadUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                if (response.ok) {
                    const data = await response.json();
                    if (typeof data.unread_count === 'number') {
                        unreadCount = data.unread_count;
                        renderDropdown();
                    }
                }
            } catch (e) {}
        }

        function renderDropdown() {
            if (unreadBadge) {
                unreadBadge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
                unreadBadge.classList.toggle('hidden', unreadCount <= 0);
            }

            if (!notificationList) {
                return;
            }

            notificationList.innerHTML = '';

            if (recentNotifications.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'p-5 text-center text-xs text-neutral-400';
                empty.textContent = 'No recent enquiries or orders yet.';
                notificationList.appendChild(empty);
                return;
            }

            recentNotifications.forEach((item) => {
                const isRead = Boolean(item.is_read);
                const row = document.createElement('a');
                row.href = isSafeUrl(item.url) ? item.url : '#';
                row.className = 'block px-4 py-3 hover:bg-neutral-50 transition-colors ' + (isRead ? 'bg-white opacity-75' : 'bg-neutral-50/80');
                row.addEventListener('click', () => {
                    if (!isRead) {
                        markNotificationRead(item.id);
                    }
                });

                const top = document.createElement('div');
                top.className = 'flex items-center justify-between gap-2 mb-1';

                const badge = document.createElement('span');
                badge.className = item.type === 'order'
                    ? 'px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-300'
                    : 'px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300';
                badge.textContent = item.title || (item.type === 'order' ? 'New Order' : 'Enquiry');

                const metaRight = document.createElement('div');
                metaRight.className = 'flex items-center gap-1.5';

                const time = document.createElement('span');
                time.className = 'text-[10px] text-neutral-400';
                time.textContent = item.time_ago || '';
                metaRight.appendChild(time);

                if (!isRead) {
                    const dot = document.createElement('span');
                    dot.className = 'w-1.5 h-1.5 bg-rose-600';
                    metaRight.appendChild(dot);
                }

                top.appendChild(badge);
                top.appendChild(metaRight);

                const msg = document.createElement('p');
                msg.className = 'text-xs font-bold text-neutral-900 truncate';
                msg.textContent = item.message || item.title || '';

                const detail = document.createElement('p');
                detail.className = 'text-[11px] text-neutral-600 truncate mt-0.5';
                detail.textContent = item.detail || '';

                row.appendChild(top);
                row.appendChild(msg);
                row.appendChild(detail);

                notificationList.appendChild(row);
            });
        }

        function showToast(item) {
            if (!toastContainer) {
                return;
            }

            const card = document.createElement('div');
            const isOrder = item.type === 'order';
            card.className = 'pointer-events-auto bg-white border border-neutral-300 shadow-lg p-3.5 border-l-4 ' + (isOrder ? 'border-l-emerald-600' : 'border-l-amber-500');

            const header = document.createElement('div');
            header.className = 'flex items-center justify-between gap-2 mb-1.5';

            const badge = document.createElement('span');
            badge.className = isOrder
                ? 'px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-300'
                : 'px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300';
            badge.textContent = item.title || (isOrder ? 'New Order Placed' : 'New Enquiry Received');

            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'text-neutral-400 hover:text-neutral-900 text-xs font-bold px-1 cursor-pointer';
            closeBtn.setAttribute('aria-label', 'Dismiss notification');
            closeBtn.textContent = '✕';
            closeBtn.addEventListener('click', () => card.remove());

            header.appendChild(badge);
            header.appendChild(closeBtn);

            const title = document.createElement('p');
            title.className = 'text-xs font-bold text-neutral-900';
            title.textContent = item.message || '';

            const detail = document.createElement('p');
            detail.className = 'text-[11px] text-neutral-600 mt-0.5 line-clamp-2';
            detail.textContent = item.detail || '';

            const footer = document.createElement('div');
            footer.className = 'mt-2.5 pt-2 border-t border-neutral-100 flex items-center justify-between gap-2';

            const link = document.createElement('a');
            link.href = isSafeUrl(item.url) ? item.url : '#';
            link.className = 'text-[11px] font-bold uppercase tracking-wider text-neutral-900 hover:underline';
            link.textContent = isOrder ? 'View Order →' : 'View Enquiry →';
            link.addEventListener('click', () => markNotificationRead(item.id));

            footer.appendChild(link);

            if (soundEnabled && audioBlocked) {
                const playBtn = document.createElement('button');
                playBtn.type = 'button';
                playBtn.className = 'px-2 py-1 bg-neutral-900 text-white text-[10px] font-bold uppercase tracking-wider cursor-pointer';
                playBtn.textContent = 'Play Voice';
                playBtn.addEventListener('click', () => {
                    audioBlocked = false;
                    audioUnlocked = true;
                    updateVoiceToggleUI();
                    audioQueue.push(item.type);
                    processAudioQueue();
                    playBtn.remove();
                });
                footer.appendChild(playBtn);
            } else {
                const timeSpan = document.createElement('span');
                timeSpan.className = 'text-[10px] text-neutral-400';
                timeSpan.textContent = item.payload?.submitted_at || item.payload?.order_date || 'Just now';
                footer.appendChild(timeSpan);
            }

            card.appendChild(header);
            card.appendChild(title);
            if (item.detail) {
                card.appendChild(detail);
            }
            card.appendChild(footer);

            toastContainer.prepend(card);

            let dismissTimer = setTimeout(() => card.remove(), TOAST_TTL_MS);
            card.addEventListener('mouseenter', () => clearTimeout(dismissTimer));
            card.addEventListener('mouseleave', () => {
                dismissTimer = setTimeout(() => card.remove(), 5000);
            });
        }

        async function syncLiveData() {
            const contextEl = document.querySelector('[data-live-context]');
            if (!contextEl || isSyncingLiveData) {
                return;
            }

            const context = contextEl.getAttribute('data-live-context');
            const currentParams = new URLSearchParams(window.location.search);
            const currentPage = currentParams.get('page');

            // Only auto-refresh list tables on page 1 so we don't disrupt pagination browsing
            if (currentPage && currentPage !== '1' && context !== 'dashboard') {
                return;
            }

            isSyncingLiveData = true;

            try {
                const query = new URLSearchParams(currentParams.toString());
                query.set('context', context);

                const response = await fetch(`${liveDataUrl}?${query.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();

                if (data.context === 'dashboard') {
                    const revEl = document.getElementById('live-dashboard-total-revenue');
                    const paidEl = document.getElementById('live-dashboard-paid-orders');
                    const inqTotalEl = document.getElementById('live-dashboard-total-inquiries');
                    const inqNewEl = document.getElementById('live-dashboard-new-inquiries');
                    const recentOrdersEl = document.getElementById('live-dashboard-recent-orders');
                    const recentInquiriesEl = document.getElementById('live-dashboard-recent-inquiries');

                    if (revEl && data.metrics?.total_revenue) revEl.textContent = data.metrics.total_revenue;
                    if (paidEl && data.metrics?.paid_orders_label) paidEl.textContent = data.metrics.paid_orders_label;
                    if (inqTotalEl && data.metrics?.total_inquiries) inqTotalEl.textContent = data.metrics.total_inquiries;
                    if (inqNewEl && data.metrics?.new_inquiries_label) inqNewEl.textContent = data.metrics.new_inquiries_label;
                    if (recentOrdersEl && typeof data.html?.recent_orders === 'string') recentOrdersEl.innerHTML = data.html.recent_orders;
                    if (recentInquiriesEl && typeof data.html?.recent_inquiries === 'string') recentInquiriesEl.innerHTML = data.html.recent_inquiries;
                } else if (data.context === 'inquiries') {
                    const counts = data.status_counts || {};
                    const setTabText = (id, label, count) => {
                        const el = document.getElementById(id);
                        if (el && typeof count === 'number') {
                            el.textContent = `${label} (${count})`;
                        }
                    };
                    setTabText('live-inquiries-tab-all', 'All', counts.all);
                    setTabText('live-inquiries-tab-pending', 'Pending', counts.pending);
                    setTabText('live-inquiries-tab-replied', 'Replied', counts.replied);
                    setTabText('live-inquiries-tab-failed', 'Failed', counts.failed);
                    setTabText('live-inquiries-tab-closed', 'Closed', counts.closed);

                    const desktopTbody = document.getElementById('live-inquiries-desktop-tbody');
                    const mobileCards = document.getElementById('live-inquiries-mobile-cards');
                    if (desktopTbody && typeof data.html?.desktop_rows === 'string') desktopTbody.innerHTML = data.html.desktop_rows;
                    if (mobileCards && typeof data.html?.mobile_cards === 'string') mobileCards.innerHTML = data.html.mobile_cards;
                } else if (data.context === 'orders') {
                    const totalEl = document.getElementById('live-orders-total');
                    const revEl = document.getElementById('live-orders-revenue');
                    const paidEl = document.getElementById('live-orders-paid-label');
                    const procEl = document.getElementById('live-orders-processing');
                    const delivEl = document.getElementById('live-orders-delivered');
                    const tbodyEl = document.getElementById('live-orders-tbody');

                    if (totalEl && data.stats?.total) totalEl.textContent = data.stats.total;
                    if (revEl && data.stats?.total_revenue) revEl.textContent = data.stats.total_revenue;
                    if (paidEl && data.stats?.paid_label) paidEl.textContent = data.stats.paid_label;
                    if (procEl && data.stats?.processing) procEl.textContent = data.stats.processing;
                    if (delivEl && data.stats?.delivered) delivEl.textContent = data.stats.delivered;
                    if (tbodyEl && typeof data.html?.table_rows === 'string') tbodyEl.innerHTML = data.html.table_rows;
                }
            } catch (e) {
                // Ignore transient network errors
            } finally {
                isSyncingLiveData = false;
            }
        }

        function processServerPayload(data) {
            if (!data || typeof data !== 'object') {
                return;
            }

            if (data.cursor && typeof data.cursor.after_id === 'number') {
                cursorAfterId = Math.max(cursorAfterId, data.cursor.after_id);
            }

            if (typeof data.unread_count === 'number') {
                unreadCount = data.unread_count;
            }

            if (data.counts) {
                updateSidebarBadges(data.counts);
            }

            if (Array.isArray(data.recent_notifications)) {
                recentNotifications = data.recent_notifications.slice(0, 15);
            }

            const incoming = Array.isArray(data.notifications) ? data.notifications : [];
            const freshItems = [];

            incoming.forEach((item) => {
                const numericId = Number(item?.id || 0);
                if (!numericId || seenIds.has(numericId)) {
                    return;
                }
                seenIds.add(numericId);
                freshItems.push(item);
            });

            if (freshItems.length > 0) {
                freshItems.forEach((item) => {
                    if (!Array.isArray(data.recent_notifications)) {
                        recentNotifications = [item, ...recentNotifications.filter((n) => Number(n.id) !== Number(item.id))].slice(0, 15);
                    }
                    showToast(item);
                    enqueueAudio(item.id, item.type);
                });
            }

            renderDropdown();

            const nextTokens = data.sync_tokens || {};
            const tokensChanged =
                nextTokens.inquiry !== currentSyncTokens.inquiry ||
                nextTokens.order !== currentSyncTokens.order;

            if (tokensChanged || freshItems.length > 0) {
                currentSyncTokens = nextTokens;
                syncLiveData();
            }
        }

        async function pollNotifications() {
            if (isPolling || !navigator.onLine) {
                return;
            }
            isPolling = true;

            try {
                const params = new URLSearchParams({
                    after_id: String(cursorAfterId || 0),
                });

                const response = await fetch(`${pollUrl}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                processServerPayload(data);
            } catch (e) {
                // Ignore transient network errors
            } finally {
                isPolling = false;
            }
        }

        function startFallbackPolling() {
            if (fallbackPollTimer) {
                return;
            }
            setConnectionStatus('poll');
            pollNotifications();
            fallbackPollTimer = setInterval(pollNotifications, POLL_FALLBACK_INTERVAL_MS);
        }

        function stopFallbackPolling() {
            if (fallbackPollTimer) {
                clearInterval(fallbackPollTimer);
                fallbackPollTimer = null;
            }
        }

        function connectSSE() {
            if (typeof window.EventSource !== 'function') {
                startFallbackPolling();
                return;
            }

            if (eventSource) {
                eventSource.close();
                eventSource = null;
            }

            const params = new URLSearchParams({
                after_id: String(cursorAfterId || 0),
            });

            eventSource = new EventSource(`${streamUrl}?${params.toString()}`, {
                withCredentials: true,
            });

            eventSource.addEventListener('open', () => {
                sseRetryCount = 0;
                stopFallbackPolling();
                setConnectionStatus('sse');
            });

            eventSource.addEventListener('notification', (event) => {
                sseRetryCount = 0;
                stopFallbackPolling();
                setConnectionStatus('sse');
                try {
                    const data = JSON.parse(event.data);
                    processServerPayload(data);
                } catch (e) {}
            });

            eventSource.addEventListener('error', () => {
                if (eventSource) {
                    eventSource.close();
                    eventSource = null;
                }

                if (!navigator.onLine) {
                    setConnectionStatus('offline');
                    return;
                }

                sseRetryCount += 1;
                startFallbackPolling();

                const delayMs = Math.min(15000, 2000 * Math.pow(2, Math.min(sseRetryCount - 1, 3)));
                clearTimeout(sseReconnectTimer);
                sseReconnectTimer = setTimeout(connectSSE, delayMs);
            });
        }

        // Event bindings
        ['pointerdown', 'keydown', 'click'].forEach((evtName) => {
            document.addEventListener(evtName, unlockAudioElements, { once: true, passive: true });
        });

        voiceToggleBtn?.addEventListener('click', () => {
            soundEnabled = !soundEnabled;
            saveSoundPreference(soundEnabled);
            if (soundEnabled) {
                audioBlocked = false;
                unlockAudioElements();
                flushRecentBlockedAudio();
            } else {
                audioQueue.length = 0;
                lastBlockedAudio = null;
            }
            updateVoiceToggleUI();
        });

        unlockBtn?.addEventListener('click', () => {
            audioBlocked = false;
            audioUnlocked = true;
            updateVoiceToggleUI();
            flushRecentBlockedAudio();
        });

        bellBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            if (!dropdown) {
                return;
            }
            const isHidden = dropdown.classList.contains('hidden');
            dropdown.classList.toggle('hidden', !isHidden);
            bellBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
        });

        markReadBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            markAllRead();
        });

        document.addEventListener('click', (e) => {
            if (dropdown && bellWrapper && !bellWrapper.contains(e.target)) {
                dropdown.classList.add('hidden');
                bellBtn?.setAttribute('aria-expanded', 'false');
            }
        });

        window.addEventListener('online', () => {
            pollNotifications();
            connectSSE();
        });

        window.addEventListener('offline', () => {
            setConnectionStatus('offline');
            if (eventSource) {
                eventSource.close();
                eventSource = null;
            }
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && navigator.onLine) {
                pollNotifications();
                if (!eventSource || eventSource.readyState === EventSource.CLOSED) {
                    connectSSE();
                }
            }
        });

        // Initial render & real-time startup
        updateVoiceToggleUI();
        renderDropdown();
        connectSSE();
    })();
</script>
