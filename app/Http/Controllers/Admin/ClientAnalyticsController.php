<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\AnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ClientAnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analytics,
    ) {
    }

    public function show(Request $request, Client $client): View
    {
        $this->authorize('view', $client);

        [$from, $to] = $this->resolveRange($request);
        $data = $this->analytics->forClientDashboard($client, $from, $to);

        return view('admin.clients.analytics.index', [
            'client' => $client,
            'data' => $data,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function data(Request $request, Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        [$from, $to] = $this->resolveRange($request);
        $data = $this->analytics->forClientDashboard($client, $from, $to);

        return response()->json($data);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveRange(Request $request): array
    {
        $to = $request->filled('to') ? Carbon::parse($request->string('to')->toString())->endOfDay() : now()->endOfDay();
        $from = $request->filled('from') ? Carbon::parse($request->string('from')->toString())->startOfDay() : $to->copy()->subDays(30)->startOfDay();

        return [$from, $to];
    }
}