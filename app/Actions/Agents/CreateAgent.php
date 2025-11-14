<?php

namespace App\Actions\Agents;

use App\Models\Agent;
use App\Models\User;
use App\Repositories\AgentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateAgent
{
    public function __construct(
        protected AgentRepository $agentRepository
    ) {}

    public function execute(array $data): Agent
    {
        return tenancy()->central(function () use ($data) {
            return DB::transaction(function () use ($data) {
                $user = $this->createUser($data);

                $this->assignAgentRole($user);

                $agentData = $this->prepareAgentData($data, $user->id);

                $agent = $this->agentRepository->create($agentData);

                return $agent->load('user');
            });
        });
    }

    /**
     * Create User account.
     *
     * @param  array  $data
     * @return User
     */
    protected function createUser(array $data): User
    {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Assign 'agent' role to user.
     * Creates the role if it doesn't exist.
     *
     * @param  User  $user
     * @return void
     */
    protected function assignAgentRole(User $user): void
    {
        $role = Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web']);

        $user->assignRole($role);
    }

    /**
     * Prepare agent data for creation.
     *
     * @param  array  $data
     * @param  int  $userId
     * @return array
     */
    protected function prepareAgentData(array $data, int $userId): array
    {
        $agentData = [
            'user_id' => $userId,
            'name'    => $data['name'],
            'email'   => $data['email'],
        ];

        return $agentData;
    }
}