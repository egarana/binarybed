<?php

namespace App\Actions\Agents;

use App\Models\Agent;
use App\Repositories\AgentRepository;

class DeleteAgent
{
    public function __construct(
        protected AgentRepository $agentRepository
    ) {}

    public function execute(Agent $agent): void
    {

    }
}