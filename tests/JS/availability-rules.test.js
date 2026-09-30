import { describe, it, expect, vi } from 'vitest';
import {
    normalizeWindow,
    parseAvailableWindows,
    parseUnavailableRanges,
    isWithinWindows,
    isCheckInBlocked,
    isCheckOutBlocked,
    findUnavailableRangeContaining,
    rangeOverlapsUnavailable,
    isStayWithinWindows,
    isStayInvalid,
    findNextBlockedStart,
    findWindowEnd,
    earlierDate,
    computeMaxEndDate,
} from '../../public/assets/js/availability-rules.js';

// Minimal stand-ins for DOM elements — only getAttribute is used by the
// functions under test, so a plain object is enough; no jsdom needed.
function fakeElement(attributes) {
    return {
        getAttribute: (name) => (name in attributes ? attributes[name] : null),
    };
}

describe('normalizeWindow', () => {
    it('accepts the {start, end} shape', () => {
        expect(normalizeWindow({ start: '2026-01-01', end: '2026-01-10' }))
            .toEqual({ start_date: '2026-01-01', end_date: '2026-01-10' });
    });

    it('accepts the {start_date, end_date} shape', () => {
        expect(normalizeWindow({ start_date: '2026-01-01', end_date: '2026-01-10' }))
            .toEqual({ start_date: '2026-01-01', end_date: '2026-01-10' });
    });

    it('throws a named error when a date is missing', () => {
        expect(() => normalizeWindow({ start: '2026-01-01' })).toThrow(
            'Availability window is missing a start or end date.'
        );
    });

    it('throws a named error when start is after end', () => {
        expect(() => normalizeWindow({ start: '2026-01-10', end: '2026-01-01' })).toThrow(
            'Availability window start date is after its end date.'
        );
    });
});

describe('parseAvailableWindows', () => {
    it('returns null and logs when the attribute is missing', () => {
        const spy = vi.spyOn(console, 'error').mockImplementation(() => {});
        const form = fakeElement({});

        expect(parseAvailableWindows(form)).toBeNull();
        expect(spy).toHaveBeenCalled();
        spy.mockRestore();
    });

    it('returns null when the JSON is malformed', () => {
        const spy = vi.spyOn(console, 'error').mockImplementation(() => {});
        const form = fakeElement({ 'data-available-windows': 'not json' });

        expect(parseAvailableWindows(form)).toBeNull();
        spy.mockRestore();
    });

    it('returns null when the array is empty', () => {
        const spy = vi.spyOn(console, 'error').mockImplementation(() => {});
        const form = fakeElement({ 'data-available-windows': '[]' });

        expect(parseAvailableWindows(form)).toBeNull();
        spy.mockRestore();
    });

    it('skips invalid windows but keeps the valid ones', () => {
        const spy = vi.spyOn(console, 'error').mockImplementation(() => {});
        const form = fakeElement({
            'data-available-windows': JSON.stringify([
                { start: '2026-01-01', end: '2026-01-10' },
                { start: '2026-02-10', end: '2026-02-01' }, // invalid: reversed
            ]),
        });

        expect(parseAvailableWindows(form)).toEqual([
            { start_date: '2026-01-01', end_date: '2026-01-10' },
        ]);
        spy.mockRestore();
    });

    it('returns null if every window is invalid', () => {
        const spy = vi.spyOn(console, 'error').mockImplementation(() => {});
        const form = fakeElement({
            'data-available-windows': JSON.stringify([{ start: '2026-02-10', end: '2026-02-01' }]),
        });

        expect(parseAvailableWindows(form)).toBeNull();
        spy.mockRestore();
    });
});

describe('parseUnavailableRanges', () => {
    it('returns an empty array when the attribute is missing', () => {
        expect(parseUnavailableRanges(fakeElement({}))).toEqual([]);
    });

    it('returns an empty array on malformed JSON', () => {
        expect(parseUnavailableRanges(fakeElement({ 'data-unavailable': '{bad' }))).toEqual([]);
    });

    it('parses valid JSON', () => {
        const ranges = [{ start_date: '2026-01-05', end_date: '2026-01-08' }];
        expect(parseUnavailableRanges(fakeElement({ 'data-unavailable': JSON.stringify(ranges) }))).toEqual(ranges);
    });
});

const WINDOWS = [{ start_date: '2026-01-01', end_date: '2026-01-31' }];
const UNAVAILABLE = [{ start_date: '2026-01-10', end_date: '2026-01-15' }];

describe('isWithinWindows', () => {
    it('is true for a date inside the window', () => {
        expect(isWithinWindows('2026-01-15', WINDOWS)).toBe(true);
    });

    it('is false for a date outside every window', () => {
        expect(isWithinWindows('2026-02-01', WINDOWS)).toBe(false);
    });
});

describe('isCheckInBlocked / isCheckOutBlocked', () => {
    it('blocks check-in on the first day of an unavailable range', () => {
        expect(isCheckInBlocked('2026-01-10', WINDOWS, UNAVAILABLE)).toBe(true);
    });

    it('allows check-out on the first day of an unavailable range (it was the previous stay\'s last night)', () => {
        expect(isCheckOutBlocked('2026-01-10', WINDOWS, UNAVAILABLE)).toBe(false);
    });

    it('blocks check-out on the last day of an unavailable range', () => {
        expect(isCheckOutBlocked('2026-01-15', WINDOWS, UNAVAILABLE)).toBe(true);
    });

    it('allows check-in on the last day of an unavailable range (new stay starts as the old one ends)', () => {
        expect(isCheckInBlocked('2026-01-15', WINDOWS, UNAVAILABLE)).toBe(false);
    });

    it('blocks any date outside the available windows regardless of unavailable ranges', () => {
        expect(isCheckInBlocked('2026-02-01', WINDOWS, [])).toBe(true);
        expect(isCheckOutBlocked('2026-02-01', WINDOWS, [])).toBe(true);
    });
});

describe('findUnavailableRangeContaining', () => {
    it('finds the range containing a date', () => {
        expect(findUnavailableRangeContaining('2026-01-12', UNAVAILABLE)).toEqual(UNAVAILABLE[0]);
    });

    it('returns null when no range contains the date', () => {
        expect(findUnavailableRangeContaining('2026-01-20', UNAVAILABLE)).toBeNull();
    });
});

describe('rangeOverlapsUnavailable', () => {
    it('detects overlap', () => {
        expect(rangeOverlapsUnavailable('2026-01-08', '2026-01-12', UNAVAILABLE)).toBe(true);
    });

    it('detects no overlap for an adjacent, non-overlapping stay', () => {
        expect(rangeOverlapsUnavailable('2026-01-01', '2026-01-10', UNAVAILABLE)).toBe(false);
    });
});

describe('isStayWithinWindows / isStayInvalid', () => {
    it('accepts a stay fully inside one window with no overlap', () => {
        expect(isStayWithinWindows('2026-01-02', '2026-01-05', WINDOWS)).toBe(true);
        expect(isStayInvalid('2026-01-02', '2026-01-05', WINDOWS, UNAVAILABLE)).toBe(false);
    });

    it('rejects a stay that spans outside the window', () => {
        expect(isStayWithinWindows('2026-01-28', '2026-02-05', WINDOWS)).toBe(false);
    });

    it('rejects a stay that overlaps an unavailable range even if inside the window', () => {
        expect(isStayInvalid('2026-01-08', '2026-01-12', WINDOWS, UNAVAILABLE)).toBe(true);
    });
});

describe('findNextBlockedStart', () => {
    it('finds the next unavailable start on or after the given date', () => {
        expect(findNextBlockedStart('2026-01-01', UNAVAILABLE)).toBe('2026-01-10');
    });

    it('returns null when nothing is on or after the given date', () => {
        expect(findNextBlockedStart('2026-01-20', UNAVAILABLE)).toBeNull();
    });
});

describe('findWindowEnd', () => {
    it('returns the end date of the window containing the start date', () => {
        expect(findWindowEnd('2026-01-05', WINDOWS)).toBe('2026-01-31');
    });

    it('returns null when the date is in no window', () => {
        expect(findWindowEnd('2026-02-05', WINDOWS)).toBeNull();
    });
});

describe('earlierDate', () => {
    it('returns the other date when one side is null', () => {
        expect(earlierDate(null, '2026-01-05')).toBe('2026-01-05');
        expect(earlierDate('2026-01-05', null)).toBe('2026-01-05');
    });

    it('returns the earlier of two dates', () => {
        expect(earlierDate('2026-01-05', '2026-01-10')).toBe('2026-01-05');
        expect(earlierDate('2026-01-10', '2026-01-05')).toBe('2026-01-05');
    });
});

describe('computeMaxEndDate', () => {
    it('caps at the window end when nothing else blocks it', () => {
        expect(computeMaxEndDate('2026-01-20', WINDOWS, [], null)).toBe('2026-01-31');
    });

    it('caps at the next unavailable start date if it comes before the window end', () => {
        expect(computeMaxEndDate('2026-01-05', WINDOWS, UNAVAILABLE, null)).toBe('2026-01-10');
    });

    it('caps at the hard max if it is the earliest bound', () => {
        expect(computeMaxEndDate('2026-01-05', WINDOWS, [], '2026-01-08')).toBe('2026-01-08');
    });
});
