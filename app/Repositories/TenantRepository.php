<?php

namespace App\Repositories;

use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Services\PaginationService;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\AllowedFilter;
use App\QueryBuilder\Sorts\RelationSort;
use App\QueryBuilder\Filters\RelationFilter;
use App\QueryBuilder\Filters\MultiFieldSearchFilter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TenantRepository
{
    public function __construct(
        protected PaginationService $pagination
    ) {}

    private function baseQuery(): QueryBuilder
    {
        return QueryBuilder::for(Tenant::class)
            ->allowedFilters([
                'id',
                'name',
                AllowedFilter::custom('domain', new RelationFilter('domains', 'domain')),
                AllowedFilter::custom('search', new MultiFieldSearchFilter(['name', 'id'])),
            ])
            ->allowedSorts([
                'id', 'name', 'created_at', 'updated_at',
                AllowedSort::custom('domain', new RelationSort('domains', 'domain', 'MIN')),
            ])
            ->defaultSort('name')
            ->with('domains');
    }

    public function getAllPaginated(Request $request)
    {
        $perPage = $this->pagination->resolvePerPage($request);

        // Map clean URL params to Spatie QueryBuilder format
        if ($request->has('search')) {
            $request->merge(['filter' => array_merge(
                $request->input('filter', []),
                ['search' => $request->input('search')]
            )]);
        }

        $result = $this->baseQuery()
            ->paginate($perPage)
            ->appends($request->query());

        if ($result->currentPage() > $result->lastPage() && $result->total() > 0) {
            throw new NotFoundHttpException();
        }

        return $result;
    }

    /**
     * Create a new tenant.
     */
    public function create(array $data): Tenant
    {
        $tenant = Tenant::create($data);

        return $tenant->load('domains');
    }

    /**
     * Update an existing tenant.
     */
    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);

        return $tenant->fresh()->load('domains');
    }

    /**
     * Delete a tenant (soft delete).
     */
    public function delete(Tenant $tenant): void
    {
        $tenant->delete();
    }

    /**
     * Force delete a tenant (permanent delete).
     */
    public function forceDelete(Tenant $tenant): void
    {
        $tenant->forceDelete();
    }

    /**
     * Find a tenant by ID.
     */
    public function find(string $id): ?Tenant
    {
        return Tenant::find($id);
    }

    /**
     * Find a tenant by ID with domains loaded.
     */
    public function findWithDomains(string $id): ?Tenant
    {
        return Tenant::with('domains')->find($id);
    }

    /**
     * Get all tenants without pagination.
     */
    public function getAll()
    {
        return Tenant::orderBy('name')->get();
    }

    /**
     * Search tenants for combobox (with limit).
     * Returns tenants in format: [{ value: id, label: name }]
     *
     * @param string|null $search
     * @param int $limit
     * @return array
     */
    public function search(?string $search = null, int $limit = 10): array
    {
        $query = Tenant::query();

        // If no search term, return first N tenants
        if (!$search) {
            return $query->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(fn($tenant) => [
                    'value' => $tenant->id,
                    'label' => $tenant->name,
                ])
                ->toArray();
        }

        // Search by name or id
        return $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn($tenant) => [
                'value' => $tenant->id,
                'label' => $tenant->name,
            ])
            ->toArray();
    }
}