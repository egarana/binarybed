<?php

namespace App\Repositories;

use App\Models\Unit;
use Illuminate\Http\Request;
use App\Services\PaginationService;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\AllowedFilter;
use App\QueryBuilder\Sorts\RelationSort;
use App\QueryBuilder\Filters\RelationFilter;
use App\QueryBuilder\Filters\MultiFieldSearchFilter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UnitRepository
{
    public function __construct(
        protected PaginationService $pagination
    ) {}

    private function baseQuery(): QueryBuilder
    {
        return QueryBuilder::for(Unit::class)
            ->allowedFilters([
                'name',
                'slug',
                AllowedFilter::custom('search', new MultiFieldSearchFilter(['name', 'slug'])),
            ])
            ->allowedSorts([
                'name', 'slug', 'created_at', 'updated_at',
                // AllowedSort::custom('domain', new RelationSort('domains', 'domain', 'MIN')),
            ])
            ->defaultSort('name');
    }

    public function getAll()
    {
        return Unit::orderBy('name')->get();
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

    public function getForEdit(Unit $unit): Unit
    {
        return $unit;
    }

    public function create(array $data): Unit
    {
        $unit = Unit::create($data);

        return $unit;
    }

    public function update(Unit $unit, array $data): Unit
    {
        $unit->update($data);

        return $unit->fresh();
    }

    public function delete(Unit $unit): void
    {
        $unit->delete();
    }

    /**
     * Search units for combobox (with limit).
     * Returns units in format: [{ value: id, label: name }]
     *
     * @param string|null $search
     * @param int $limit
     * @return array
     */
    public function search(?string $search = null, int $limit = 10): array
    {
        $query = Unit::query();

        // If no search term, return first N units
        if (!$search) {
            return $query->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(fn($unit) => [
                    'value' => $unit->id,
                    'label' => $unit->name,
                ])
                ->toArray();
        }

        // Search by name or id
        return $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn($unit) => [
                'value' => $unit->id,
                'label' => $unit->name,
            ])
            ->toArray();
    }
}