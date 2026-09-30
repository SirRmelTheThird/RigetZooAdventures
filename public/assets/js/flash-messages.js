import { SELECTOR, FLASH_DURATION_MS } from './constants.js';

export function initFlashMessages() {
    document.querySelectorAll(SELECTOR.FLASH_MESSAGE).forEach((message) => {
        setTimeout(() => message.remove(), FLASH_DURATION_MS);
    });
}
