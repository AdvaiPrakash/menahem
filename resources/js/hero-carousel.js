/**
 * Hero Section – Smooth Infinite Auto-Scrolling Interactive Product Carousel
 *
 * Features:
 * - Fluid, frame-rate independent horizontal auto-scrolling via requestAnimationFrame & translate3d
 * - Seamless mobile touch & desktop pointer drag (left & right swiping)
 * - 1:1 direct finger tracking with natural momentum release
 * - Autoplay does NOT pause on touch, tap, hover, or click; smoothly continues after dragging
 * - Non-intrusive gesture disambiguation: vertical touch gestures scroll the page normally
 * - Strict click vs. drag distinction: tapping opens product detail page, dragging never triggers accidental clicks
 * - Mathematically airtight infinite modulo loop with 3-group buffer (0px jumps or gaps)
 * - Zero layout recalculations / forced reflows inside the animation loop
 * - Viewport intersection & document visibility handling to pause when offscreen
 */

export function initHeroCarousel() {
    const containers = document.querySelectorAll('[data-hero-carousel]');
    if (!containers.length) return;

    containers.forEach((container) => {
        setupSingleCarousel(container);
    });
}

function setupSingleCarousel(container) {
    const track = container.querySelector('[data-carousel-track]');
    if (!track) return;

    const groups = track.querySelectorAll('[data-carousel-group]');
    if (groups.length < 2) return;

    const group0 = groups[0];
    const group1 = groups[1];

    // Reduced motion check
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const baseAutoSpeed = prefersReducedMotion ? 0 : -48; // px/sec moving leftwards

    let period = 0;
    let currentX = 0;
    const autoSpeed = baseAutoSpeed;
    let momentumVelocity = 0; // px/sec

    // Interaction state
    let isPointerDown = false;
    let isDragging = false;
    let preventClick = false;
    let direction = null; // null | 'horizontal' | 'vertical'
    let startX = 0;
    let startY = 0;
    let lastX = 0;
    let lastTime = 0;
    let dragStartX = 0;
    let dragStartCurrentX = 0;
    let velocitySamples = [];
    let activePointerId = null;

    // Visibility & Lifecycle state
    let inViewport = true;
    let isRunning = false;
    let rafId = null;
    let lastFrameTime = null;

    function measurePeriod() {
        if (!container.offsetParent && container.clientWidth === 0) {
            return false;
        }

        const g0Left = group0.offsetLeft;
        const g1Left = group1.offsetLeft;
        const calculatedPeriod = g1Left - g0Left;

        if (calculatedPeriod > 50) {
            period = calculatedPeriod;
            if (currentX === 0) {
                currentX = -period;
            }
            normalizeX();
            applyTransform();
            return true;
        }
        return false;
    }

    function normalizeX() {
        if (!period || period <= 0 || !Number.isFinite(period) || !Number.isFinite(currentX)) {
            return;
        }
        // Mathematically airtight O(1) modulo wrap within [-2 * period, -period)
        let rel = (currentX + period) % period;
        if (rel > 0) {
            rel -= period;
        }
        currentX = -period + rel;
    }

    function applyTransform() {
        track.style.transform = `translate3d(${currentX.toFixed(2)}px, 0, 0)`;
    }

    function startLoop() {
        if (isRunning) return;
        if (!inViewport || (container.offsetWidth === 0 && container.clientHeight === 0)) {
            return;
        }
        isRunning = true;
        lastFrameTime = performance.now();
        rafId = requestAnimationFrame(loop);
    }

    function stopLoop() {
        if (!isRunning) return;
        isRunning = false;
        if (rafId) {
            cancelAnimationFrame(rafId);
            rafId = null;
        }
    }

    // High performance RAF loop: completely pauses when off-screen or tab is backgrounded
    function loop(time) {
        if (!isRunning) return;

        const isVisible = inViewport && (container.offsetParent !== null || container.clientWidth > 0);
        if (!isVisible) {
            stopLoop();
            return;
        }

        if (!lastFrameTime) lastFrameTime = time;
        const dt = Math.min((time - lastFrameTime) / 1000, 0.1);
        lastFrameTime = time;

        if (period > 0) {
            const isActivelyDragging = isPointerDown && direction === 'horizontal';

            if (!isActivelyDragging) {
                // Apply momentum decay if released with drag velocity
                if (Math.abs(momentumVelocity) > 2) {
                    const decay = Math.pow(0.88, dt * 60);
                    momentumVelocity *= decay;
                    currentX += momentumVelocity * dt;
                } else {
                    momentumVelocity = 0;
                }

                // Continuous constant auto-scrolling
                currentX += autoSpeed * dt;

                normalizeX();
                applyTransform();
            }
        }

        rafId = requestAnimationFrame(loop);
    }

    function attachPointerListeners() {
        window.addEventListener('pointermove', onPointerMove, { passive: false });
        window.addEventListener('pointerup', onPointerUp, { passive: true });
        window.addEventListener('pointercancel', onPointerUp, { passive: true });
    }

    function detachPointerListeners() {
        window.removeEventListener('pointermove', onPointerMove, { passive: false });
        window.removeEventListener('pointerup', onPointerUp, { passive: true });
        window.removeEventListener('pointercancel', onPointerUp, { passive: true });
    }

    // Pointer event handlers
    function onPointerDown(e) {
        if (e.button !== undefined && e.button !== 0) return;

        isPointerDown = true;
        activePointerId = e.pointerId;
        startX = e.clientX;
        startY = e.clientY;
        lastX = e.clientX;
        lastTime = performance.now();
        direction = null;
        isDragging = false;
        momentumVelocity = 0;
        velocitySamples = [];
        attachPointerListeners();
    }

    function onPointerMove(e) {
        if (!isPointerDown || (activePointerId !== null && e.pointerId !== activePointerId)) return;

        const dx = e.clientX - startX;
        const dy = e.clientY - startY;

        if (direction === null) {
            const absX = Math.abs(dx);
            const absY = Math.abs(dy);

            // Need at least 6px of motion before determining user intent
            if (absX < 6 && absY < 6) {
                return;
            }

            if (absY > absX) {
                // Native vertical webpage scrolling: release pointer immediately
                direction = 'vertical';
                isPointerDown = false;
                activePointerId = null;
                return;
            } else {
                // Manual horizontal carousel drag: capture gesture
                direction = 'horizontal';
                isDragging = true;
                preventClick = true;
                dragStartX = e.clientX;
                dragStartCurrentX = currentX;

                try {
                    if (track.setPointerCapture) {
                        track.setPointerCapture(e.pointerId);
                    }
                } catch (_) {}
            }
        }

        if (direction === 'horizontal') {
            if (e.cancelable) {
                e.preventDefault();
            }

            const now = performance.now();
            const sampleDt = (now - lastTime) / 1000;
            if (sampleDt > 0.005) {
                const v = (e.clientX - lastX) / sampleDt;
                velocitySamples.push({ v, t: now });
                velocitySamples = velocitySamples.filter((s) => now - s.t < 100);
                lastX = e.clientX;
                lastTime = now;
            }

            currentX = dragStartCurrentX + (e.clientX - dragStartX);
            normalizeX();
            applyTransform();
        }
    }

    function onPointerUp(e) {
        if (activePointerId !== null && e.pointerId !== activePointerId) return;

        try {
            if (track.hasPointerCapture && track.hasPointerCapture(e.pointerId)) {
                track.releasePointerCapture(e.pointerId);
            }
        } catch (_) {}

        const wasDraggingHorizontal = direction === 'horizontal' && isDragging;

        isPointerDown = false;
        activePointerId = null;

        if (wasDraggingHorizontal) {
            const now = performance.now();
            const recent = velocitySamples.filter((s) => now - s.t < 100);
            if (recent.length > 0) {
                const avgV = recent.reduce((sum, s) => sum + s.v, 0) / recent.length;
                momentumVelocity = Math.max(-1400, Math.min(1400, avgV));
            } else {
                momentumVelocity = 0;
            }

            // Suppress accidental click events that fire right after a drag
            setTimeout(() => {
                preventClick = false;
                isDragging = false;
            }, 80);
        } else {
            preventClick = false;
            isDragging = false;
        }

        direction = null;
        detachPointerListeners();
    }

    // Intercept clicks if user was dragging
    track.addEventListener(
        'click',
        (e) => {
            if (preventClick || isDragging) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        },
        true // capture phase
    );

    // Prevent native drag-and-drop ghost image on cards & images
    track.querySelectorAll('img, a').forEach((el) => {
        el.setAttribute('draggable', 'false');
        el.addEventListener('dragstart', (e) => e.preventDefault());
    });

    track.addEventListener('pointerdown', onPointerDown, { passive: true });

    // Initial period measurement with retries only if container is currently rendered
    if (container.offsetWidth > 0) {
        if (!measurePeriod()) {
            let attempts = 0;
            const retryTimer = setInterval(() => {
                attempts++;
                if (measurePeriod() || attempts > 15) {
                    clearInterval(retryTimer);
                    if (inViewport && container.offsetWidth > 0) {
                        startLoop();
                    }
                }
            }, 50);
        } else {
            if (inViewport) {
                startLoop();
            }
        }
    }

    // Window load & fonts ready verification
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(() => {
            if (container.offsetWidth > 0) {
                measurePeriod();
                if (inViewport) startLoop();
            }
        });
    }
    window.addEventListener(
        'load',
        () => {
            if (container.offsetWidth > 0) {
                measurePeriod();
                if (inViewport) startLoop();
            }
        },
        { once: true }
    );

    // Debounced window resize handler (handles breakpoint switches between mobile & desktop carousels)
    let resizeTimer = null;
    window.addEventListener(
        'resize',
        () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                const isRendered = container.offsetWidth > 0;
                if (isRendered) {
                    measurePeriod();
                    if (inViewport) {
                        startLoop();
                    }
                } else {
                    stopLoop();
                }
            }, 100);
        },
        { passive: true }
    );

    // IntersectionObserver to pause RAF when out of viewport
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            ([entry]) => {
                inViewport = entry.isIntersecting;
                if (inViewport && container.offsetWidth > 0) {
                    measurePeriod();
                    startLoop();
                } else {
                    stopLoop();
                }
            },
            { threshold: 0.05 }
        );
        observer.observe(container);
    }

    // Page visibility listener
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            inViewport = false;
            stopLoop();
        } else {
            inViewport = true;
            if (container.offsetWidth > 0) {
                measurePeriod();
                startLoop();
            }
        }
    });
}
