<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SlackIntegration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class SlackIntegrationController extends Controller
{
    public function index(Request $request): View
    {
        $integration = SlackIntegration::where('client_id', $request->user()->client_id)->first();

        return view('portal.integrations.slack', [
            'integration' => $integration,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'webhook_url' => ['required', 'url', 'max:500'],
            'channel' => ['nullable', 'string', 'max:100'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['in:negative_feedback,response_completed,survey_published'],
        ]);

        SlackIntegration::updateOrCreate(
            ['client_id' => $request->user()->client_id],
            [
                'webhook_url' => $data['webhook_url'],
                'channel' => $data['channel'] ?? null,
                'events' => $data['events'],
                'is_active' => true,
            ]
        );

        return redirect()->route('portal.integrations.slack')
            ->with('status', 'Slack integration updated.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        SlackIntegration::where('client_id', $request->user()->client_id)->delete();

        return redirect()->route('portal.integrations.slack')
            ->with('status', 'Slack integration removed.');
    }
}