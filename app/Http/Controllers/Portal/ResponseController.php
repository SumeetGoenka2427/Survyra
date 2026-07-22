<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Response as SurveyResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ResponseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        $surveyIds = $request->integer('survey_id')
            ? [$request->integer('survey_id')]
            : $client->surveys()->pluck('id');

        $to = $request->filled('to') ? Carbon::parse($request->string('to')->toString())->endOfDay() : now()->endOfDay();
        $from = $request->filled('from') ? Carbon::parse($request->string('from')->toString())->startOfDay() : $to->copy()->subDays(30)->startOfDay();

        $responses = SurveyResponse::query()
            ->whereIn('survey_id', $surveyIds)
            ->whereBetween('started_at', [$from, $to])
            ->with('survey')
            ->latest('started_at')
            ->paginate(15)
            ->withQueryString();

        return response()->json(['html' => view('analytics.responses-table', ['responses' => $responses])->render()]);
    }

    public function show(Request $request, SurveyResponse $response): JsonResponse
    {
        abort_unless($response->client_id === $request->user()->client_id, 404);

        $response->load(['survey', 'answers.question']);

        return response()->json(['html' => view('analytics.response-detail', ['response' => $response])->render()]);
    }
}
