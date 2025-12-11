export interface Tenant {
    id: string;
    name: string;
    domain: string;
    type?: string;
    industry?: string;
    location?: string;
    resource_routes?: Record<string, 'units' | 'activities'>;
}
