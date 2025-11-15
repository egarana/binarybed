<?php

namespace App\Repositories;

use App\Models\Agent;
use Illuminate\Http\Request;
use App\Services\PaginationService;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\AllowedFilter;
use App\QueryBuilder\Sorts\RelationSort;
use App\QueryBuilder\Filters\RelationFilter;
use App\QueryBuilder\Filters\MultiFieldSearchFilter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AgentRepository
{
    public function __construct(
        protected PaginationService $pagination
    ) {}

    private function baseQuery(): QueryBuilder
    {
        return QueryBuilder::for(Agent::class)
            ->allowedFilters([
                'code',
                'name',
                'email',
                AllowedFilter::custom('search', new MultiFieldSearchFilter(['code', 'name', 'email'])),
            ])
            ->allowedSorts([
                'code', 'name', 'email', 'created_at', 'updated_at',
                // AllowedSort::custom('domain', new RelationSort('domains', 'domain', 'MIN')),
            ])
            ->defaultSort('name');
    }

    public function getAll()
    {
        return Agent::orderBy('name')->get();
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

    public function getForEdit(Agent $agent): Agent
    {
        return $agent->load('user');
    }

    public function create(array $data): Agent
    {
        $agent = Agent::create($data);

        return $agent;
    }

    public function update(Agent $agent, array $data): Agent
    {
        $agent->update($data);

        return $agent->fresh()->load('domains');
    }

    public function delete(Agent $agent): void
    {
        $agent->delete();
    }

    /**
     * Search agents for combobox (with limit).
     * Returns agents in format: [{ value: id, label: name }]
     *
     * @param string|null $search
     * @param int $limit
     * @return array
     */
    public function search(?string $search = null, int $limit = 10): array
    {
        $query = Agent::query();

        // If no search term, return first N agents
        if (!$search) {
            return $query->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(fn($agent) => [
                    'value' => $agent->id,
                    'label' => $agent->name,
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
            ->map(fn($agent) => [
                'value' => $agent->id,
                'label' => $agent->name,
            ])
            ->toArray();
    }
}