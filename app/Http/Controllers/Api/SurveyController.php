<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $client = $request->attributes->get('api_client');

        $surveys = Survey::where('client_id', $client->id)
            ->where('status', 'published')
            ->select(['id', 'title', 'slug', 'layout', 'status', 'published_at'])
            ->latest()
            ->paginate(20);

        return response()->json($surveys);
    }

    public function show(Request $request, Survey $survey): JsonResponse
    {
        $client = $request->attributes->get('api_client');

        abort_if($survey->client_id !== $client->id, 403);

        return response()->json($survey->load('questions.questionType'));
    }
}
