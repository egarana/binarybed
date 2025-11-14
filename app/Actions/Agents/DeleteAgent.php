<?php

namespace App\Actions\Agents;

use App\Models\Agent;
use App\Repositories\AgentRepository;
use Illuminate\Support\Facades\DB;

class DeleteAgent
{
    public function __construct(
        protected AgentRepository $agentRepository
    ) {}

    /**
     * Delete an agent and its associated user.
     *
     * This method performs the following steps:
     * 1. Loads the agent with its user relationship
     * 2. Deletes the agent record
     * 3. Deletes the associated user (if exists)
     *
     * All operations are wrapped in a database transaction to ensure data integrity.
     *
     * @param  Agent  $agent
     * @return void
     */
    public function execute(Agent $agent): void
    {
        tenancy()->central(function () use ($agent) {
            DB::transaction(function () use ($agent) {
                // Load the user relationship before deletion
                $agent->load('user');
                $user = $agent->user;

                // Delete the agent first
                $this->agentRepository->delete($agent);

                // Delete the associated user if exists
                if ($user) {
                    $user->delete();
                }
            });
        });
    }
}