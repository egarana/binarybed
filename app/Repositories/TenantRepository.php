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
                'slug',
                AllowedFilter::custom('domain', new RelationFilter('domains', 'domain')),
            ])
            ->allowedSorts([
                'id', 'name', 'slug', 'created_at', 'updated_at',
                AllowedSort::custom('domain', new RelationSort('domains', 'domain', 'MIN')),
            ])
            ->defaultSort('name')
            ->with('domains');
    }

    public function all()
    {
        return $this->baseQuery()
            ->get();
    }

    public function paginate(Request $request)
    {
        $perPage = $this->pagination->resolvePerPage($request);

        $result = $this->baseQuery()
            ->paginate($perPage)
            ->appends($request->query());

        if ($result->currentPage() > $result->lastPage() && $result->total() > 0) {
            throw new NotFoundHttpException();
        }

        return $result;
    }

    public function find(int|string $id): Tenant
    {
        return Tenant::with('domains')->findOrFail($id);
    }

    public function create(array $data): Tenant
    {
        $tenant = Tenant::create($data);

        return $tenant->load('domains');
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);

        return $tenant->load('domains');
    }

    public function delete(Tenant $tenant): void
    {
        $tenant->delete();
    }
}
