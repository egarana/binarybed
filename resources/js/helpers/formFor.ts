/**
 * Universal form binding helper for Wayfinder routes.
 * 
 * 🎯 Tujuan:
 * - Menghasilkan struktur `{ action, method }` yang cocok untuk <Form v-bind="...">
 * - Tidak akan ke-overwrite saat regenerasi Wayfinder
 * - Bisa dipakai untuk semua HTTP methods (POST, PUT, DELETE, dll)
 */

import type { RouteDefinition } from '@/wayfinder'

/**
 * Create a standard form binding from a Wayfinder route definition.
 * 
 * @example
 * <Form v-bind="formFor(UserController.store.post())">
 */
export function formFor(route: RouteDefinition<any>) {
    // Normalisasi method spoofing untuk Laravel
    const method = route.method?.toUpperCase() ?? 'POST'

    // Laravel hanya menerima GET/POST langsung
    // PUT, PATCH, DELETE harus disimulasikan via _method field
    const needsSpoof = ['PUT', 'PATCH', 'DELETE'].includes(method)

    // Kembalikan struktur yang bisa langsung digunakan di Inertia Form
    return {
        action: route.url,
        method: needsSpoof ? 'post' : method.toLowerCase(),
        ...(needsSpoof && { query: { _method: method } }),
    }
}
