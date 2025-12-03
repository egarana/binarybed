<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeatureRequest;
use App\Http\Requests\UpdateFeatureRequest;
use App\Models\Feature;
use App\Services\FeatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeatureController extends Controller
{
    public function __construct(
        protected FeatureService $service
    ) {}

    public function index(Request $request): Response
    {
        $features = $this->service->getAllPaginated($request);

        return Inertia::render('features/Index', compact('features'));
    }

    public function create(): Response
    {
        $categories = Feature::getCategories();

        return Inertia::render('features/Create', compact('categories'));
    }

    public function store(StoreFeatureRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('features.index', ['sort' => '-created_at']);
    }

    public function edit(Feature $feature): Response
    {
        $feature = $this->service->getForEdit($feature);
        $categories = Feature::getCategories();

        return Inertia::render('features/Edit', compact('feature', 'categories'));
    }

    public function update(UpdateFeatureRequest $request, Feature $feature): RedirectResponse
    {
        $this->service->update($feature, $request->validated());

        return redirect()->route('features.index', ['sort' => '-updated_at']);
    }

    public function destroy(Feature $feature): RedirectResponse
    {
        $this->service->delete($feature);

        return redirect()->route('features.index');
    }
}
