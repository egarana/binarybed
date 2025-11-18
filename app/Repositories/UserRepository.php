<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\PaginationService;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\AllowedFilter;
use App\QueryBuilder\Sorts\RelationSort;
use App\QueryBuilder\Filters\RelationFilter;
use App\QueryBuilder\Filters\MultiFieldSearchFilter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserRepository
{
    public function __construct(
        protected PaginationService $pagination
    ) {}

    private function baseQuery(): QueryBuilder
    {
        return QueryBuilder::for(User::class)
            ->allowedFilters([
                'name',
                'email',
                AllowedFilter::custom('search', new MultiFieldSearchFilter(['name', 'email'])),
            ])
            ->allowedSorts([
                'name', 'email', 'created_at', 'updated_at',
                // AllowedSort::custom('domain', new RelationSort('domains', 'domain', 'MIN')),
            ])
            ->defaultSort('name');
    }

    public function getAll()
    {
        return User::orderBy('name')->get();
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

    public function getForEdit(User $user): User
    {
        return $user;
    }

    public function create(array $data): User
    {
        $user = User::create($data);

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    /**
     * Search users for combobox (with limit).
     * Returns users in format: [{ value: id, label: name }]
     *
     * @param string|null $search
     * @param int $limit
     * @return array
     */
    public function search(?string $search = null, int $limit = 10): array
    {
        $query = User::query();

        // If no search term, return first N users
        if (!$search) {
            return $query->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(fn($user) => [
                    'value' => $user->id,
                    'label' => $user->name,
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
            ->map(fn($user) => [
                'value' => $user->id,
                'label' => $user->name,
            ])
            ->toArray();
    }
}