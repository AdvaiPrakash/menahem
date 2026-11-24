@inject('adminNotificationService', 'App\Services\AdminNotificationService')
@php
    $initialNotificationState = $adminNotificationService->getInitialState();
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

        const STORAGE_SOUND_KEY = 'menahem_admin_voice_enabled';
        const STORAGE_READ_KEY = 'menahem_admin_read_notification_keys';
        const SESSION_CURSOR_KEY = 'menahem_admin_poll_cursor';
        const SESSION_SEEN_KEY = 'menahem_admin_seen_notification_keys';
        const CURSOR_MAX_AGE_MS = 10 * 60 * 1000;
        const POLL_INTERVAL_MS = 5000;
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

        const ordersSidebarBadge = document.getElementById('admin-sidebar-orders-badge');
        const inquiriesSidebarBadge = document.getElementById('admin-sidebar-inquiries-badge');

        let soundEnabled = readSoundPreference();
        let audioUnlocked = false;
        let audioBlocked = false;
        let isPlayingAudio = false;
        let isPolling = false;
        let lastPollTimestamp = 0;

        const audioQueue = [];
        const pendingBlockedAudio = [];

        const readKeys = new Set(readJsonArray(localStorage, STORAGE_READ_KEY));
        const seenKeys = new Set([
            ...(Array.isArray(initialState.seen_keys) ? initialState.seen_keys : []),
            ...readJsonArray(sessionStorage, SESSION_SEEN_KEY),
        ]);

        let recentNotifications = Array.isArray(initialState.recent_notifications)
            ? initialState.recent_notifications.slice(0, 15)
            : [];

        let cursor = loadCursor();

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

        function loadCursor() {
            const fallback = {
                after_inquiry_id: Number(initialState.cursor?.after_inquiry_id || 0),
                since_order_at: initialState.cursor?.since_order_at || new Date().toISOString(),
                saved_at: Date.now(),
            };

            try {
                const raw = sessionStorage.getItem(SESSION_CURSOR_KEY);
                if (!raw) {
                    return fallback;
                }
                const parsed = JSON.parse(raw);
                if (!parsed || typeof parsed.saved_at !== 'number') {
                    return fallback;
                }
                if (Date.now() - parsed.saved_at > CURSOR_MAX_AGE_MS) {
                    return fallback;
                }
                return {
                    after_inquiry_id: Math.min(
                        Number(parsed.after_inquiry_id || 0),
                        fallback.after_inquiry_id
                    ),
                    since_order_at: parsed.since_order_at || fallback.since_order_at,
                    saved_at: Date.now(),
                };
            } catch (e) {
                return fallback;
            }
        }

        function saveCursor(nextCursor) {
            cursor = {
                after_inquiry_id: Number(nextCursor.after_inquiry_id ?? cursor.after_inquiry_id),
                since_order_at: nextCursor.since_order_at || cursor.since_order_at,
                saved_at: Date.now(),
            };
            try {
                sessionStorage.setItem(SESSION_CURSOR_KEY, JSON.stringify(cursor));
            } catch (e) {}
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
                            flushPendingBlockedAudio();
                        }).catch(() => {
                            audioEl.muted = prevMuted;
                        });
                    }
                } catch (e) {}
            });
        }

        function flushPendingBlockedAudio() {
            if (!soundEnabled || pendingBlockedAudio.length === 0) {
                return;
            }
            while (pendingBlockedAudio.length > 0) {
                audioQueue.push(pendingBlockedAudio.shift());
            }
            processAudioQueue();
        }

        function enqueueAudio(type) {
            if (!soundEnabled) {
                return;
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
                        pendingBlockedAudio.push(type);
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

        function getUnreadCount() {
            return recentNotifications.filter((item) => !readKeys.has(item.key)).length;
        }

        function markAsRead(key) {
            if (!key) {
                return;
            }
            readKeys.add(key);
            writeJsonArray(localStorage, STORAGE_READ_KEY, readKeys, 200);
            renderDropdown();
        }

        function markAllRead() {
            recentNotifications.forEach((item) => readKeys.add(item.key));
            writeJsonArray(localStorage, STORAGE_READ_KEY, readKeys, 200);
            renderDropdown();
        }

        function renderDropdown() {
            const unread = getUnreadCount();
            if (unreadBadge) {
                unreadBadge.textContent = unread > 99 ? '99+' : String(unread);
                unreadBadge.classList.toggle('hidden', unread <= 0);
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
                const isRead = readKeys.has(item.key);
                const row = document.createElement('a');
                row.href = isSafeUrl(item.url) ? item.url : '#';
                row.className = 'block px-4 py-3 hover:bg-neutral-50 transition-colors ' + (isRead ? 'bg-white opacity-75' : 'bg-neutral-50/70');
                row.addEventListener('click', () => markAsRead(item.key));

                const top = document.createElement('div');
                top.className = 'flex items-center justify-between gap-2 mb-1';

                const badge = document.createElement('span');
                badge.className = item.type === 'order'
                    ? 'px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-300'
                    : 'px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300';
                badge.textContent = item.type === 'order' ? 'Order Received' : 'Enquiry';

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
            badge.textContent = item.title || (isOrder ? 'New Order Received' : 'New Enquiry Received');

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
            link.addEventListener('click', () => markAsRead(item.key));

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
                    enqueueAudio(item.type);
                    playBtn.remove();
                });
                footer.appendChild(playBtn);
            } else {
                const timeSpan = document.createElement('span');
                timeSpan.className = 'text-[10px] text-neutral-400';
                timeSpan.textContent = 'Just now';
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

        function handleIncomingNotifications(items) {
            if (!Array.isArray(items) || items.length === 0) {
                return;
            }

            const freshItems = [];
            items.forEach((item) => {
                if (!item || !item.key || seenKeys.has(item.key)) {
                    return;
                }
                seenKeys.add(item.key);
                freshItems.push(item);
            });

            if (freshItems.length === 0) {
                return;
            }

            writeJsonArray(sessionStorage, SESSION_SEEN_KEY, seenKeys, 200);

            freshItems.forEach((item) => {
                recentNotifications = [item, ...recentNotifications.filter((n) => n.key !== item.key)].slice(0, 15);
                enqueueAudio(item.type);
                showToast(item);
            });

            renderDropdown();
        }

        async function pollNotifications() {
            if (isPolling) {
                return;
            }
            isPolling = true;
            lastPollTimestamp = Date.now();

            try {
                const params = new URLSearchParams({
                    after_inquiry_id: String(cursor.after_inquiry_id || 0),
                    since_order_at: String(cursor.since_order_at || ''),
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
                if (data.cursor) {
                    saveCursor(data.cursor);
                }
                if (data.counts) {
                    updateSidebarBadges(data.counts);
                }
                if (Array.isArray(data.notifications) && data.notifications.length > 0) {
                    handleIncomingNotifications(data.notifications);
                }
            } catch (e) {
                // Ignore transient network errors during background polling
            } finally {
                isPolling = false;
            }
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
                flushPendingBlockedAudio();
            } else {
                audioQueue.length = 0;
                pendingBlockedAudio.length = 0;
            }
            updateVoiceToggleUI();
        });

        unlockBtn?.addEventListener('click', () => {
            audioBlocked = false;
            audioUnlocked = true;
            updateVoiceToggleUI();
            flushPendingBlockedAudio();
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

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && Date.now() - lastPollTimestamp >= 2000) {
                pollNotifications();
            }
        });

        // Initial render & polling loop
        updateVoiceToggleUI();
        renderDropdown();
        saveCursor(cursor);

        setTimeout(pollNotifications, 1500);
        setInterval(pollNotifications, POLL_INTERVAL_MS);
    })();
</script>
