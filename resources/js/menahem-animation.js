/**
 * Interactive Gradient Shimmer for the Hero "Menahem" Wordmark
 *
 * Implements mouse-tracking position calculation from the reference:
 * - Updates --position-x and --position-y relative to element bounding rect
 * - Smoothly manages --is-hovered state
 * - Uses requestAnimationFrame to throttle coordinate updates for 60fps performance without CPU spikes
 */

export function initMenahemAnimation() {
    const targets = document.querySelectorAll('[data-menahem-word]');
    if (!targets.length) return;

    targets.forEach((el) => {
        let isHovered = false;
        let ticking = false;
        let mouseX = 50;
        let mouseY = 50;

        function updatePosition() {
            if (!isHovered) {
                ticking = false;
                return;
            }
            el.style.setProperty('--position-x', mouseX.toFixed(2));
            el.style.setProperty('--position-y', mouseY.toFixed(2));
            ticking = false;
        }

        function onMouseMove(e) {
            const rect = el.getBoundingClientRect();
            if (rect.width === 0 || rect.height === 0) return;
            const px = ((e.clientX - rect.left) / rect.width) * 100;
            const py = ((e.clientY - rect.top) / rect.height) * 100;
            mouseX = Math.max(0, Math.min(100, px));
            mouseY = Math.max(0, Math.min(100, py));

            if (!ticking) {
                requestAnimationFrame(updatePosition);
                ticking = true;
            }
        }

        function onMouseEnter(e) {
            isHovered = true;
            el.style.setProperty('--is-hovered', '1');
            onMouseMove(e);
        }

        function onMouseLeave() {
            isHovered = false;
            el.style.setProperty('--is-hovered', '0');
        }

        el.addEventListener('mousemove', onMouseMove, { passive: true });
        el.addEventListener('mouseenter', onMouseEnter, { passive: true });
        el.addEventListener('mouseleave', onMouseLeave, { passive: true });
    });
}
