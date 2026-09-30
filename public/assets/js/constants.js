export const REVEAL_STAGGER_MS = 70;
export const REVEAL_MAX_STEPS = 4;
export const REVEAL_THRESHOLD = 0.12;
export const REVEAL_ROOT_MARGIN = '0px 0px -6% 0px';

export const CURRENCY_SYMBOL = '£';
export const MONEY_DECIMALS = 2;

export const FLASH_DURATION_MS = 3500;

export const DAY_MS = 24 * 60 * 60 * 1000;
export const DATE_FORMAT = 'Y-m-d';
export const DEFAULT_START_MIN = 'today';

export const CSS_CLASS = {
    VISIBLE: 'is-visible',
    DAY_UNAVAILABLE: 'rz-day--unavailable',
};

export const SELECTOR = {
    FLASH_MESSAGE: '[data-flash-message]',
    HIDE_ON_ERROR_IMAGE: 'img[data-hide-on-error]',
    REVEAL_ITEM: '.rz-reveal',
    TICKET_FORM: '[data-ticket-form]',
    STEPPER: '[data-stepper]',
    STEPPER_INPUT: '[data-input]',
    STEPPER_VALUE: '[data-value]',
    STEPPER_BUTTON: '[data-step]',
    STEPPER_DECREMENT: '[data-step="-1"]',
    TOTAL_OUTPUT: '[data-total]',
    SUBMIT_BUTTON: '[data-submit]',
    DATE_RANGE_FORM: '[data-date-range]',
    RANGE_START: '[data-range-start]',
    RANGE_END: '[data-range-end]',
    CONFIRM_FORM: 'form[data-confirm]',
    PLAIN_DATE_INPUT: 'input[type="date"]:not([data-range-start]):not([data-range-end])',
};

export const DEFAULT_MESSAGE = {
    DATE_UNAVAILABLE: 'Date is unavailable.',
    RANGE_UNAVAILABLE: 'Selected date range is unavailable.',
    OUTSIDE_WINDOW_TITLE: 'Outside available booking dates.',
    UNAVAILABLE_TITLE: 'Unavailable',
};
