<?php

namespace App\Actions\Rates;

use App\HandlesTenancy;
use App\Models\Rate;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class GetRatesForResource
{
    use HandlesTenancy;

    /**
     * Get all rates for a specific resource.
     *
     * @param string $tenantId
     * @param string $resourceType Fully qualified class name (e.g., App\Models\Unit)
     * @param int $resourceId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function execute(string $tenantId, string $resourceType, int $resourceId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->executeInTenantContext($tenantId, function () use ($resourceType, $resourceId, $perPage) {
            $query = Rate::where('rateable_type', $resourceType)
                ->where('rateable_id', $resourceId);

            // Apply status filter
            $statusFilter = request()->input('status');
            if ($statusFilter !== null && $statusFilter !== '') {
                $isActive = $statusFilter === 'active' || $statusFilter === '1' || $statusFilter === 'true';
                $query->where('is_active', $isActive);
            }

            // Apply currency filter
            $currencyFilter = request()->input('currency');
            if ($currencyFilter !== null && $currencyFilter !== '') {
                $query->where('currency', strtoupper($currencyFilter));
            }

            // Apply search filter
            $searchValue = request()->input('search');
            if ($searchValue) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('slug', 'like', "%{$searchValue}%");
                });
            }

            // Apply sorting
            $sortField = request()->input('sort', 'created_at');
            $sortDirection = str_starts_with($sortField, '-') ? 'desc' : 'asc';
            $sortField = ltrim($sortField, '-');

            // Map sort aliases
            $sortAliases = [
                'status' => 'is_active',
            ];
            $sortField = $sortAliases[$sortField] ?? $sortField;

            // Validate sort field
            $allowedSorts = ['name', 'slug', 'price', 'currency', 'is_active', 'created_at', 'updated_at'];
            if (!in_array($sortField, $allowedSorts)) {
                $sortField = 'created_at';
            }

            $query->orderBy($sortField, $sortDirection);

            $currentPage = Paginator::resolveCurrentPage();
            $total = $query->count();
            $items = $query->skip(($currentPage - 1) * $perPage)->take($perPage)->get();

            return new LengthAwarePaginator(
                $items->map(function ($rate) {
                    return [
                        'id' => $rate->id,
                        'name' => $rate->name,
                        'slug' => $rate->slug,
                        'description' => $rate->description,
                        'price' => $rate->price,
                        'currency' => $rate->currency,
                        'is_active' => $rate->is_active,
                        'is_default' => $rate->is_default,
                        'created_at' => $rate->created_at,
                        'updated_at' => $rate->updated_at,
                    ];
                }),
                $total,
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        });
    }
}
