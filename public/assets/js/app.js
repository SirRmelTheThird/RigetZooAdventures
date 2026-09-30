
(() => {
    'use strict';

    const REVEAL_STAGGER_MS = 70;
    const REVEAL_MAX_STEPS = 4;
    const CURRENCY_SYMBOL = '£';
    const MONEY_DECIMALS = 2;
    const FLASH_DURATION_MS = 3500;

    const formatMoney = (amount) => CURRENCY_SYMBOL + amount.toFixed(MONEY_DECIMALS);

    function initFlashMessages() {
        document.querySelectorAll('[data-flash-message]').forEach((message) => {
            setTimeout(() => {
                message.remove();
            }, FLASH_DURATION_MS);
        });
    }

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
        const hasFlatpickr = typeof window.flatpickr === 'function';

        if (!hasFlatpickr) {
          console.error(
              'Flatpickr is not loaded. Check that flatpickr.min.js loads before your application script.'
          );
        }

        const formatDateObj = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        };

        function parseUnavailable(input) {
            const raw = input.getAttribute('data-unavailable');

            if (!raw) {
                return [];
            }

            try {
                return JSON.parse(raw);
            } catch {
                return [];
            }
        }

      function parseAvailableWindows(form) {
          const raw = form.getAttribute('data-available-windows');

          if (!raw) {
              console.error('Missing data-available-windows:', form);
              return null;
          }

          try {
              const windows = JSON.parse(raw);

              if (!Array.isArray(windows) || windows.length === 0) {
                  console.error('No availability windows found:', windows);
                  return null;
              }

              const parsedWindows = windows
                  .map((window) => ({
                      start_date: window.start ?? window.start_date,
                      end_date: window.end ?? window.end_date
                  }))
                  .filter((window) =>
                      window.start_date &&
                      window.end_date &&
                      window.start_date <= window.end_date
                  );

              if (parsedWindows.length === 0) {
                  console.error('Invalid availability windows:', windows);
                  return null;
              }

              return parsedWindows;

          } catch (error) {
              console.error('Failed to parse availability windows:', error);
              return null;
          }
      }

        function isOutsideAvailableWindows(dateStr, windows) {
            return !windows.some((window) =>
                dateStr >= window.start_date &&
                dateStr <= window.end_date
            );
        }

        function isUnavailableForCheckIn(dateStr, ranges) {
            return ranges.some((range) =>
                dateStr >= range.start_date &&
                dateStr < range.end_date
            );
        }

        function isUnavailableForCheckOut(dateStr, ranges) {
            return ranges.some((range) =>
                dateStr > range.start_date &&
                dateStr <= range.end_date
            );
        }

        function findUnavailableInfo(dateStr, ranges) {
            return ranges.find((range) =>
                dateStr >= range.start_date &&
                dateStr < range.end_date
            ) || null;
        }

        function rangeOverlapsUnavailable(startStr, endStr, ranges) {
            return ranges.some((range) =>
                startStr < range.end_date &&
                endStr > range.start_date
            );
        }

        function isStayOutsideAvailableWindows(
            startStr,
            endStr,
            windows
        ) {
            return !windows.some((window) =>
                startStr >= window.start_date &&
                startStr < window.end_date &&
                endStr > startStr &&
                endStr <= window.end_date
            );
        }

        function findNextUnavailableStart(startStr, ranges) {
            let nextStart = null;

            ranges.forEach((range) => {
                if (
                    range.start_date >= startStr &&
                    (
                        nextStart === null ||
                        range.start_date < nextStart
                    )
                ) {
                    nextStart = range.start_date;
                }
            });

            return nextStart;
        }

        function findAvailableWindowEnd(startStr, windows) {
            const window = windows.find((item) =>
                startStr >= item.start_date &&
                startStr < item.end_date
            );

            return window ? window.end_date : null;
        }

        function deselectAndWarn(input, message) {
            input.setCustomValidity(message);
            input.reportValidity();
            input.value = '';
            input.setCustomValidity('');
        }

        document.querySelectorAll('[data-date-range]').forEach((form) => {
            const start = form.querySelector('[data-range-start]');
            const end = form.querySelector('[data-range-end]');

            if (!start || !end) {
                return;
            }

            const unavailable = parseUnavailable(start);
            const availableWindows = parseAvailableWindows(form);

            if (availableWindows === null) {
                return;
            }

            const msgDateUnavailable =
                form.dataset.msgDateUnavailable ||
                'Date is unavailable.';

            const msgRangeUnavailable =
                form.dataset.msgRangeUnavailable ||
                'Selected date range is unavailable.';

            const initialEndMax = end.getAttribute('max') || '';

            const isBlockedCheckIn = (dateStr) =>
                isOutsideAvailableWindows(dateStr, availableWindows) ||
                isUnavailableForCheckIn(dateStr, unavailable);

            const isBlockedCheckOut = (dateStr) =>
                isOutsideAvailableWindows(dateStr, availableWindows) ||
                isUnavailableForCheckOut(dateStr, unavailable);

            const decorateUnavailableDay = (dayElem) => {
                const dateStr = formatDateObj(dayElem.dateObj);

                const info = findUnavailableInfo(
                    dateStr,
                    unavailable
                );

                if (isOutsideAvailableWindows(dateStr, availableWindows)) {
                    dayElem.classList.add('rz-day--unavailable');
                    dayElem.title = 'Outside available booking dates.';
                } else if (info) {
                    dayElem.classList.add('rz-day--unavailable');

                    dayElem.title = info.reason
                        ? `Unavailable: ${info.reason}`
                        : 'Unavailable';
                }
            };

            if (hasFlatpickr) {
                let endPicker = null;

                const startPicker = window.flatpickr(start, {
                    dateFormat: 'Y-m-d',

                    minDate: start.getAttribute('min') || 'today',

                    maxDate: start.getAttribute('max') || undefined,

                    disable: [
                        (date) => isBlockedCheckIn(
                            formatDateObj(date)
                        )
                    ],

                    onDayCreate: (dObj, dStr, fp, dayElem) => {
                        decorateUnavailableDay(dayElem);
                    },

                    onChange: (selectedDates, dateStr) => {
                        if (!dateStr || selectedDates.length === 0) {
                            if (endPicker) {
                                endPicker.clear();

                                endPicker.set(
                                    'minDate',
                                    end.getAttribute('min') ||
                                        new Date(Date.now() + DAY_MS)
                                );

                                endPicker.set(
                                    'maxDate',
                                    initialEndMax || undefined
                                );
                            }

                            return;
                        }

                        const startDate = selectedDates[0];

                        const minEnd = new Date(
                            startDate.getTime() + DAY_MS
                        );

                        if (!endPicker) {
                            return;
                        }

                        endPicker.clear();

                        endPicker.set('minDate', minEnd);

                        const windowEnd = findAvailableWindowEnd(
                            dateStr,
                            availableWindows
                        );

                        const nextBlocked = findNextUnavailableStart(
                            dateStr,
                            unavailable
                        );

                        let maxEnd = windowEnd;

                        if (
                            nextBlocked !== null &&
                            (
                                maxEnd === null ||
                                nextBlocked < maxEnd
                            )
                        ) {
                            maxEnd = nextBlocked;
                        }

                        if (initialEndMax) {
                            maxEnd = maxEnd === null
                                ? initialEndMax
                                : (
                                    initialEndMax < maxEnd
                                        ? initialEndMax
                                        : maxEnd
                                );
                        }

                        endPicker.set(
                            'maxDate',
                            maxEnd || undefined
                        );

                        endPicker.open();
                    }
                });

                endPicker = window.flatpickr(end, {
                    dateFormat: 'Y-m-d',

                    minDate: end.getAttribute('min') ||
                        new Date(Date.now() + DAY_MS),

                    maxDate: end.getAttribute('max') || undefined,

                    disable: [
                        (date) => isBlockedCheckOut(
                            formatDateObj(date)
                        )
                    ],

                    onDayCreate: (dObj, dStr, fp, dayElem) => {
                        decorateUnavailableDay(dayElem);
                    },

                    onChange: (selectedDates, dateStr) => {
                        if (
                            !dateStr ||
                            selectedDates.length === 0 ||
                            startPicker.selectedDates.length === 0
                        ) {
                            return;
                        }

                        const startStr = formatDateObj(
                            startPicker.selectedDates[0]
                        );

                        const invalidWindow =
                            isStayOutsideAvailableWindows(
                                startStr,
                                dateStr,
                                availableWindows
                            );

                        const invalidReservation =
                            rangeOverlapsUnavailable(
                                startStr,
                                dateStr,
                                unavailable
                            );

                        if (invalidWindow || invalidReservation) {
                            endPicker.clear();

                            deselectAndWarn(
                                end,
                                msgRangeUnavailable
                            );
                        }
                    }
                });
            } else {
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

                    if (isBlockedCheckIn(start.value)) {
                        deselectAndWarn(
                            start,
                            msgDateUnavailable
                        );

                        return;
                    }

                    const startDate = new Date(
                        `${start.value}T00:00:00`
                    );

                    const minEnd = formatDateObj(
                        new Date(startDate.getTime() + DAY_MS)
                    );

                    end.min = minEnd;

                    const windowEnd = findAvailableWindowEnd(
                        start.value,
                        availableWindows
                    );

                    const nextBlocked = findNextUnavailableStart(
                        start.value,
                        unavailable
                    );

                    let maxEnd = windowEnd;

                    if (
                        nextBlocked !== null &&
                        (
                            maxEnd === null ||
                            nextBlocked < maxEnd
                        )
                    ) {
                        maxEnd = nextBlocked;
                    }

                    if (initialEndMax) {
                        maxEnd = maxEnd === null
                            ? initialEndMax
                            : (
                                initialEndMax < maxEnd
                                    ? initialEndMax
                                    : maxEnd
                            );
                    }

                    if (maxEnd) {
                        end.max = maxEnd;
                    } else {
                        end.removeAttribute('max');
                    }

                    if (end.value) {
                        const invalidWindow =
                            isStayOutsideAvailableWindows(
                                start.value,
                                end.value,
                                availableWindows
                            );

                        const invalidReservation =
                            rangeOverlapsUnavailable(
                                start.value,
                                end.value,
                                unavailable
                            );

                        if (
                            end.value < minEnd ||
                            invalidWindow ||
                            invalidReservation
                        ) {
                            deselectAndWarn(
                                end,
                                msgRangeUnavailable
                            );
                        }
                    }
                });

                end.addEventListener('change', () => {
                    if (!end.value) {
                        return;
                    }

                    if (isBlockedCheckOut(end.value)) {
                        deselectAndWarn(
                            end,
                            msgDateUnavailable
                        );

                        return;
                    }

                    if (!start.value) {
                        return;
                    }

                    const invalidWindow =
                        isStayOutsideAvailableWindows(
                            start.value,
                            end.value,
                            availableWindows
                        );

                    const invalidReservation =
                        rangeOverlapsUnavailable(
                            start.value,
                            end.value,
                            unavailable
                        );

                    if (invalidWindow || invalidReservation) {
                        deselectAndWarn(
                            end,
                            msgRangeUnavailable
                        );
                    }
                });
            }

            form.addEventListener('submit', (event) => {
                if (!start.value || !end.value) {
                    return;
                }

                const invalidWindow =
                    isStayOutsideAvailableWindows(
                        start.value,
                        end.value,
                        availableWindows
                    );

                const invalidReservation =
                    rangeOverlapsUnavailable(
                        start.value,
                        end.value,
                        unavailable
                    );

                if (invalidWindow || invalidReservation) {
                    event.preventDefault();

                    deselectAndWarn(
                        end,
                        msgRangeUnavailable
                    );
                }
            });
        });

        if (hasFlatpickr) {
            document.querySelectorAll(
                'input[type="date"]:not([data-range-start]):not([data-range-end])'
            ).forEach((input) => {
                window.flatpickr(input, {
                    dateFormat: 'Y-m-d',
                    minDate: input.getAttribute('min') || 'today',
                    maxDate: input.getAttribute('max') || undefined
                });
            });
        }
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
    initFlashMessages();
})();
