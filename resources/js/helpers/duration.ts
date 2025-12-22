/**
 * Format duration in minutes to human-readable format (e.g., "2h 30m")
 * @param minutes - Duration in minutes
 * @returns Formatted duration string
 */
export function formatDuration(minutes: number | null | undefined): string {
    if (minutes === null || minutes === undefined || minutes <= 0) {
        return 'Not set';
    }

    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;

    if (hours === 0) {
        return `${mins}m`;
    }

    if (mins === 0) {
        return `${hours}h`;
    }

    return `${hours}h ${mins}m`;
}

/**
 * Format duration in days to human-readable format
 * Context-aware: uses "nights" for accommodation, "days" for other resources
 * 
 * @param days - Duration in days
 * @param isAccommodation - If true, uses "nights" (hotel industry standard), otherwise "days"
 * @returns Formatted duration string
 */
export function formatDurationDays(
    days: number | null | undefined,
    isAccommodation: boolean = false
): string {
    if (days === null || days === undefined || days <= 0) {
        return 'Not set';
    }

    const unit = isAccommodation ? 'night' : 'day';
    const plural = days === 1 ? '' : 's';

    return `${days} ${unit}${plural}`;
}
