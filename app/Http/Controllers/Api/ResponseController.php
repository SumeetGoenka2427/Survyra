<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Response;
use App\Models\Survey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public function index(Request $request, Survey $survey): JsonResponse
    {
        $client = $request->attributes->get('api_client');
        abort_if($survey->client_id !== $client->id, 403);

        $responses = $survey->responses()
            ->where('status', 'completed')
            ->with('answers')
            ->latest('completed_at')
            ->paginate(50);

        return response()->json($responses);
    }

    public function show(Request $request, Response $response): JsonResponse
    {
        $client = $request->attributes->get('api_client');
        abort_if($response->client_id !== $client->id, 403);

        return response()->json($response->load('answers.question'));
    }
}
