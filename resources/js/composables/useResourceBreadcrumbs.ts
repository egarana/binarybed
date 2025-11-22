import type { BreadcrumbItem } from '@/types';

interface UseResourceBreadcrumbsConfig {
    resourceName: string;
    resourceNamePlural: string;
    indexRoute: string;
    action?: 'create' | 'edit';
    actionRoute?: string;
}

export function useResourceBreadcrumbs(config: UseResourceBreadcrumbsConfig): BreadcrumbItem[] {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: config.resourceNamePlural,
            href: config.indexRoute,
        },
    ];

    if (config.action && config.actionRoute) {
        breadcrumbs.push({
            title: `${config.action === 'create' ? 'Create' : 'Edit'} ${config.resourceName}`,
            href: config.actionRoute,
        });
    }

    return breadcrumbs;
}
