<?php

namespace App\Services;

use App\Actions\Agents\CreateAgent;
use App\Actions\Agents\DeleteAgent;
use App\Actions\Agents\UpdateAgent;
use App\Models\Agent;
use App\Repositories\AgentRepository;
use Illuminate\Http\Request;

class AgentService
{
    public function __construct(
        protected AgentRepository $repository,
        protected CreateAgent $createAgent,
        protected UpdateAgent $updateAgent,
        protected DeleteAgent $deleteAgent,
    ) {}

    public function getAllPaginated(Request $request)
    {
        return $this->repository->getAllPaginated($request);
    }

    public function getForEdit(Agent $agent): array
    {
        $agent = $this->repository->getForEdit($agent);
        
        return [
            'id' => $agent->id,
            'code' => $agent->code,
            'name' => $agent->name,
            'email' => $agent->email,
            'user' => [
                'id' => $agent->user->id,
                'name' => $agent->user->name,
                'email' => $agent->user->email,
            ],
        ];
    }

    public function create(array $data): Agent
    {
        return $this->createAgent->execute($data);
    }

    public function update(Agent $agent, array $data): Agent
    {
        return $this->updateAgent->execute($agent, $data);
    }

    public function delete(Agent $agent): void
    {
        $this->deleteAgent->execute($agent);
    }
}
