
(() => {
    'use strict';

    const REVEAL_STAGGER_MS = 70;
    const REVEAL_MAX_STEPS = 4;
    const CURRENCY_SYMBOL = '£';
    const MONEY_DECIMALS = 2;

    const formatMoney = (amount) => CURRENCY_SYMBOL + amount.toFixed(MONEY_DECIMALS);

    function initImageFallbacks() {
        const hide = (img) => img.remove();

        document.querySelectorAll('img[data-hide-on-error]').forEach((img) => {
            if (img.complete && img.naturalWidth === 0) {
                hide(img);
            }
        });
        document.addEventListener('error', (event) => {
            if (event.target instanceof HTMLImageElement && event.target.hasAttribute('data-hide-on-error')) {
                hide(event.target);
            }
        }, true);
    }

    function initReveal() {
        const items = document.querySelectorAll('.rz-reveal');
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (reduceMotion || !('IntersectionObserver' in window)) {
            items.forEach((item) => item.classList.add('is-visible'));
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            const visible = entries.filter((entry) => entry.isIntersecting);
            visible.forEach((entry, index) => {
                const step = Math.min(index, REVEAL_MAX_STEPS);
                entry.target.style.setProperty('--rz-delay', `${step * REVEAL_STAGGER_MS}ms`);
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });

        items.forEach((item) => observer.observe(item));
    }

    function initTicketForms() {
        document.querySelectorAll('[data-ticket-form]').forEach((form) => {
            const steppers = Array.from(form.querySelectorAll('[data-stepper]'));
            const totalOutput = form.querySelector('[data-total]');
            const submit = form.querySelector('[data-submit]');

            const refresh = () => {
                let total = 0;
                let quantity = 0;
                steppers.forEach((stepper) => {
                    const value = Number(stepper.querySelector('[data-input]').value);
                    total += value * Number(stepper.dataset.price);
                    quantity += value;
                    stepper.querySelector('[data-step="-1"]').disabled = value === 0;
                });
                totalOutput.textContent = formatMoney(total);
                submit.disabled = quantity === 0;
            };

            steppers.forEach((stepper) => {
                const input = stepper.querySelector('[data-input]');
                const display = stepper.querySelector('[data-value]');

                stepper.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-step]');
                    if (!button) {
                        return;
                    }
                    const next = Math.max(0, Number(input.value) + Number(button.dataset.step));
                    input.value = String(next);
                    display.textContent = String(next);
                    refresh();
                });
            });

            refresh();
        });
    }

    function initDateRanges() {
        const DAY_MS = 24 * 60 * 60 * 1000;

        document.querySelectorAll('[data-date-range]').forEach((form) => {
            const start = form.querySelector('[data-range-start]');
            const end = form.querySelector('[data-range-end]');

            start.addEventListener('change', () => {
                if (!start.value) {
                    return;
                }
                const minEnd = new Date(new Date(start.value).getTime() + DAY_MS).toISOString().slice(0, 10);
                end.min = minEnd;
                if (end.value && end.value < minEnd) {
                    end.value = minEnd;
                }
            });
        });
    }

    function initConfirmations() {
        document.querySelectorAll('form[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!window.confirm(form.dataset.confirm)) {
                    event.preventDefault();
                }
            });
        });
    }

    initImageFallbacks();
    initReveal();
    initTicketForms();
    initDateRanges();
    initConfirmations();
})();
