<x-admin-layout title="Dashboard">
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <x-stat-card
                label="Total Clients"
                :value="$stats['total_clients']"
                icon="bi-buildings"
                color="primary"
                :trend="$stats['clients_trend']"
            />
        </div>
        <div class="col-md-4">
            <x-stat-card label="Active Clients" :value="$stats['active_clients']" icon="bi-check-circle" color="success" />
        </div>
        <div class="col-md-4">
            <x-stat-card label="Trial Clients" :value="$stats['trial_clients']" icon="bi-hourglass-split" color="warning" />
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <x-stat-card label="Published Surveys" :value="$stats['published_surveys']" icon="bi-clipboard-check" color="info" />
        </div>
        <div class="col-md-4">
            <x-stat-card
                label="Responses This Week"
                :value="$stats['responses_this_week']"
                icon="bi-inboxes"
                color="primary"
                :trend="$stats['responses_trend']"
            />
        </div>
        <div class="col-md-4">
            <x-stat-card label="Needs Attention (negative, 7d)" :value="$stats['negative_responses_this_week']" icon="bi-exclamation-triangle" color="danger" />
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 h-100" id="recent-clients-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>Recently Added Clients</strong>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" id="recent-clients-refresh" class="ds-icon-btn" title="Refresh">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                        @can('viewAny', \App\Models\Client::class)
                            <a href="{{ route('admin.clients.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
                        @endcan
                    </div>
                </div>
                <div id="recent-clients-body">
                    @include('admin.partials.recent-clients')
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 h-100" id="recent-responses-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>Recent Survey Responses</strong>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" id="recent-responses-refresh" class="ds-icon-btn" title="Refresh">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>
                <div id="recent-responses-body">
                    @include('admin.partials.recent-responses')
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                function wireRefresh(bodyId, buttonId, url) {
                    const body = document.getElementById(bodyId);
                    const refreshBtn = document.getElementById(buttonId);
                    if (!body || !refreshBtn) return;

                    refreshBtn.addEventListener('click', function () {
                        const icon = refreshBtn.querySelector('i');
                        icon.classList.add('spin');
                        refreshBtn.disabled = true;

                        fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
                            .then((response) => response.json())
                            .then((data) => {
                                body.innerHTML = data.html;
                                Toast.success('Dashboard refreshed');
                            })
                            .catch(() => Toast.error('Could not refresh right now'))
                            .finally(() => {
                                icon.classList.remove('spin');
                                refreshBtn.disabled = false;
                            });
                    });
                }

                wireRefresh('recent-clients-body', 'recent-clients-refresh', @json(route('admin.dashboard.recent-clients')));
                wireRefresh('recent-responses-body', 'recent-responses-refresh', @json(route('admin.dashboard.recent-responses')));
            })();
        </script>
        <style>
            #recent-clients-refresh .spin, #recent-responses-refresh .spin { animation: dsSpin 0.6s linear; }
            @keyframes dsSpin { to { transform: rotate(360deg); } }
        </style>
    @endpush
</x-admin-layout>
