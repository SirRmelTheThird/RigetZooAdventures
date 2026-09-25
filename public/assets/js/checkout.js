/**
 * Stripe payment element for the checkout page.
 * Keys and the failure message arrive as data attributes on #payment-form,
 * so this file contains no server-rendered values.
 */
(() => {
    'use strict';

    const form = document.getElementById('payment-form');
    if (!form) {
        return;
    }

    const button = document.getElementById('payment-submit');
    const message = document.getElementById('payment-message');

    // Keep Stripe's fields visually in line with the site's design tokens.
    const appearance = {
        theme: 'stripe',
        variables: {
            colorPrimary: '#c4520c',
            colorText: '#231f18',
            colorDanger: '#9f2f22',
            fontFamily: 'Geist, "Segoe UI", system-ui, sans-serif',
            borderRadius: '12px',
            spacingUnit: '4px',
        },
    };

    const stripe = Stripe(form.dataset.stripeKey);
    const elements = stripe.elements({ clientSecret: form.dataset.clientSecret, appearance });
    elements.create('payment').mount('#payment-element');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        button.disabled = true;
        message.hidden = true;

        const { error } = await stripe.confirmPayment({
            elements,
            confirmParams: { return_url: window.location.href },
            redirect: 'if_required',
        });

        if (error) {
            message.textContent = form.dataset.failureMessage;
            if (error.message) {
                message.textContent = error.message;
            }
            message.hidden = false;
            button.disabled = false;
            return;
        }

        form.submit();
    });
})();
