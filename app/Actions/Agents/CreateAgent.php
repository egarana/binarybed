<?php

namespace App\Actions\Agents;

use App\Models\Agent;
use App\Repositories\AgentRepository;

class CreateAgent
{
    public function __construct(
        protected AgentRepository $agentRepository
    ) {}

    /**
     * Jalankan proses pembuatan agent baru.
     *
     * @param  array  $data
     * @return Agent
     */
    public function execute(array $data): Agent
    {
        return tenancy()->central(function () use ($data) {
            
        });
    }
}