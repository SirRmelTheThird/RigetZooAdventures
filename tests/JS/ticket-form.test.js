import { describe, it, expect } from 'vitest';
import { calculateTicketTotals } from '../../public/assets/js/ticket-form.js';

describe('calculateTicketTotals', () => {
    it('returns zero total and quantity for no items', () => {
        expect(calculateTicketTotals([])).toEqual({ total: 0, quantity: 0 });
    });

    it('sums price times quantity across items', () => {
        const items = [
            { quantity: 2, price: 10 },
            { quantity: 1, price: 25 },
        ];

        expect(calculateTicketTotals(items)).toEqual({ total: 45, quantity: 3 });
    });

    it('ignores items with zero quantity', () => {
        const items = [
            { quantity: 0, price: 50 },
            { quantity: 2, price: 5 },
        ];

        expect(calculateTicketTotals(items)).toEqual({ total: 10, quantity: 2 });
    });
});
