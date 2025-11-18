<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateUser
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(array $data): User
    {
        return tenancy()->central(function () use ($data) {
            return DB::transaction(function () use ($data) {
                $user = $this->userRepository->create($data);

                return $user;
            });
        });
    }

    /**
     * Assign 'user' role to user.
     * Creates the role if it doesn't exist.
     *
     * @param  User  $user
     * @return void
     */
    protected function assignUserRole(User $user): void
    {
        $role = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $user->assignRole($role);
    }
}