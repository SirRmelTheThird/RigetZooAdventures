// Pure functions only — no DOM access. Everything here takes plain data
// in (strings, arrays of {start_date, end_date}) and returns plain data
// out, so it can be unit tested without a browser or jsdom.

export function parseUnavailableRanges(input) {
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

export function normalizeWindow(rawWindow) {
    let startDate = rawWindow.start;
    if (!startDate) {
        startDate = rawWindow.start_date;
    }

    let endDate = rawWindow.end;
    if (!endDate) {
        endDate = rawWindow.end_date;
    }

    if (!startDate || !endDate) {
        throw new Error('Availability window is missing a start or end date.');
    }
    if (startDate > endDate) {
        throw new Error('Availability window start date is after its end date.');
    }

    return { start_date: startDate, end_date: endDate };
}

export function parseAvailableWindows(form) {
    const raw = form.getAttribute('data-available-windows');
    if (!raw) {
        console.error('Missing data-available-windows:', form);
        return null;
    }

    let rawWindows;
    try {
        rawWindows = JSON.parse(raw);
    } catch (error) {
        console.error('Failed to parse availability windows:', error);
        return null;
    }

    if (!Array.isArray(rawWindows) || rawWindows.length === 0) {
        console.error('No availability windows found:', rawWindows);
        return null;
    }

    const windows = [];
    for (const rawWindow of rawWindows) {
        try {
            windows.push(normalizeWindow(rawWindow));
        } catch (error) {
            console.error('Skipping invalid availability window:', rawWindow, error);
        }
    }

    if (windows.length === 0) {
        console.error('No valid availability windows found:', rawWindows);
        return null;
    }

    return windows;
}

export function isWithinWindows(dateStr, windows) {
    return windows.some((window) => dateStr >= window.start_date && dateStr <= window.end_date);
}

export function isCheckInBlocked(dateStr, windows, unavailable) {
    if (!isWithinWindows(dateStr, windows)) {
        return true;
    }
    return unavailable.some((range) => dateStr >= range.start_date && dateStr < range.end_date);
}

export function isCheckOutBlocked(dateStr, windows, unavailable) {
    if (!isWithinWindows(dateStr, windows)) {
        return true;
    }
    return unavailable.some((range) => dateStr > range.start_date && dateStr <= range.end_date);
}

export function findUnavailableRangeContaining(dateStr, unavailable) {
    return unavailable.find((range) => dateStr >= range.start_date && dateStr < range.end_date) || null;
}

export function rangeOverlapsUnavailable(startStr, endStr, unavailable) {
    return unavailable.some((range) => startStr < range.end_date && endStr > range.start_date);
}

export function isStayWithinWindows(startStr, endStr, windows) {
    return windows.some((window) =>
        startStr >= window.start_date &&
        startStr < window.end_date &&
        endStr > startStr &&
        endStr <= window.end_date
    );
}

export function isStayInvalid(startStr, endStr, windows, unavailable) {
    if (!isStayWithinWindows(startStr, endStr, windows)) {
        return true;
    }
    return rangeOverlapsUnavailable(startStr, endStr, unavailable);
}

export function findNextBlockedStart(startStr, unavailable) {
    let nextStart = null;

    unavailable.forEach((range) => {
        if (range.start_date < startStr) {
            return;
        }
        if (nextStart === null || range.start_date < nextStart) {
            nextStart = range.start_date;
        }
    });

    return nextStart;
}

export function findWindowEnd(startStr, windows) {
    const window = windows.find((item) => startStr >= item.start_date && startStr < item.end_date);
    if (!window) {
        return null;
    }
    return window.end_date;
}

export function earlierDate(dateA, dateB) {
    if (dateA === null) {
        return dateB;
    }
    if (dateB === null) {
        return dateA;
    }
    if (dateA < dateB) {
        return dateA;
    }
    return dateB;
}

export function computeMaxEndDate(startStr, windows, unavailable, hardMax) {
    let maxEnd = findWindowEnd(startStr, windows);

    const nextBlocked = findNextBlockedStart(startStr, unavailable);
    if (nextBlocked !== null) {
        maxEnd = earlierDate(maxEnd, nextBlocked);
    }

    if (hardMax) {
        maxEnd = earlierDate(maxEnd, hardMax);
    }

    return maxEnd;
}
