<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentRequest;
use App\Models\Agent;
use App\Services\AgentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgentController extends Controller
{
    public function __construct(
        protected AgentService $service
    ) {}

    public function index(Request $request): Response
    {
        $agents = $this->service->getAllPaginated($request);

        return Inertia::render('agents/Index', compact('agents'));
    }

    public function create(): Response
    {
        return Inertia::render('agents/Create');
    }

    public function store(StoreAgentRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->route('agents.index', ['sort' => '-created_at']);
    }

    public function edit(Agent $agent): Response
    {
        $agent = $agent->load('domains');

        return Inertia::render('agents/Edit', compact('agent'));
    }

    public function update(UpdateAgentRequest $request, Agent $agent)
    {
        $this->service->update($agent, $request->validated());

        return redirect()->route('agents.index', ['sort' => '-updated_at']);
    }

    public function destroy(Agent $agent)
    {
        $this->service->delete($agent);

        return redirect()->route('agents.index');
    }
}
