<?php

namespace App\Services;

use App\Models\Feature;
use App\Repositories\FeatureRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeatureService
{
    public function __construct(
        protected FeatureRepository $featureRepository
    ) {}

    public function getAllPaginated(Request $request)
    {
        return $this->featureRepository->getAllPaginated($request);
    }

    public function getForEdit(Feature $feature): array
    {
        $feature = $this->featureRepository->getForEdit($feature);

        return [
            'id' => $feature->id,
            'name' => $feature->name,
            'value' => $feature->value,
            'description' => $feature->description,
            'icon' => $feature->icon,
        ];
    }

    public function search(?string $search = null, int $limit = 10): array
    {
        return $this->featureRepository->search($search, $limit);
    }

    public function create(array $data): Feature
    {
        return Feature::create([
            'name' => $data['name'],
            'value' => $data['value'],
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
        ]);
    }

    public function update(Feature $feature, array $data): Feature
    {
        $feature->update([
            'name' => $data['name'] ?? $feature->name,
            'value' => $data['value'] ?? $feature->value,
            'description' => $data['description'] ?? $feature->description,
            'icon' => $data['icon'] ?? $feature->icon,
        ]);

        $updatedFeature = $feature->fresh();

        // Sync updated feature to all tenant databases
        foreach (\App\Models\Tenant::all() as $tenant) {
            $tenant->run(function () use ($updatedFeature) {
                \App\Models\ResourceFeature::where('feature_id', $updatedFeature->id)
                    ->update([
                        'name' => $updatedFeature->name,
                        'value' => $updatedFeature->value,
                        'description' => $updatedFeature->description,
                        'icon' => $updatedFeature->icon,
                    ]);
            });
        }

        return $updatedFeature;
    }

    public function delete(Feature $feature): void
    {
        $featureId = $feature->id;

        // Delete from central database first
        $feature->delete();

        // Clean up from all tenant databases
        foreach (\App\Models\Tenant::all() as $tenant) {
            $tenant->run(function () use ($featureId) {
                // Delete from pivot tables (resource_features)
                DB::table('resource_features')
                    ->where('feature_id', $featureId)
                    ->delete();

                // Delete from synced features table
                \App\Models\ResourceFeature::where('feature_id', $featureId)->delete();
            });
        }
    }
}
