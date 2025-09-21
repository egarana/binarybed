<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Property;
use App\Models\User;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Http\Request;
use Inertia\Response;
use Illuminate\Support\Arr;
use Illuminate\Http\RedirectResponse;

class PropertyController extends Controller
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
            'domain',
            AllowedSort::field('units_count'),
            AllowedSort::field('users_count'),
            'created_at',
            'updated_at',
        ];

        // Extract sort keys for validation
        $allowedSortKeys = [
            'name',
            'domain',
            'units_count',
            'users_count',
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
        $properties = QueryBuilder::for(Property::class, $request)
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('name', 'LIKE', "%{$value}%")
                            ->orWhere('domain', 'LIKE', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts($allowedSorts)
            ->withCount(['units', 'users'])
            ->with('users')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Properties/Index', [
            'properties' => $properties,
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

        $users = QueryBuilder::for(User::class)
            ->allowedFilters([
                AllowedFilter::partial('search', 'name'),
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(function ($user) {
                return [
                    'value' => (string) $user->id,
                    'label' => [
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ];
            });

        return Inertia::render('Properties/Create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePropertyRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $property = Property::create(Arr::only($validated, ['name', 'domain', 'slug']));

        if (!empty($validated['users'])) {
            $userIds = collect($validated['users'])->pluck('value')->toArray();
            $property->users()->sync($userIds);
        }

        return redirect()->route('properties.index');
    }


    /**
     * Display the specified resource.
     */
    public function show(Property $property)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Property $property, Request $request)
    {
        // Normalize 'search' to 'filter[search]'
        if ($request->filled('search')) {
            $request->merge([
                'filter' => ['search' => $request->input('search')]
            ]);
        }

        $users = QueryBuilder::for(User::class)
            ->allowedFilters([
                AllowedFilter::partial('search', 'name'),
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(function ($user) {
                return [
                    'value' => (string) $user->id,
                    'label' => [
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ];
            });

        // Load and format selected users only
        $property->load('users');
        $formattedProperty = [
            'id' => $property->id,
            'name' => $property->name,
            'domain' => $property->domain,
            'slug' => $property->slug,
            'users' => $property->users->map(fn ($user) => [
                'value' => (string) $user->id,
                'label' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]),
        ];

        return Inertia::render('Properties/Edit', [
            'property' => $formattedProperty,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePropertyRequest $request, Property $property): RedirectResponse
    {
        $validated = $request->validated();

        // Update the property fields
        $property->update(Arr::only($validated, ['name', 'domain', 'slug']));

        // Sync users if present
        if (!empty($validated['users'])) {
            $userIds = collect($validated['users'])->pluck('value')->toArray();
            $property->users()->sync($userIds);
        } else {
            $property->users()->sync([]);
        }

        return redirect()->route('properties.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property)
    {
        $property->delete();
    }
}
