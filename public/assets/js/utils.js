import { CURRENCY_SYMBOL, MONEY_DECIMALS, DAY_MS } from './constants.js';

export function formatMoney(amount) {
    return CURRENCY_SYMBOL + amount.toFixed(MONEY_DECIMALS);
}

export function formatDateObj(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

export function addDays(date, days) {
    return new Date(date.getTime() + days * DAY_MS);
}
