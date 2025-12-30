/**
 * Format a price value with ISO 4217 currency code
 * 
 * @param price - The numeric price value to format
 * @param currency - The ISO 4217 currency code (e.g., 'IDR', 'USD', 'EUR')
 * @returns Formatted currency string with ISO 4217 code
 * 
 * @example
 * formatCurrency(1100000, 'IDR') // "IDR 1,100,000"
 * formatCurrency(99.99, 'USD') // "USD 100"
 */
export function formatCurrency(price: number, currency: string): string {
    const code = currency || 'IDR';
    const formatted = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(price);
    return `${code} ${formatted}`;
}

/**
 * Format a number with thousand separators (International format: 1,000,000)
 * 
 * @param value - The numeric value to format
 * @returns Formatted number string with thousand separators
 * 
 * @example
 * formatNumber(1100000) // "1,100,000"
 * formatNumber(99) // "99"
 */
export function formatNumber(value: number): string {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

/**
 * Currency symbol mapping
 */
const currencySymbols: Record<string, string> = {
    USD: '$',
    EUR: '€',
    GBP: '£',
    JPY: '¥',
    CNY: '¥',
    IDR: 'Rp',
    SGD: 'S$',
    MYR: 'RM',
    THB: '฿',
    AUD: 'A$',
    KRW: '₩',
    INR: '₹',
};

/**
 * Format currency code with symbol, e.g., "USD ($)", "IDR (Rp)"
 * 
 * @param currency - The currency code (e.g., 'IDR', 'USD')
 * @returns Formatted string like "USD ($)"
 * 
 * @example
 * formatCurrencyLabel('USD') // "USD ($)"
 * formatCurrencyLabel('IDR') // "IDR (Rp)"
 */
export function formatCurrencyLabel(currency: string): string {
    const symbol = currencySymbols[currency] || currency;
    return `${currency} (${symbol})`;
}
