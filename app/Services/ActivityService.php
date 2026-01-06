<?php

namespace App\Services;

use App\Actions\Activities\AttachUserToActivity;
use App\Actions\Activities\CreateActivity;
use App\Actions\Activities\DeleteActivity;
use App\Actions\Activities\DetachUserFromActivity;
use App\Actions\Activities\FindActivityByTenantAndSlug;
use App\Actions\Activities\FindActivityForCommission;
use App\Actions\Activities\GetAttachedUsersForActivity;
use App\Actions\Activities\UpdateActivity;
use App\Actions\Activities\UpdateUserActivityRole;
use App\HasCrossTenantsQuery;
use App\Models\Activity;
use App\Repositories\ActivityRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityService
{
    public function __construct(
        protected ActivityRepository $activityRepository,
        protected CreateActivity $createActivity,
        protected UpdateActivity $updateActivity,
        protected DeleteActivity $deleteActivity,
        protected FindActivityByTenantAndSlug $findActivityByTenantAndSlug,
        protected FindActivityForCommission $findActivityForCommission,
        protected AttachUserToActivity $attachUserToActivity,
        protected DetachUserFromActivity $detachUserFromActivity,
        protected GetAttachedUsersForActivity $getAttachedUsersForActivity,
        protected UpdateUserActivityRole $updateUserActivityRole
    ) {}

    public function getAllFromAllTenantsPaginated(Request $request)
    {
        return $this->activityRepository->getAllFromAllTenantsPaginated($request);
    }

    public function getForEdit(string $tenantId, string $slug): array
    {
        return $this->findActivityByTenantAndSlug->execute($tenantId, $slug);
    }

    public function getForCommission(string $tenantId, string $slug): array
    {
        return $this->findActivityForCommission->execute($tenantId, $slug);
    }

    public function create(array $data): Activity
    {
        return $this->createActivity->execute($data);
    }

    public function update(string $tenantId, string $slug, array $data): Activity
    {
        return $this->updateActivity->execute($tenantId, $slug, $data);
    }

    public function delete(string $tenantId, string $slug): void
    {
        $this->deleteActivity->execute($tenantId, $slug);
    }

    public function attachUserToActivity(string $tenantId, string $slug, array $data): void
    {
        $this->attachUserToActivity->execute($tenantId, $slug, $data);
    }

    public function detachUserFromActivity(string $tenantId, string $slug, string $userGlobalId): void
    {
        $this->detachUserFromActivity->execute($tenantId, $slug, $userGlobalId);
    }

    public function updateUserActivityRole(string $tenantId, string $slug, string $userGlobalId, array $data): void
    {
        $this->updateUserActivityRole->execute($tenantId, $slug, $userGlobalId, $data);
    }

    public function getAttachedUsers(string $tenantId, string $slug): LengthAwarePaginator
    {
        return $this->getAttachedUsersForActivity->execute($tenantId, $slug);
    }

    public function getForFeatures(string $tenantId, string $slug): array
    {
        return $this->findActivityByTenantAndSlug->executeForFeatures($tenantId, $slug);
    }

    public function syncFeatures(string $tenantId, string $slug, array $featuresData): void
    {
        $this->updateActivity->syncFeatures($tenantId, $slug, $featuresData);
    }
}
