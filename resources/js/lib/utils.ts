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
    href: any,
    currentUrl: string,
) {
    const url = typeof href === 'string' ? href : href.url;

    // Ambil path saja dari full URL
    const pathname = new URL(url, window.location.origin).pathname;

    // Active jika currentUrl diawali pathname
    return currentUrl.startsWith(pathname);
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}
