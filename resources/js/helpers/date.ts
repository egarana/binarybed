import dayjs from 'dayjs';

/**
 * Format a date string to a readable format
 * 
 * @param dateString - The date string to format (e.g., '2025-01-15')
 * @param format - The dayjs format string (default: 'MMM DD, YYYY')
 * @returns Formatted date string
 * 
 * @example
 * formatDate('2025-01-15') // "Jan 15, 2025"
 * formatDate('2025-01-15', 'DD/MM/YYYY') // "15/01/2025"
 */
export function formatDate(dateString: string | null | undefined, format: string = 'MMM DD, YYYY'): string {
    if (!dateString) return '-';
    return dayjs(dateString).format(format);
}

/**
 * Format a date range to a readable format
 * 
 * @param startDate - The start date string
 * @param endDate - The end date string
 * @param format - The dayjs format string (default: 'MMM DD, YYYY')
 * @returns Formatted date range string (e.g., "Jan 15, 2025 - Jan 18, 2025")
 * 
 * @example
 * formatDateRange('2025-01-15', '2025-01-18') // "Jan 15, 2025 - Jan 18, 2025"
 * formatDateRange('2025-01-15', null) // "Jan 15, 2025 - -"
 */
export function formatDateRange(
    startDate: string | null | undefined,
    endDate: string | null | undefined,
    format: string = 'MMM DD, YYYY'
): string {
    const start = formatDate(startDate, format);
    const end = formatDate(endDate, format);
    return `${start} - ${end}`;
}

/**
 * Format a date range for schedule display (compact format)
 * 
 * @param startDate - The start date string
 * @param endDate - The end date string
 * @returns Formatted schedule string (e.g., "Dec 25 - Dec 27")
 * 
 * @example
 * formatSchedule('2025-12-25', '2025-12-27') // "Dec 25 - Dec 27"
 */
export function formatSchedule(
    startDate: string | null | undefined,
    endDate: string | null | undefined
): string {
    if (!startDate) return '-';

    const start = dayjs(startDate);
    const end = endDate ? dayjs(endDate) : null;

    // If same day, show only one date
    if (end && start.isSame(end, 'day')) {
        return start.format('MMM DD, YYYY');
    }

    // If same year, omit year from start date
    if (end && start.year() === end.year()) {
        return `${start.format('MMM DD')} - ${end.format('MMM DD, YYYY')}`;
    }

    // Different years or no end date
    if (end) {
        return `${start.format('MMM DD, YYYY')} - ${end.format('MMM DD, YYYY')}`;
    }

    return start.format('MMM DD, YYYY');
}
