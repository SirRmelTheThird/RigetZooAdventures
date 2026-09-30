import { SELECTOR } from './constants.js';
import { formatMoney } from './utils.js';

// Pure — takes plain {quantity, price} items, returns totals. Kept apart
// from the DOM reads below so the math can be unit tested directly.
export function calculateTicketTotals(items) {
    let total = 0;
    let quantity = 0;

    items.forEach((item) => {
        total += item.quantity * item.price;
        quantity += item.quantity;
    });

    return { total, quantity };
}

function readStepperState(stepper) {
    const input = stepper.querySelector(SELECTOR.STEPPER_INPUT);
    const quantity = Number(input.value);
    const price = Number(stepper.dataset.price);
    return { quantity, price };
}

function refreshTicketForm(form, steppers) {
    const totalOutput = form.querySelector(SELECTOR.TOTAL_OUTPUT);
    const submit = form.querySelector(SELECTOR.SUBMIT_BUTTON);

    const items = steppers.map((stepper) => readStepperState(stepper));
    const { total, quantity } = calculateTicketTotals(items);

    steppers.forEach((stepper) => {
        const state = readStepperState(stepper);
        const decrementButton = stepper.querySelector(SELECTOR.STEPPER_DECREMENT);
        decrementButton.disabled = state.quantity === 0;
    });

    totalOutput.textContent = formatMoney(total);
    submit.disabled = quantity === 0;
}

function handleStepperClick(event, stepper, form, steppers) {
    const button = event.target.closest(SELECTOR.STEPPER_BUTTON);
    if (!button) {
        return;
    }

    const input = stepper.querySelector(SELECTOR.STEPPER_INPUT);
    const display = stepper.querySelector(SELECTOR.STEPPER_VALUE);
    const nextValue = Math.max(0, Number(input.value) + Number(button.dataset.step));

    input.value = String(nextValue);
    display.textContent = String(nextValue);

    refreshTicketForm(form, steppers);
}

export function initTicketForms() {
    document.querySelectorAll(SELECTOR.TICKET_FORM).forEach((form) => {
        const steppers = Array.from(form.querySelectorAll(SELECTOR.STEPPER));

        steppers.forEach((stepper) => {
            stepper.addEventListener('click', (event) => {
                handleStepperClick(event, stepper, form, steppers);
            });
        });

        refreshTicketForm(form, steppers);
    });
}
