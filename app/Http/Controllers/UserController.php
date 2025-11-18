<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        protected UserService $service
    ) {}

    public function index(Request $request): Response
    {
        $users = $this->service->getAllPaginated($request);

        return Inertia::render('users/Index', compact('users'));
    }

    public function create(): Response
    {
        return Inertia::render('users/Create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('users.index', ['sort' => '-created_at']);
    }

    public function edit(User $user): Response
    {
        $user = $this->service->getForEdit($user);
        
        return Inertia::render('users/Edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->service->update($user, $request->validated());

        return redirect()->route('users.index', ['sort' => '-updated_at']);
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->service->delete($user);

        return redirect()->route('users.index');
    }
}
