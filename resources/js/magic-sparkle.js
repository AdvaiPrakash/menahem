/**
 * Magic Sparkle Effect for "Menahem" in Hero Section
 */
export function initMagicSparkles() {
    const magicContainers = document.querySelectorAll('.magic');
    if (!magicContainers.length) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) return;

    const interval = 1000;

    const rand = (min, max) =>
        Math.floor(Math.random() * (max - min + 1)) + min;

    const animate = (star) => {
        star.style.setProperty('--star-left', `${rand(-10, 100)}%`);
        star.style.setProperty('--star-top', `${rand(-40, 80)}%`);

        star.style.animation = 'none';
        star.offsetHeight; // trigger reflow
        star.style.animation = '';
    };

    magicContainers.forEach((container) => {
        const stars = container.querySelectorAll('.magic-star');
        let index = 0;
        stars.forEach((star) => {
            setTimeout(() => {
                animate(star);
                setInterval(() => animate(star), interval);
            }, index++ * (interval / stars.length));
        });
    });
}
