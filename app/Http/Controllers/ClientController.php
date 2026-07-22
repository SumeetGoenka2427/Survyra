<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\SubscriptionPlan;
use App\Services\ClientService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $clients)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Client::class);

        return view('admin.clients.index', [
            'clients' => $this->clients->paginate(15, $this->searchTerm($request), $this->statusFilter($request)),
            'stats' => $this->clients->stats(),
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        return response()->json(['html' => $this->renderFragment($request)]);
    }

    public function create(): View
    {
        $this->authorize('create', Client::class);

        return view('admin.clients.create', [
            'plans' => SubscriptionPlan::query()->where('is_active', true)->get(),
        ]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = $this->clients->create($request->validated(), $request->user()->id);

        return redirect()->route('admin.clients.edit', $client)
            ->with('status', 'Client created successfully.');
    }

    public function edit(Client $client): View
    {
        $this->authorize('update', $client);

        return view('admin.clients.edit', [
            'client' => $client,
            'plans' => SubscriptionPlan::query()->where('is_active', true)->get(),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->clients->update($client, $request->validated());

        return redirect()->route('admin.clients.edit', $client)
            ->with('status', 'Client updated successfully.');
    }

    public function toggleStatus(Client $client, Request $request): JsonResponse
    {
        $this->authorize('update', $client);

        $this->clients->toggleStatus($client);

        return response()->json(['html' => $this->renderFragment($request)]);
    }

    public function destroy(Client $client, Request $request): JsonResponse
    {
        $this->authorize('delete', $client);

        $this->clients->delete($client);

        return response()->json(['html' => $this->renderFragment($request)]);
    }

    private function renderFragment(Request $request): string
    {
        return view('admin.clients._fragment', [
            'clients' => $this->clients->paginate(15, $this->searchTerm($request), $this->statusFilter($request)),
            'stats' => $this->clients->stats(),
        ])->render();
    }

    private function searchTerm(Request $request): ?string
    {
        return $request->string('search')->toString() ?: null;
    }

    private function statusFilter(Request $request): ?string
    {
        return $request->string('status')->toString() ?: null;
    }
}
