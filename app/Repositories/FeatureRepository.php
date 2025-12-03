<?php

namespace App\Repositories;

use App\Models\Feature;
use App\QueryBuilder\Filters\MultiFieldSearchFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class FeatureRepository
{
    /**
     * Get all features with pagination and optional filtering
     */
    public function getAllPaginated(Request $request): LengthAwarePaginator
    {
        // Map clean URL params to Spatie QueryBuilder format
        if ($request->has('search')) {
            $request->merge(['filter' => array_merge(
                $request->input('filter', []),
                ['search' => $request->input('search')]
            )]);
        }

        // Map category parameter to Spatie QueryBuilder format
        if ($request->has('category')) {
            $request->merge(['filter' => array_merge(
                $request->input('filter', []),
                ['category' => $request->input('category')]
            )]);
        }

        return QueryBuilder::for(Feature::class)
            ->allowedFilters([
                'name',
                'value',
                AllowedFilter::custom('search', new MultiFieldSearchFilter(['name', 'value', 'description'])),
                AllowedFilter::exact('category'),
            ])
            ->allowedSorts([
                'name',
                'value',
                'category',
                'created_at',
                'updated_at',
            ])
            ->defaultSort('name')
            ->paginate($request->input('per_page', 15))
            ->appends($request->query());
    }

    /**
     * Get feature for editing
     */
    public function getForEdit(Feature $feature): Feature
    {
        return $feature;
    }

    /**
     * Search features by name or value
     */
    public function search(?string $search = null, int $limit = 10): array
    {
        $query = Feature::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%");
            });
        }

        return $query
            ->limit($limit)
            ->get()
            ->map(function ($feature) {
                return [
                    'value' => (string) $feature->id,
                    'label' => $feature->name,
                    'icon' => $feature->icon,
                ];
            })
            ->toArray();
    }
}
