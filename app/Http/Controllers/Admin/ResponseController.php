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
    /**
     * Columns a client may sort the responses table by — whitelisted to
     * avoid passing an arbitrary column name straight into orderBy().
     */
    private const SORTABLE_COLUMNS = ['started_at', 'status', 'score'];

    public function index(Request $request): JsonResponse
    {
        $client = $request->integer('client_id') ? Client::query()->find($request->integer('client_id')) : null;

        abort_if(! $client, 404);

        $surveyIds = $request->integer('survey_id')
            ? [$request->integer('survey_id')]
            : $client->surveys()->pluck('id');

        $to = $request->filled('to') ? Carbon::parse($request->string('to')->toString())->endOfDay() : now()->endOfDay();
        $from = $request->filled('from') ? Carbon::parse($request->string('from')->toString())->startOfDay() : $to->copy()->subDays(30)->startOfDay();

        $sort = in_array($request->string('sort')->toString(), self::SORTABLE_COLUMNS, true)
            ? $request->string('sort')->toString()
            : 'started_at';
        $direction = $request->string('dir')->toString() === 'asc' ? 'asc' : 'desc';

        $responses = SurveyResponse::query()
            ->whereIn('survey_id', $surveyIds)
            ->whereBetween('started_at', [$from, $to])
            ->when($request->filled('status'), fn ($q) => $request->string('status')->toString() === 'pending'
                ? $q->whereNotIn('status', ['completed', 'abandoned'])
                : $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search')->toString().'%';
                $q->where(function ($inner) use ($term) {
                    $inner->whereHas('survey', fn ($s) => $s->where('title', 'like', $term))
                        ->orWhereHas('contact', fn ($c) => $c->where('name', 'like', $term)->orWhere('email', 'like', $term))
                        ->orWhere('source', 'like', $term);
                });
            })
            ->with(['survey', 'contact'])
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return response()->json(['html' => view('analytics.responses-table', [
            'responses' => $responses,
            'sort' => $sort,
            'direction' => $direction,
        ])->render()]);
    }

    public function show(SurveyResponse $response): JsonResponse
    {
        $response->load(['survey', 'answers.question']);

        return response()->json(['html' => view('analytics.response-detail', ['response' => $response])->render()]);
    }
}
