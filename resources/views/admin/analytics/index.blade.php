<x-admin-layout title="Analytics">
    <div
        id="analytics-app"
        data-data-url="{{ route('admin.analytics.data') }}"
        data-responses-url="{{ route('admin.analytics.responses.index') }}"
        data-response-show-url-template="{{ route('admin.analytics.responses.show', ['response' => '__ID__']) }}"
        data-reports-url="{{ route('admin.analytics.reports.index') }}"
        data-export-url-template="{{ route('admin.analytics.export', ['format' => '__FORMAT__']) }}"
    >
        @include('analytics.filters', [
            'clients' => $clients,
            'selectedClientId' => $client?->id,
            'surveys' => $surveys,
            'selectedSurveyId' => $survey?->id,
            'from' => $from,
            'to' => $to,
        ])

        <div class="d-flex align-items-center gap-2 mb-3">
            <ul class="nav nav-tabs mb-0 flex-grow-1">
                <li class="nav-item"><button type="button" class="nav-link active" data-analytics-tab="dashboard">Dashboard</button></li>
                <li class="nav-item"><button type="button" class="nav-link" data-analytics-tab="responses">Responses</button></li>
                <li class="nav-item"><button type="button" class="nav-link" data-analytics-tab="reports">Scheduled Reports</button></li>
            </ul>
            <button id="analytics-live-toggle" class="btn btn-sm btn-outline-success">Stop Live</button>
            <span id="analytics-live-indicator" class="badge text-bg-success"><span class="spinner-grow spinner-grow-sm me-1"></span>Live</span>
        </div>

        <div data-analytics-pane="dashboard">
            <div class="row g-3 mb-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white"><strong>Response Volume</strong></div>
                        <div class="card-body"><div id="analytics-trend-chart"></div></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white"><strong>Sentiment</strong></div>
                        <div class="card-body"><div id="analytics-sentiment-chart"></div></div>
                    </div>
                </div>
            </div>

            <div id="analytics-dashboard-fragment">
                @if ($snapshot)
                    @include('analytics.dashboard', ['snapshot' => $snapshot])
                @else
                    <div class="text-center text-muted py-5">No client selected.</div>
                @endif
            </div>
        </div>

        <div data-analytics-pane="responses" class="d-none">
            <div id="analytics-responses-fragment">
                <div class="text-center text-muted py-5">
                    <div class="spinner-border spinner-border-sm"></div> Loading…
                </div>
            </div>
        </div>

        <div data-analytics-pane="reports" class="d-none">
            <div id="analytics-reports-fragment">
                <div class="text-center text-muted py-5">
                    <div class="spinner-border spinner-border-sm"></div> Loading…
                </div>
            </div>
        </div>

        <div class="modal fade" id="responseModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content" id="responseModalContent"></div>
            </div>
        </div>

        @php
            $chartSeed = [
                'trend' => $snapshot['trend'] ?? ['labels' => [], 'series' => []],
                'sentiment' => $snapshot['sentiment_counts'] ?? ['positive' => 0, 'neutral' => 0, 'negative' => 0],
            ];
        @endphp
        <script id="analytics-initial-chart-data" type="application/json">
            @json($chartSeed)
        </script>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.52.0/dist/apexcharts.min.js"></script>
    <script src="{{ asset('assets/js/analytics.js') }}" defer></script>
</x-admin-layout>
