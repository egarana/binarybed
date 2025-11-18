<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteUser
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(User $user): void
    {
        tenancy()->central(function () use ($user) {
            DB::transaction(function () use ($user) {
                $this->userRepository->delete($user);
            });
        });
    }
}