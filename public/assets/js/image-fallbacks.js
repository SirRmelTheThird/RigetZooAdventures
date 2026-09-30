import { SELECTOR } from './constants.js';

export function initImageFallbacks() {
    document.querySelectorAll(SELECTOR.HIDE_ON_ERROR_IMAGE).forEach((img) => {
        if (!img.complete) {
            return;
        }
        if (img.naturalWidth !== 0) {
            return;
        }
        img.remove();
    });

    document.addEventListener('error', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLImageElement)) {
            return;
        }
        if (!target.hasAttribute('data-hide-on-error')) {
            return;
        }
        target.remove();
    }, true);
}
