<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Response as SurveyResponse;
use App\Models\Survey;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.dashboard', [
            'stats' => $this->stats(),
            'recentClients' => $this->recentClients(),
            'recentResponses' => $this->recentResponses(),
        ]);
    }

    public function recentClientsFragment(Request $request): JsonResponse
    {
        return response()->json([
            'html' => view('admin.partials.recent-clients', ['recentClients' => $this->recentClients()])->render(),
        ]);
    }

    public function recentResponsesFragment(Request $request): JsonResponse
    {
        return response()->json([
            'html' => view('admin.partials.recent-responses', ['recentResponses' => $this->recentResponses()])->render(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function stats(): array
    {
        $thisWeek = Client::query()->where('created_at', '>=', now()->subDays(7))->count();
        $lastWeek = Client::query()->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();

        $responsesThisWeek = SurveyResponse::query()->where('completed_at', '>=', now()->subDays(7))->count();
        $responsesLastWeek = SurveyResponse::query()->whereBetween('completed_at', [now()->subDays(14), now()->subDays(7)])->count();

        return [
            'total_clients' => Client::query()->count(),
            'active_clients' => Client::query()->where('status', 'active')->count(),
            'trial_clients' => Client::query()->where('status', 'trial')->count(),
            'clients_trend' => $this->trend($thisWeek, $lastWeek),
            'published_surveys' => Survey::query()->where('status', 'published')->count(),
            'total_surveys' => Survey::query()->count(),
            'responses_this_week' => $responsesThisWeek,
            'responses_trend' => $this->trend($responsesThisWeek, $responsesLastWeek),
            'negative_responses_this_week' => SurveyResponse::query()
                ->where('sentiment', 'negative')
                ->where('completed_at', '>=', now()->subDays(7))
                ->count(),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Client>
     */
    private function recentClients()
    {
        return Client::query()->latest()->limit(5)->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, SurveyResponse>
     */
    private function recentResponses()
    {
        return SurveyResponse::query()
            ->whereNotNull('completed_at')
            ->with(['client', 'survey'])
            ->latest('completed_at')
            ->limit(5)
            ->get();
    }

    /**
     * @return array{direction: string, value: string}
     */
    private function trend(int $current, int $previous): array
    {
        if ($previous === 0) {
            return $current > 0
                ? ['direction' => 'up', 'value' => "+{$current} this week"]
                : ['direction' => 'flat', 'value' => 'No change'];
        }

        $change = round((($current - $previous) / $previous) * 100);

        return [
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'flat'),
            'value' => ($change > 0 ? '+' : '').$change.'% vs last week',
        ];
    }
}
