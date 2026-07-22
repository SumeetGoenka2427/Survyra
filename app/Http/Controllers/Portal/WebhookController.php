<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class WebhookController extends Controller
{
    public function __construct(private readonly WebhookService $webhooks) {}

    public function index(Request $request): View
    {
        return view('portal.integrations.webhooks', [
            'webhooks' => $this->webhooks->allForClient($request->user()->client),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['in:response.started,response.completed'],
            'secret' => ['nullable', 'string', 'max:255'],
        ]);

        $this->webhooks->create($request->user()->client, $data);

        return redirect()->route('portal.integrations.webhooks')->with('status', 'Webhook added.');
    }

    public function destroy(Request $request, Webhook $webhook): RedirectResponse
    {
        abort_if($webhook->client_id !== $request->user()->client_id, 403);
        $this->webhooks->delete($webhook);

        return redirect()->route('portal.integrations.webhooks')->with('status', 'Webhook deleted.');
    }
}
