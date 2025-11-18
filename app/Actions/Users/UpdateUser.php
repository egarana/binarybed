<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateUser
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(User $user, array $data): User
    {
        return tenancy()->central(function () use ($user, $data) {
            return DB::transaction(function () use ($user, $data) {
                if (!empty($data['password'])) {
                    $data['password'] = Hash::make($data['password']);
                }

                $user = $this->userRepository->update($user, $data);

                return $user->fresh();
            });
        });
    }
}