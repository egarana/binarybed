<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
                $data['global_id'] = Str::uuid()->toString();

                $user = $this->userRepository->create($data);

                return $user;
            });
        });
    }
}