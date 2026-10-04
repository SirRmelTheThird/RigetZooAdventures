import { SELECTOR, CSS_CLASS, DEFAULT_MESSAGE, DATE_FORMAT, DEFAULT_START_MIN } from './constants.js';
import { formatDateObj, addDays } from './utils.js';
import * as AvailabilityRules from './availability-rules.js';

function decorateUnavailableDay(dayElem, windows, unavailable) {
    const dateStr = formatDateObj(dayElem.dateObj);

    if (!AvailabilityRules.isWithinWindows(dateStr, windows)) {
        dayElem.classList.add(CSS_CLASS.DAY_UNAVAILABLE);
        dayElem.title = DEFAULT_MESSAGE.OUTSIDE_WINDOW_TITLE;
        return;
    }

    const info = AvailabilityRules.findUnavailableRangeContaining(dateStr, unavailable);
    if (!info) {
        return;
    }

    dayElem.classList.add(CSS_CLASS.DAY_UNAVAILABLE);

    if (info.reason) {
        dayElem.title = `Unavailable: ${info.reason}`;
        return;
    }
    dayElem.title = DEFAULT_MESSAGE.UNAVAILABLE_TITLE;
}

function deselectAndWarn(input, message) {
    input.setCustomValidity(message);
    input.reportValidity();
    input.value = '';
    input.setCustomValidity('');
}

function setupFlatpickrRange(context) {
    const { start, end, windows, unavailable, messages, initialEndMax } = context;
    let endPicker = null;

    const startPicker = window.flatpickr(start, {
        dateFormat: DATE_FORMAT,
        position: 'auto center',
        minDate: start.getAttribute('min') || DEFAULT_START_MIN,
        maxDate: start.getAttribute('max') || undefined,
        disable: [(date) => AvailabilityRules.isCheckInBlocked(formatDateObj(date), windows, unavailable)],
        onDayCreate: (dObj, dStr, fp, dayElem) => decorateUnavailableDay(dayElem, windows, unavailable),
        onChange: (selectedDates, dateStr) => {
            if (dateStr && selectedDates.length > 0) {
                if (!endPicker) {
                    return;
                }

                const startDate = selectedDates[0];
                const minEnd = addDays(startDate, 1);
                const maxEnd = AvailabilityRules.computeMaxEndDate(dateStr, windows, unavailable, initialEndMax);

                endPicker.clear();
                endPicker.set('minDate', minEnd);
                endPicker.set('maxDate', maxEnd || undefined);
                endPicker.open();
                return;
            }

            if (!endPicker) {
                return;
            }
            endPicker.clear();
            endPicker.set('minDate', end.getAttribute('min') || addDays(new Date(), 1));
            endPicker.set('maxDate', initialEndMax || undefined);
        },
    });

    endPicker = window.flatpickr(end, {
        dateFormat: DATE_FORMAT,
        position: 'auto center',
        minDate: end.getAttribute('min') || addDays(new Date(), 1),
        maxDate: end.getAttribute('max') || undefined,
        disable: [(date) => AvailabilityRules.isCheckOutBlocked(formatDateObj(date), windows, unavailable)],
        onDayCreate: (dObj, dStr, fp, dayElem) => decorateUnavailableDay(dayElem, windows, unavailable),
        onChange: (selectedDates, dateStr) => {
            if (!dateStr || selectedDates.length === 0) {
                return;
            }
            if (startPicker.selectedDates.length === 0) {
                return;
            }

            const startStr = formatDateObj(startPicker.selectedDates[0]);
            if (!AvailabilityRules.isStayInvalid(startStr, dateStr, windows, unavailable)) {
                return;
            }

            endPicker.clear();
            deselectAndWarn(end, messages.rangeUnavailable);
        },
    });
}

function setupNativeDateRange(context) {
    const { start, end, windows, unavailable, messages, initialEndMax } = context;

    start.addEventListener('change', () => {
        if (!start.value) {
            end.removeAttribute('min');
            if (initialEndMax) {
                end.max = initialEndMax;
            } else {
                end.removeAttribute('max');
            }
            return;
        }

        if (AvailabilityRules.isCheckInBlocked(start.value, windows, unavailable)) {
            deselectAndWarn(start, messages.dateUnavailable);
            return;
        }

        const startDate = new Date(`${start.value}T00:00:00`);
        const minEnd = formatDateObj(addDays(startDate, 1));
        end.min = minEnd;

        const maxEnd = AvailabilityRules.computeMaxEndDate(start.value, windows, unavailable, initialEndMax);
        if (maxEnd) {
            end.max = maxEnd;
        } else {
            end.removeAttribute('max');
        }

        if (!end.value) {
            return;
        }
        if (end.value < minEnd || AvailabilityRules.isStayInvalid(start.value, end.value, windows, unavailable)) {
            deselectAndWarn(end, messages.rangeUnavailable);
        }
    });

    end.addEventListener('change', () => {
        if (!end.value) {
            return;
        }

        if (AvailabilityRules.isCheckOutBlocked(end.value, windows, unavailable)) {
            deselectAndWarn(end, messages.dateUnavailable);
            return;
        }

        if (!start.value) {
            return;
        }

        if (AvailabilityRules.isStayInvalid(start.value, end.value, windows, unavailable)) {
            deselectAndWarn(end, messages.rangeUnavailable);
        }
    });
}

function syncTicketCalendarSize(input, instance) {
    instance.calendarContainer.classList.add('rz-ticket-calendar');
    instance.calendarContainer.style.width = `${input.getBoundingClientRect().width}px`;
}

function setupDateRangeForm(form, hasFlatpickr) {
    const start = form.querySelector(SELECTOR.RANGE_START);
    const end = form.querySelector(SELECTOR.RANGE_END);
    if (!start || !end) {
        return;
    }

    const unavailable = AvailabilityRules.parseUnavailableRanges(form);   // ← was: (start)
    const windows = AvailabilityRules.parseAvailableWindows(form);
    if (windows === null) {
        return;
    }

    const messages = {
        dateUnavailable: form.dataset.msgDateUnavailable || DEFAULT_MESSAGE.DATE_UNAVAILABLE,
        rangeUnavailable: form.dataset.msgRangeUnavailable || DEFAULT_MESSAGE.RANGE_UNAVAILABLE,
    };

    const initialEndMax = end.getAttribute('max') || '';
    const context = { start, end, windows, unavailable, messages, initialEndMax };

    if (hasFlatpickr) {
        setupFlatpickrRange(context);
    } else {
        setupNativeDateRange(context);
    }

    form.addEventListener('submit', (event) => {
        if (!start.value || !end.value) {
            return;
        }
        if (AvailabilityRules.isStayInvalid(start.value, end.value, windows, unavailable)) {
            event.preventDefault();
            deselectAndWarn(end, messages.rangeUnavailable);
        }
    });
}

function initPlainDatePickers() {
    document.querySelectorAll(SELECTOR.PLAIN_DATE_INPUT).forEach((input) => {
        window.flatpickr(input, {
            dateFormat: DATE_FORMAT,
            position: 'below center',
            minDate: input.getAttribute('min') || DEFAULT_START_MIN,
            maxDate: input.getAttribute('max') || undefined,
            onReady: (...args) => syncTicketCalendarSize(input, args[2]),
            onOpen: (...args) => syncTicketCalendarSize(input, args[2]),
        });
    });
}

export function initDateRanges() {
    const hasFlatpickr = typeof window.flatpickr === 'function';

    if (!hasFlatpickr) {
        console.error('Flatpickr is not loaded. Check that flatpickr.min.js loads before your application script.');
    }

    document.querySelectorAll(SELECTOR.DATE_RANGE_FORM).forEach((form) => {
        setupDateRangeForm(form, hasFlatpickr);
    });

    if (hasFlatpickr) {
        initPlainDatePickers();
    }
}
