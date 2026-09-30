import { describe, it, expect } from 'vitest';
import { formatMoney, formatDateObj, addDays } from '../../public//assets/js/utils.js';

describe('formatMoney', () => {
    it('formats with the currency symbol and two decimals', () => {
        expect(formatMoney(12)).toBe('£12.00');
        expect(formatMoney(4.5)).toBe('£4.50');
        expect(formatMoney(0)).toBe('£0.00');
    });
});

describe('formatDateObj', () => {
    it('formats a Date as YYYY-MM-DD, zero-padded', () => {
        expect(formatDateObj(new Date(2026, 0, 5))).toBe('2026-01-05');
        expect(formatDateObj(new Date(2026, 10, 30))).toBe('2026-11-30');
    });
});

describe('addDays', () => {
    it('adds whole days without mutating the input', () => {
        const start = new Date(2026, 0, 1);
        const result = addDays(start, 5);

        expect(formatDateObj(result)).toBe('2026-01-06');
        expect(formatDateObj(start)).toBe('2026-01-01');
    });

    it('rolls over a month boundary correctly', () => {
        expect(formatDateObj(addDays(new Date(2026, 0, 30), 3))).toBe('2026-02-02');
    });
});
