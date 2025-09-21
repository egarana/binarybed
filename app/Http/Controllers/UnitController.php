<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use App\Models\Property;
use App\Models\Rate;
use App\Models\Feature;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Http\RedirectResponse;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // Normalize 'search' to 'filter[search]'
        if ($request->filled('search')) {
            $request->merge([
                'filter' => ['search' => $request->input('search')]
            ]);
        }

        // Validate perPage
        $perPage = intval($request->query('perPage', 20));
        if (!in_array($perPage, [10, 20, 30, 40, 50])) {
            $perPage = 20;
        }

        // Define allowed sorts
        $allowedSorts = [
            'name',
            AllowedSort::callback('property', function ($query, $direction, $property) {
                // ✅ Normalize direction (fixes Spatie bug)
                $direction = in_array(strtolower($direction), ['asc', 'desc'])
                    ? strtolower($direction)
                    : (request()->query('sort') === "-{$property}" ? 'desc' : 'asc');

                    // ✅ Join properties table if not already joined
                    if (!collect($query->getQuery()->joins ?? [])->pluck('table')->contains('properties')) {
                        $query->leftJoin('properties', 'units.property_id', '=', 'properties.id');
                    }
                    
                    // ✅ Avoid column conflict
                    $query->select('units.*');
                    
                    // ✅ Sort by property name
                    $query->orderBy('properties.name', $direction);
                }),
            'qty',
            AllowedSort::callback('standard_rate_price', function ($query, $direction, $standardRatePrice) {
                // ✅ Normalize direction (fixes Spatie bug)
                $direction = in_array(strtolower($direction), ['asc', 'desc'])
                    ? strtolower($direction)
                    : (request()->query('sort') === "-{$standardRatePrice}" ? 'desc' : 'asc');

                // Join the rates table with the "Standard Rate" filter for sorting by price
                if (!collect($query->getQuery()->joins ?? [])->pluck('table')->contains('rates')) {
                    $query->leftJoin('rates', function ($join) {
                        $join->on('units.id', '=', 'rates.unit_id')
                            ->where('rates.name', '=', 'Standard Rate');
                    });
                }

                $query->orderBy('rates.price', $direction)->select('units.*');
            }),
            AllowedSort::field('rates_count'),
            'created_at',
            'updated_at',
        ];

        // Extract sort keys for validation
        $allowedSortKeys = [
            'name',
            'property',
            'qty',
            'standard_rate_price',
            'rates_count',
            'created_at',
            'updated_at',
        ];

        // Validate and sanitize sort parameter
        $sort = $request->query('sort');
        if ($sort && !in_array(ltrim($sort, '-'), $allowedSortKeys)) {
            // Remove invalid sort from query
            $request->query->remove('sort');
            $sort = null;
        }

        // Build query
        $units = QueryBuilder::for(Unit::class, $request)
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('units.name', 'like', "%{$value}%")
                            ->orWhereHas('property', function ($q) use ($value) {
                                $q->where('properties.name', 'like', "%{$value}%");
                            });
                    });
                }),
            ])
            ->allowedSorts($allowedSorts)
            ->withCount('rates')
            ->with(['property', 'standardRate'])
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Units/Index', [
            'units' => $units,
            'perPage' => $perPage,
            'search' => $request->input('search'),
            'sort' => $sort,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Normalize 'search' to 'filter[search]'
        if ($request->filled('search')) {
            $request->merge([
                'filter' => ['search' => $request->input('search')]
            ]);
        }

        $properties = QueryBuilder::for(Property::class)
            ->allowedFilters([
                AllowedFilter::partial('search', 'name'),
            ])
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function ($property) {
                return [
                    'value' => (string) $property->id,
                    'label' => $property->name,
                ];
            });

        $features = QueryBuilder::for(Feature::class)
            ->allowedFilters([
                AllowedFilter::partial('search', 'name'),
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'icon'])
            ->map(function ($feature) {
                return [
                    'value' => (string) $feature->id,
                    'name' => $feature->name,
                    'icon' => $feature->icon,
                ];
            });

        return Inertia::render('Units/Create', [
            'properties' => $properties,
            'features' => $features,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['features'] = collect($validated['features'] ?? [])
            ->pluck('value')
            ->map(fn($id) => (int) $id)
            ->values()
            ->toArray();

        // Create the Unit
        $unit = Unit::create([
            'name' => $validated['name'],
            'property_id' => $validated['property']['value'] ?? null,
            'features' => $validated['features'],
        ]);

        // Create the Standard Rate for the Unit
        $unit->rates()->create([
            'name' => 'Standard Rate',
            'price' => $validated['standard_rate'],
        ]);

        return redirect()->route('units.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit, Request $request): Response
    {
        // Normalize 'search' to 'filter[search]'
        if ($request->filled('search')) {
            $request->merge([
                'filter' => ['search' => $request->input('search')]
            ]);
        }

        // Define allowed sorts
        $allowedSorts = [
            'name',
            'price',
            'created_at',
            'updated_at',
        ];

        // Extract sort keys for validation
        $allowedSortKeys = [
            'name',
            'price',
            'created_at',
            'updated_at',
        ];

        // Validate and sanitize sort parameter
        $sort = $request->query('sort');
        if ($sort && !in_array(ltrim($sort, '-'), $allowedSortKeys)) {
            // Remove invalid sort from query
            $request->query->remove('sort');
            $sort = null;
        }

        $rate = null;
        if ($request->filled('rateId')) {
            $rate = Rate::find($request->input('rateId'));
        }

        $properties = QueryBuilder::for(Property::class)
            ->allowedFilters([
                AllowedFilter::partial('search', 'name'),
            ])
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function ($property) {
                return [
                    'value' => (string) $property->id,
                    'label' => $property->name,
                ];
            });

        // $features = QueryBuilder::for(Feature::class)
        //     ->allowedFilters([
        //         AllowedFilter::partial('search', 'name'),
        //     ])
        //     ->orderBy('name')
        //     ->get(['id', 'name', 'icon'])
        //     ->map(function ($feature) {
        //         return [
        //             'value' => (string) $feature->id,
        //             'name' => $feature->name,
        //             'icon' => $feature->icon,
        //         ];
        //     });

        $allFeatures = QueryBuilder::for(Feature::class)
            ->allowedFilters([AllowedFilter::partial('search', 'name')])
            ->orderBy('name')
            ->get(['id', 'name', 'icon'])
            ->map(fn($feature) => [
                'value' => (string) $feature->id,
                'name'  => $feature->name,
                'icon'  => $feature->icon,
            ]);

        // Load property and rates for the unit
        // $unit->load(['property', 'rates']);

        $selectedFeatures = [];
        if (!empty($unit->features)) {
            $selectedFeatures = Feature::whereIn('id', $unit->features)
                ->orderByRaw('FIELD(id, ' . implode(',', $unit->features) . ')')
                ->get()
                ->map(fn($feature) => [
                    'value' => (string) $feature->id,
                    'name'  => $feature->name,
                    'icon'  => $feature->icon,
                ])
                ->toArray();
        }

        $rates = QueryBuilder::for(Rate::class)
            ->where('unit_id', $unit->id)
            ->allowedSorts($allowedSorts)
            ->defaultSort('id') // optional
            ->get();

        $formattedUnit = [
            'id' => $unit->id,
            'name' => $unit->name,
            'property' => $unit->property ? [
                'value' => (string) $unit->property->id,
                'label' => $unit->property->name,
            ] : null,
            'features' => $selectedFeatures,
        ];

        return Inertia::render('Units/Edit', [
            'unit' => $formattedUnit,
            'properties' => $properties,
            'features' => $allFeatures,
            'rates' => $rates,
            'rate' => $rate,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreUnitRequest $request, Unit $unit)
    {
        $validated = $request->validated();

        $unit->name = $request->input('name');

        // Handle optional property assignment
        if ($request->filled('property.value')) {
            $unit->property_id = $request->input('property.value');
        } else {
            $unit->property_id = null; // Clear the property if none selected
        }

        // Handle features (same logic as store)
        $unit->features = collect($validated['features'] ?? [])
            ->pluck('value')
            ->map(fn($id) => (int) $id)
            ->values()
            ->toArray();

        $unit->save();

        return redirect()->route('units.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        $unit->delete();
    }
}
