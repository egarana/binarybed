import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
): boolean {
    try {
        const normalizedToCheck = normalizeUrl(toUrl(urlToCheck));
        const normalizedCurrent = normalizeUrl(currentUrl);

        // cocok jika currentUrl diawali oleh urlToCheck (mis. /tenants -> /tenants/create)
        return (
            normalizedCurrent === normalizedToCheck ||
            normalizedCurrent.startsWith(normalizedToCheck + '/')
        );
    } catch {
        // fallback jika parsing gagal
        const base = toUrl(urlToCheck).replace(/\/+$/, '');
        const current = currentUrl.replace(/\/+$/, '');
        return current === base || current.startsWith(base + '/');
    }
}

function normalizeUrl(url: string): string {
    const u = new URL(url, window.location.origin);
    return u.pathname.replace(/\/+$/, '');
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}
