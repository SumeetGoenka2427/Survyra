<x-admin-layout title="Dashboard">
    <div class="row g-3 mb-4">
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

    <div class="card border-0" id="recent-clients-card">
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

    @push('scripts')
        <script>
            (function () {
                const body = document.getElementById('recent-clients-body');
                const refreshBtn = document.getElementById('recent-clients-refresh');
                const url = @json(route('admin.dashboard.recent-clients'));

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
            })();
        </script>
        <style>
            #recent-clients-refresh .spin { animation: dsSpin 0.6s linear; }
            @keyframes dsSpin { to { transform: rotate(360deg); } }
        </style>
    @endpush
</x-admin-layout>
