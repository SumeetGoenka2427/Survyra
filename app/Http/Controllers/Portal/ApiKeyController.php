<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Services\ApiKeyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class ApiKeyController extends Controller
{
    public function __construct(private readonly ApiKeyService $keys) {}

    public function index(Request $request): View
    {
        return view('portal.integrations.api-keys', [
            'keys' => $this->keys->allForClient($request->user()->client),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:100']]);

        $result = $this->keys->create($request->user()->client, $request->input('name'));

        return redirect()->route('portal.integrations.api-keys')
            ->with('new_key', $result['plain'])
            ->with('status', 'API key created. Copy it now — it will not be shown again.');
    }

    public function destroy(Request $request, ApiKey $apiKey): RedirectResponse
    {
        abort_if($apiKey->client_id !== $request->user()->client_id, 403);
        $this->keys->delete($apiKey);

        return redirect()->route('portal.integrations.api-keys')->with('status', 'API key deleted.');
    }
}
