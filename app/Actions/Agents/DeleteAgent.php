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

    public function execute(Agent $agent): void
    {
        tenancy()->central(function () use ($agent) {
            DB::transaction(function () use ($agent) {
                $agent->load('user');
                $user = $agent->user;

                $this->agentRepository->delete($agent);

                if ($user) {
                    $user->delete();
                }
            });
        });
    }
}