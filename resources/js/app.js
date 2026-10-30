import Lenis from 'lenis';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

/**
 * Menahem Frontend Runtime
 * Features: Alpine.js, Lenis Smooth Inertia Scroll, Scroll Reveals, Sticky Header, WhatsApp Helper
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Lenis Smooth Scrolling
    let lenis = null;
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!prefersReducedMotion) {
        lenis = new Lenis({
            lerp: 0.09,
            smoothWheel: true,
            touchMultiplier: 1.2,
            wheelMultiplier: 0.95,
        });

        function raf(time) {
            lenis.raf(time);
            requestAnimationFrame(raf);
        }
        requestAnimationFrame(raf);

        // Smooth scroll for internal in-page anchor links
        document.addEventListener('click', (e) => {
            const anchor = e.target.closest('a[href^="#"]');
            if (!anchor) return;
            const targetId = anchor.getAttribute('href');
            if (targetId === '#' || !targetId) return;

            const targetElement = document.querySelector(targetId);
            if (targetElement && lenis) {
                e.preventDefault();
                lenis.scrollTo(targetElement, { offset: -70 });
            }
        });
    }

    // 2. Scroll Reveal Observer
    const revealElements = document.querySelectorAll('[data-reveal]');
    if ('IntersectionObserver' in window && !prefersReducedMotion) {
        const revealObserver = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const delay = entry.target.getAttribute('data-reveal-delay') || '0';
                        setTimeout(() => {
                            entry.target.classList.add('is-revealed');
                        }, parseInt(delay, 10));
                        observer.unobserve(entry.target);
                    }
                });
            },
            {
                rootMargin: '0px 0px -60px 0px',
                threshold: 0.08,
            }
        );

        revealElements.forEach((el) => revealObserver.observe(el));
    } else {
        // Fallback for no-js or reduced-motion
        revealElements.forEach((el) => el.classList.add('is-revealed'));
    }

    // 3. Smart Sticky Header Shrink
    const navbar = document.getElementById('main-navbar');
    if (navbar) {
        let lastScrollY = window.scrollY;
        let ticking = false;

        const updateNavbar = () => {
            const scrollY = window.scrollY;
            if (scrollY > 50) {
                navbar.classList.add('scrolled', 'py-2');
                navbar.classList.remove('py-4');
            } else {
                navbar.classList.remove('scrolled', 'py-2');
                navbar.classList.add('py-4');
            }
            lastScrollY = scrollY;
            ticking = false;
        };

        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(updateNavbar);
                ticking = true;
            }
        }, { passive: true });
    }

    // 4. Mobile Menu Drawer
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenuDrawer = document.getElementById('mobile-menu-drawer');
    const mobileMenuClose = document.getElementById('mobile-menu-close');

    if (mobileMenuBtn && mobileMenuDrawer) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenuDrawer.classList.toggle('hidden');
        });
    }

    if (mobileMenuClose && mobileMenuDrawer) {
        mobileMenuClose.addEventListener('click', () => {
            mobileMenuDrawer.classList.add('hidden');
        });
    }

    // 5. Live Countdown Timer
    const daysEl = document.getElementById('timer-days');
    const hrsEl = document.getElementById('timer-hrs');
    const minsEl = document.getElementById('timer-mins');
    const secsEl = document.getElementById('timer-secs');

    if (daysEl && hrsEl && minsEl && secsEl) {
        let totalSeconds = 2 * 24 * 3600 + 14 * 3600 + 36 * 60 + 45;
        setInterval(() => {
            if (totalSeconds > 0) {
                totalSeconds--;
                const d = Math.floor(totalSeconds / (3600 * 24));
                const h = Math.floor((totalSeconds % (3600 * 24)) / 3600);
                const m = Math.floor((totalSeconds % 3600) / 60);
                const s = totalSeconds % 60;

                daysEl.textContent = String(d).padStart(2, '0');
                hrsEl.textContent = String(h).padStart(2, '0');
                minsEl.textContent = String(m).padStart(2, '0');
                secsEl.textContent = String(s).padStart(2, '0');
            }
        }, 1000);
    }

    // Expose lenis instance globally if needed
    window.Menahem = {
        lenis,
    };
});
