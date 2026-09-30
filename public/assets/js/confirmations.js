import { SELECTOR } from './constants.js';

export function initConfirmations() {
    document.querySelectorAll(SELECTOR.CONFIRM_FORM).forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (window.confirm(form.dataset.confirm)) {
                return;
            }
            event.preventDefault();
        });
    });
}
