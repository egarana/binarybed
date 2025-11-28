<?php

namespace App\Services;

use App\Actions\Users\CreateUser;
use App\Actions\Users\DeleteUser;
use App\Actions\Users\UpdateUser;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class UserService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected CreateUser $createUser,
        protected UpdateUser $updateUser,
        protected DeleteUser $deleteUser,
    ) {}

    public function getAllPaginated(Request $request)
    {
        return $this->userRepository->getAllPaginated($request);
    }

    public function getForEdit(User $user): array
    {
        $user = $this->userRepository->getForEdit($user);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    public function search(?string $search = null, int $limit = 5): array
    {
        return $this->userRepository->search($search, $limit);
    }

    public function create(array $data): User
    {
        return $this->createUser->execute($data);
    }

    public function update(User $user, array $data): User
    {
        return $this->updateUser->execute($user, $data);
    }

    public function delete(User $user): void
    {
        $this->deleteUser->execute($user);
    }
}
