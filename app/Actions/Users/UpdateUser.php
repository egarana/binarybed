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
                $userData = $this->prepareUserData($data);

                return $this->userRepository->update($user, $userData);
            });
        });
    }

    /**
     * Prepare user data for update.
     *
     * @param  array  $data
     * @return array
     */
    protected function prepareUserData(array $data): array
    {
        $userData = [
            'name'  => $data['name'],
            'email' => $data['email'],
        ];

        // Only update password if provided
        if (!empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        return $userData;
    }
}