<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\StoreRoleRequest;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\Permission\Events\PermissionDetached;
use App\Events\RolePermissionsUpdated;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleController extends Controller
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
            AllowedSort::field('users_count'),
            AllowedSort::field('permissions_count'),
            'created_at',
            'updated_at',
        ];

        // Extract sort keys for validation
        $allowedSortKeys = [
            'name',
            'users_count',
            'permissions_count',
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
        $roles = QueryBuilder::for(Role::class, $request)
            ->allowedFilters([
                AllowedFilter::partial('search', 'name'),
            ])
            ->allowedSorts($allowedSorts)
            ->withCount(['users', 'permissions'])
            ->with('permissions')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'perPage' => $perPage,
            'search' => $request->input('search'),
            'sort' => $sort,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Roles/Create', [
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->name]);

        sleep(1); // Ensure role is persisted properly before syncing

        $newPermissionIds = collect($request->permissions ?? [])->map(fn($id) => (int) $id)->sort()->values()->all();
        $oldPermissionIds = [];

        if (!empty($newPermissionIds)) {
            $role->syncPermissions($newPermissionIds);

            $added = array_diff($newPermissionIds, $oldPermissionIds);
            $addedNames = Permission::whereIn('id', $added)->pluck('name')->toArray();

            if (!empty($addedNames)) {
                RolePermissionsUpdated::dispatch($role, $addedNames, [], auth()->user());
            }
        }

        return redirect()->route('roles.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): Response
    {
        if ($role->name === 'super-admin') {
            throw new NotFoundHttpException();
        }
        
        return Inertia::render('Roles/Edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', "unique:roles,name,{$role->id}"],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->name = $validated['name'];
        $role->save();

        // Compare permission changes
        $newPermissionIds = collect($validated['permissions'] ?? [])->map(fn($id) => (int) $id)->sort()->values()->all();
        $oldPermissionIds = $role->permissions->pluck('id')->sort()->values()->all();

        if ($newPermissionIds !== $oldPermissionIds) {
            $role->syncPermissions($newPermissionIds);

            $added = array_diff($newPermissionIds, $oldPermissionIds);
            $removed = array_diff($oldPermissionIds, $newPermissionIds);

            $addedNames = Permission::whereIn('id', $added)->pluck('name')->toArray();
            $removedNames = Permission::whereIn('id', $removed)->pluck('name')->toArray();

            RolePermissionsUpdated::dispatch($role, $addedNames, $removedNames, auth()->user());
        }

        return redirect()->route('roles.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // 1. Prevent deleting super-admin role
        if ($role->name === 'super-admin') {
            return back()->withErrors([
                'authorization' => 'You cannot delete the super-admin role',
            ]);
        }

        // 2. Optional: prevent deleting a role currently assigned to yourself
        if (auth()->user()->hasRole($role->name)) {
            return back()->withErrors([
                'authorization' => 'You cannot delete a role assigned to yourself',
            ]);
        }

        // Delete the role
        $role->delete();
    }
}
