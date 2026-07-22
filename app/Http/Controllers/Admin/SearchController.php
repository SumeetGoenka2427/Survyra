<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $term = trim((string) $request->string('q'));
        $user = $request->user();

        if (mb_strlen($term) < 2) {
            return response()->json(['groups' => []]);
        }

        $groups = [];

        if ($user->can('viewAny', Client::class)) {
            $clients = Client::query()
                ->where('company_name', 'like', "%{$term}%")
                ->limit(5)
                ->get(['id', 'company_name']);

            if ($clients->isNotEmpty()) {
                $groups[] = [
                    'label' => 'Clients',
                    'icon' => 'bi-buildings',
                    'items' => $clients->map(fn (Client $client) => [
                        'title' => $client->company_name,
                        'url' => route('admin.clients.edit', $client),
                    ]),
                ];
            }
        }

        if ($user->can('manage-surveys')) {
            $surveys = Survey::query()
                ->where('title', 'like', "%{$term}%")
                ->limit(5)
                ->get(['id', 'title']);

            if ($surveys->isNotEmpty()) {
                $groups[] = [
                    'label' => 'Surveys',
                    'icon' => 'bi-ui-checks',
                    'items' => $surveys->map(fn (Survey $survey) => [
                        'title' => $survey->title,
                        'url' => route('admin.surveys.edit', $survey),
                    ]),
                ];
            }

            $templates = SurveyTemplate::query()
                ->where('name', 'like', "%{$term}%")
                ->limit(5)
                ->get(['id', 'name']);

            if ($templates->isNotEmpty()) {
                $groups[] = [
                    'label' => 'Templates',
                    'icon' => 'bi-clipboard-data',
                    'items' => $templates->map(fn (SurveyTemplate $template) => [
                        'title' => $template->name,
                        'url' => route('admin.templates.edit', $template),
                    ]),
                ];
            }
        }

        if ($user->can('send-campaigns')) {
            $campaigns = Campaign::query()
                ->where('name', 'like', "%{$term}%")
                ->limit(5)
                ->get(['id', 'name']);

            if ($campaigns->isNotEmpty()) {
                $groups[] = [
                    'label' => 'Campaigns',
                    'icon' => 'bi-megaphone',
                    'items' => $campaigns->map(fn (Campaign $campaign) => [
                        'title' => $campaign->name,
                        'url' => route('admin.campaigns.show', $campaign),
                    ]),
                ];
            }
        }

        return response()->json(['groups' => $groups]);
    }
}
