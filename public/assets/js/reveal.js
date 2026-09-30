import { SELECTOR, CSS_CLASS, REVEAL_THRESHOLD, REVEAL_ROOT_MARGIN, REVEAL_MAX_STEPS, REVEAL_STAGGER_MS } from './constants.js';

export function initReveal() {
    const items = document.querySelectorAll(SELECTOR.REVEAL_ITEM);
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const supportsObserver = 'IntersectionObserver' in window;

    if (reduceMotion || !supportsObserver) {
        items.forEach((item) => item.classList.add(CSS_CLASS.VISIBLE));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        const visibleEntries = entries.filter((entry) => entry.isIntersecting);

        visibleEntries.forEach((entry, index) => {
            const step = Math.min(index, REVEAL_MAX_STEPS);
            entry.target.style.setProperty('--rz-delay', `${step * REVEAL_STAGGER_MS}ms`);
            entry.target.classList.add(CSS_CLASS.VISIBLE);
            observer.unobserve(entry.target);
        });
    }, { threshold: REVEAL_THRESHOLD, rootMargin: REVEAL_ROOT_MARGIN });

    items.forEach((item) => observer.observe(item));
}
