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
                ->where('rateable_id', $resourceId)
                ->orderBy('created_at', 'desc');

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
