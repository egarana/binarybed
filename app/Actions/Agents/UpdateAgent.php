<?php

namespace App\Actions\Agents;

use App\Models\Agent;
use App\Repositories\AgentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateAgent
{
    public function __construct(
        protected AgentRepository $agentRepository
    ) {}

    public function execute(Agent $agent, array $data): Agent
    {
        return tenancy()->central(function () use ($agent, $data) {
            return DB::transaction(function () use ($agent, $data) {
                $agent->load('user');

                $this->updateUser($agent, $data);

                $agentData = $this->prepareAgentData($data);

                $agent = $this->agentRepository->update($agent, $agentData);

                return $agent->fresh()->load('user');
            });
        });
    }

    /**
     * Update user account data.
     *
     * @param  Agent  $agent
     * @param  array  $data
     * @return void
     */
    protected function updateUser(Agent $agent, array $data): void
    {
        $userData = [
            'name'  => $data['name'],
            'email' => $data['email'],
        ];

        // Only update password if provided
        if (!empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        $agent->user->update($userData);
    }

    /**
     * Prepare agent data for update.
     *
     * @param  array  $data
     * @return array
     */
    protected function prepareAgentData(array $data): array
    {
        return [
            'name'  => $data['name'],
            'email' => $data['email'],
        ];
    }
}