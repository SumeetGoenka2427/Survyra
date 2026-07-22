<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Response as SurveyResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ResponseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $client = $request->integer('client_id') ? Client::query()->find($request->integer('client_id')) : null;

        abort_if(! $client, 404);

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

    public function show(SurveyResponse $response): JsonResponse
    {
        $response->load(['survey', 'answers.question']);

        return response()->json(['html' => view('analytics.response-detail', ['response' => $response])->render()]);
    }
}
