<?php

namespace App\Actions\Units;

use App\HandlesTenancy;
use App\Models\Unit;

class GetAttachedUsersForUnit
{
    use HandlesTenancy;

    /**
     * Get all users attached to a unit.
     *
     * @param string $tenantId
     * @param string $slug
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function execute(string $tenantId, string $slug): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->executeInTenantContext($tenantId, function () use ($slug) {
            $unit = Unit::where('slug', $slug)->firstOrFail();

            // Use Laravel's built-in pagination
            return $unit->users()
                ->orderBy('name')
                ->paginate(request()->input('per_page', 15))
                ->through(function ($user) {
                    return [
                        'global_id' => $user->global_id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'assigned_at' => $user->pivot->assigned_at,
                        'created_at' => $user->created_at,
                    ];
                });
        });
    }
}
