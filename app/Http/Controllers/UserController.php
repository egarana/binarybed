<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Role;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;
use App\Events\UserRolesUpdated;

class UserController extends Controller
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

        $perPage = intval($request->query('perPage', 20));
        if (!in_array($perPage, [10, 20, 30, 40, 50])) {
            $perPage = 20;
        }

        // Handle selectedRoles from either array or comma-separated string
        $selectedRoles = $request->input('selectedRoles', []);
        if (is_string($selectedRoles)) {
            $selectedRoles = explode(',', $selectedRoles);
        }

        // Allowed sort fields
        $allowedSorts = ['name', 'email', 'created_at', 'updated_at'];

        // Validate and sanitize sort parameter
        $sort = $request->query('sort');
        if ($sort && !in_array(ltrim($sort, '-'), $allowedSorts)) {
            // Remove invalid sort from query
            $request->query->remove('sort');
            $sort = null;
        }

        // Start query
        $usersQuery = QueryBuilder::for(User::class, $request)
            ->allowedFilters([
                AllowedFilter::partial('search', 'name'),
            ])
            ->allowedSorts($allowedSorts)
            ->with('roles');

        // Filter by roles if any selected
        if (!empty($selectedRoles)) {
            $usersQuery->whereHas('roles', function ($query) use ($selectedRoles) {
                $query->whereIn('name', $selectedRoles);
            });
        }

        // Paginate with query string
        $users = $usersQuery->paginate($perPage)->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'perPage' => $perPage,
            'search' => $request->input('search'),
            'sort' => $sort,
            'selectedRoles' => $selectedRoles,
            'roles' => Role::pluck('name'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Users/Create', [
            'roles' => Role::select('id', 'name')->get(),
        ]);
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ],
            'password' => ['required', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        sleep(1); // delay 1 second

        $newRoleIds = collect($validated['roles'] ?? [])->map(fn($id) => (int) $id)->sort()->values()->all();
        $oldRoleIds = [];

        if (!empty($newRoleIds)) {
            $user->syncRoles($newRoleIds);

            $added = array_diff($newRoleIds, $oldRoleIds);
            $addedNames = Role::whereIn('id', $added)->pluck('name')->toArray();

            if (!empty($addedNames)) {
                UserRolesUpdated::dispatch($user, $addedNames, [], auth()->user());
            }
        }

        return redirect()->route('users.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => $user->load('roles'),
            'roles' => Role::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // ✅ Validate input
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                "unique:users,email,{$user->id}",
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ],
            'password' => ['nullable', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        // ✅ Update user attributes
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // ✅ Sync roles if provided
        $newRoleIds = collect($validated['roles'] ?? [])->map(fn ($id) => (int) $id)->sort()->values()->all();
        $oldRoleIds = $user->roles->pluck('id')->sort()->values()->all();

        // Compare old and new role IDs
        if ($newRoleIds !== $oldRoleIds) {
            $user->syncRoles($newRoleIds);

            $addedRoleIds = array_diff($newRoleIds, $oldRoleIds);
            $removedRoleIds = array_diff($oldRoleIds, $newRoleIds);

            $addedRoles = Role::whereIn('id', $addedRoleIds)->pluck('name')->toArray();
            $removedRoles = Role::whereIn('id', $removedRoleIds)->pluck('name')->toArray();

            // 🔥 Fire event for listener to log
            UserRolesUpdated::dispatch($user, $addedRoles, $removedRoles, auth()->user());
        }

        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself (or specific protected users)
        if (auth()->id() === $user->id) {
            return back()->withErrors([
                'authorization' => 'You cannot delete your own account'
            ]);
        }

        // Prevent deleting users with the "super-admin" role
        if ($user->hasRole('super-admin')) {
            return back()->withErrors([
                'authorization' => 'You cannot delete a user with the super-admin role.',
            ]);
        }

        // Delete the user
        $user->delete();
    }
}
