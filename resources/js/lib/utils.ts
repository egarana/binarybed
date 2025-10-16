import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

// export function urlIsActive(
//     urlToCheck: NonNullable<InertiaLinkProps['href']>,
//     currentUrl: string,
// ) {
//     return toUrl(urlToCheck) === currentUrl;
// }

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
): boolean {
    try {
        const normalizedToCheck = normalizeUrl(toUrl(urlToCheck));
        const normalizedCurrent = normalizeUrl(currentUrl);

        return normalizedToCheck === normalizedCurrent;
    } catch {
        // fallback jika parsing gagal
        return toUrl(urlToCheck).replace(/\/+$/, '') === currentUrl.replace(/\/+$/, '');
    }
}

function normalizeUrl(url: string): string {
    // pastikan URL bisa di-parse, baik relatif maupun absolut
    const u = new URL(url, window.location.origin);
    return u.pathname.replace(/\/+$/, '');
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}
