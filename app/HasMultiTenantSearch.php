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
     * @param callable $tenantDataMapper Callback to map tenant data to each item
     *                                    Receives: ($item, $tenant)
     *                                    Should return: array with tenant data added
     * @param callable|null $callback Callback to determine if we should fetch all records.
     *                                Returns true to fetch all, false to use query modifier.
     *                                Receives: ($query)
     * @param callable|null $queryModifier Callback to modify the query (e.g., add filters, eager loading)
     *                                      Receives: ($query)
     *                                      Should return: modified query or builder
     * @return Collection Collection of items from all tenants
     */
    protected function fetchFromAllTenants(
        string $modelClass,
        callable $tenantDataMapper,
        ?callable $callback = null,
        ?callable $queryModifier = null
    ): Collection {
        $allItems = collect();

        tenancy()->central(function () use (&$allItems, $modelClass, $callback, $queryModifier, $tenantDataMapper) {
            $tenants = Tenant::all();

            foreach ($tenants as $tenant) {
                // Initialize tenancy for each tenant
                tenancy()->initialize($tenant);

                // Determine if we should fetch all records or use query modifier
                $shouldFetchAll = $callback ? $callback() : false;

                // Always use query modifier if provided to preserve eager loading and withCount
                // The difference is:
                // - When shouldFetchAll=true: queryModifier is called without filters (for collection-level search)
                // - When shouldFetchAll=false: queryModifier is called with filters (for DB-level search)
                $query = $modelClass::query();

                if ($queryModifier) {
                    // Use queryModifier to apply withCount, eager loading, etc.
                    $items = $queryModifier($query)->get();
                } else {
                    // Fallback to simple query if no modifier provided
                    $items = $query->get();
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
     * It uses word-based search with AND logic between words:
     * - Split search value into words (by whitespace)
     * - Each word must match at least one field (OR per word)
     * - All words must match (AND between words)
     *
     * Example: "corporate nusa" matches items where:
     * - "corporate" appears in ANY of the search fields AND
     * - "nusa" appears in ANY of the search fields
     *
     * @param Collection $collection The collection to filter
     * @param string $searchValue The search term (can be multiple words)
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

        // Split search value into words (by whitespace)
        $searchWords = preg_split('/\s+/', trim($searchValue), -1, PREG_SPLIT_NO_EMPTY);

        if (empty($searchWords)) {
            return $collection;
        }

        return $collection->filter(function ($item) use ($searchWords, $searchFields) {
            // AND condition: ALL words must match
            foreach ($searchWords as $word) {
                $wordMatched = false;

                // OR condition: word can match ANY field
                foreach ($searchFields as $field) {
                    $fieldValue = is_array($item) ? ($item[$field] ?? null) : ($item->{$field} ?? null);

                    if ($fieldValue !== null && stripos((string) $fieldValue, $word) !== false) {
                        $wordMatched = true;
                        break; // Word matched in this field, no need to check other fields
                    }
                }

                // If word didn't match any field, item doesn't match
                if (!$wordMatched) {
                    return false;
                }
            }

            // All words matched
            return true;
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
