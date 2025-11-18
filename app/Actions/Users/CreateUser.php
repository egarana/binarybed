<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUser
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function execute(array $data): User
    {
        return tenancy()->central(function () use ($data) {
            return DB::transaction(function () use ($data) {
                $data['password'] = Hash::make($data['password']);

                $user = $this->userRepository->create($data);

                return $user;
            });
        });
    }
}