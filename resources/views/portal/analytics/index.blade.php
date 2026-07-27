<x-portal-layout title="Dashboard">
    @include('portal.partials.onboarding-checklist')
    <div
        id="analytics-app"
        data-data-url="{{ route('portal.analytics.data') }}"
        data-responses-url="{{ route('portal.analytics.responses.index') }}"
        data-response-show-url-template="{{ route('portal.analytics.responses.show', ['response' => '__ID__']) }}"
        data-reports-url="{{ route('portal.analytics.reports.index') }}"
        data-export-url-template="{{ route('portal.analytics.export', ['format' => '__FORMAT__']) }}"
    >
        @include('analytics.filters', [
            'clients' => null,
            'selectedClientId' => null,
            'surveys' => $surveys,
            'selectedSurveyId' => $survey?->id,
            'from' => $from,
            'to' => $to,
        ])

        <ul class="nav nav-tabs mb-3">
            <li class="nav-item"><button type="button" class="nav-link active" data-analytics-tab="dashboard">Dashboard</button></li>
            <li class="nav-item"><button type="button" class="nav-link" data-analytics-tab="responses">Responses</button></li>
            <li class="nav-item"><button type="button" class="nav-link" data-analytics-tab="reports">Scheduled Reports</button></li>
        </ul>

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
                @include('analytics.dashboard', ['snapshot' => $snapshot])
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
            $chartSeed = ['trend' => $snapshot['trend'], 'sentiment' => $snapshot['sentiment_counts']];
        @endphp
        <script id="analytics-initial-chart-data" type="application/json">
            @json($chartSeed)
        </script>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.52.0/dist/apexcharts.min.js"></script>
    <script src="{{ asset('assets/js/analytics.js') }}" defer></script>
</x-portal-layout>
