<?php

namespace App;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

trait HasMultiTenantSearch
{
    /**
     * Fetch data from all tenant databases
     *
     * @param string $modelClass The fully qualified model class name
     * @param callable|null $callback Callback to determine if we should fetch all records.
     *                                Returns true to fetch all, false to use query modifier.
     *                                Receives: ($query)
     * @param callable|null $queryModifier Callback to modify the query (e.g., add filters, eager loading)
     *                                      Receives: ($query)
     *                                      Should return: modified query or builder
     * @param callable $tenantDataMapper Callback to map tenant data to each item
     *                                    Receives: ($item, $tenant)
     *                                    Should return: array with tenant data added
     * @return Collection Collection of items from all tenants
     */
    protected function fetchFromAllTenants(
        string $modelClass,
        ?callable $callback = null,
        ?callable $queryModifier = null,
        callable $tenantDataMapper
    ): Collection {
        $allItems = collect();

        tenancy()->central(function () use (&$allItems, $modelClass, $callback, $queryModifier, $tenantDataMapper) {
            $tenants = Tenant::all();

            foreach ($tenants as $tenant) {
                // Initialize tenancy for each tenant
                tenancy()->initialize($tenant);

                // Determine if we should fetch all records or use query modifier
                $shouldFetchAll = $callback ? $callback() : false;

                if ($shouldFetchAll) {
                    // Fetch all records for collection-level filtering
                    $items = $modelClass::all();
                } else {
                    // Use query modifier for DB-level filtering
                    $query = $modelClass::query();
                    $items = $queryModifier ? $queryModifier($query)->get() : $query->get();
                }

                // Convert models to arrays and add tenant information
                $itemsArray = $items->map(function ($item) use ($tenant, $tenantDataMapper) {
                    return $tenantDataMapper($item, $tenant);
                });

                $allItems = $allItems->merge($itemsArray);
            }

            // End tenancy to return to central connection
            tenancy()->end();
        });

        return $allItems;
    }

    /**
     * Apply collection-level search filter
     *
     * This method filters a collection based on search value and fields.
     * It uses OR logic: an item matches if ANY of the specified fields contains the search value.
     * The search is case-insensitive.
     *
     * @param Collection $collection The collection to filter
     * @param string $searchValue The search term
     * @param array $searchFields Array of field names to search in
     * @return Collection Filtered collection
     */
    protected function applyCollectionSearch(
        Collection $collection,
        string $searchValue,
        array $searchFields
    ): Collection {
        if (empty($searchValue) || empty($searchFields)) {
            return $collection;
        }

        return $collection->filter(function ($item) use ($searchValue, $searchFields) {
            // OR condition: match if ANY field contains the search value
            foreach ($searchFields as $field) {
                $fieldValue = is_array($item) ? ($item[$field] ?? null) : ($item->{$field} ?? null);

                if ($fieldValue !== null && stripos((string) $fieldValue, $searchValue) !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Determine if search should be done at collection level
     *
     * This helper method checks if any of the search fields are collection-level fields
     * (fields that don't exist in the database but are added post-query).
     *
     * @param array $searchFields Fields being searched
     * @param array $collectionFields Fields that only exist at collection level
     * @return bool True if collection-level search is needed
     */
    protected function needsCollectionLevelSearch(array $searchFields, array $collectionFields): bool
    {
        return count(array_intersect($searchFields, $collectionFields)) > 0;
    }

    /**
     * Get database-only fields from search fields
     *
     * Filters out collection-level fields from search fields array.
     *
     * @param array $searchFields All search fields
     * @param array $collectionFields Fields that only exist at collection level
     * @return array Database fields only
     */
    protected function getDatabaseFields(array $searchFields, array $collectionFields): array
    {
        return array_values(array_diff($searchFields, $collectionFields));
    }
}