<x-admin-layout title="{{ $client->company_name }} Analytics">
    <div class="ds-fade-in">
        <!-- Breadcrumb area -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('admin.clients.index') }}" class="text-decoration-none">Clients</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $client->company_name }}</li>
                    </ol>
                </nav>
                <h4 class="mb-0 fw-bold">{{ $client->company_name }}</h4>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-pencil"></i> Edit Client
                </a>
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-download"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="#">PDF Report</a></li>
                        <li><a class="dropdown-item" href="#">Excel</a></li>
                        <li><a class="dropdown-item" href="#">CSV</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex flex-wrap align-items-center gap-3">
                <div>
                    <label class="form-label small text-muted mb-1">From</label>
                    <input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control form-control-sm" style="width: 160px;" id="analytics-from">
                </div>
                <div>
                    <label class="form-label small text-muted mb-1">To</label>
                    <input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control form-control-sm" style="width: 160px;" id="analytics-to">
                </div>
                <div class="btn-group btn-group-sm align-self-end">
                    <button type="button" class="btn btn-outline-secondary" data-preset-days="7">7d</button>
                    <button type="button" class="btn btn-outline-secondary" data-preset-days="30">30d</button>
                    <button type="button" class="btn btn-outline-secondary" data-preset-days="90">90d</button>
                    <button type="button" class="btn btn-outline-secondary" data-preset-days="365">1y</button>
                </div>
                <button class="btn btn-primary btn-sm align-self-end" id="analytics-apply">
                    <i class="bi bi-arrow-clockwise"></i> Apply
                </button>
            </div>
        </div>

        <!-- Client Profile Summary -->
        @include('admin.clients.analytics.partials.profile-summary')

        <!-- KPI Summary Cards -->
        @include('admin.clients.analytics.partials.kpi-cards')

        <!-- Main Charts Row -->
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <span class="fw-semibold"><i class="bi bi-graph-up me-2 text-primary"></i>Response Trend</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark">Daily</span>
                            <span class="badge bg-light text-dark">Weekly</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="client-trend-chart" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <span class="fw-semibold"><i class="bi bi-emoji-smile me-2 text-primary"></i>Sentiment Distribution</span>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div id="client-sentiment-chart" style="min-height: 300px; width: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Row: Devices, Sources, Countries -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <span class="fw-semibold"><i class="bi bi-phone me-2 text-primary"></i>Devices</span>
                    </div>
                    <div class="card-body">
                        <div id="client-devices-chart" style="min-height: 250px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <span class="fw-semibold"><i class="bi bi-globe me-2 text-primary"></i>Sources</span>
                    </div>
                    <div class="card-body">
                        <div id="client-sources-chart" style="min-height: 250px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <span class="fw-semibold"><i class="bi bi-geo-alt me-2 text-primary"></i>Top Countries</span>
                    </div>
                    <div class="card-body">
                        <div id="client-countries-chart" style="min-height: 250px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Third Row: Hourly & Day of Week -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <span class="fw-semibold"><i class="bi bi-clock me-2 text-primary"></i>Hourly Distribution</span>
                    </div>
                    <div class="card-body">
                        <div id="client-hourly-chart" style="min-height: 200px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <span class="fw-semibold"><i class="bi bi-calendar-week me-2 text-primary"></i>Day of Week</span>
                    </div>
                    <div class="card-body">
                        <div id="client-dayofweek-chart" style="min-height: 200px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Survey Performance Table -->
        @include('admin.clients.analytics.partials.survey-performance')

        <!-- Completion Stats & NPS Breakdown -->
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <span class="fw-semibold"><i class="bi bi-check2-circle me-2 text-primary"></i>Completion Stats</span>
                    </div>
                    <div class="card-body">
                        <div id="client-completion-chart" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <span class="fw-semibold"><i class="bi bi-pie-chart me-2 text-primary"></i>NPS Breakdown</span>
                    </div>
                    <div class="card-body">
                        @if (!empty($data['metrics']['nps']))
                            <div id="client-nps-chart" style="min-height: 300px;"></div>
                        @else
                            <div class="d-flex align-items-center justify-content-center" style="min-height: 300px;">
                                <div class="text-center text-muted">
                                    <i class="bi bi-bar-chart display-4 d-block mb-2"></i>
                                    <p class="mb-0">No NPS data available in this period.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Clicks -->
        @if (array_sum($data['review_clicks']) > 0)
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent">
                        <span class="fw-semibold"><i class="bi bi-hand-index-thumb me-2 text-primary"></i>Review & Action Clicks</span>
                    </div>
                    <div class="card-body">
                        <div id="client-review-clicks-chart" style="min-height: 200px;"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Activity -->
        @include('admin.clients.analytics.partials.recent-activity')
    </div>

    @php
        $chartData = [
            'trend' => $data['trend'],
            'weekly_trend' => $data['weekly_trend'],
            'sentiment' => $data['sentiment'],
            'devices' => $data['devices']->toArray(),
            'sources' => $data['sources']->toArray(),
            'countries' => $data['countries']->toArray(),
            'hourly' => $data['hourly_distribution'],
            'day_of_week' => $data['day_of_week_distribution'],
            'completion_stats' => $data['completion_stats']->toArray(),
            'metrics' => $data['metrics'],
            'review_clicks' => $data['review_clicks'],
            'summary' => $data['summary'],
        ];
    @endphp

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.52.0/dist/apexcharts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = @json($chartData);
            
            // Color palette
            const colors = {
                primary: '#4f46e5',
                success: '#10b981',
                warning: '#f59e0b',
                danger: '#f43f5e',
                info: '#6366f1',
                purple: '#8b5cf6',
                slate: ['#6366f1', '#8b5cf6', '#a78bfa', '#c4b5fd', '#ddd6fe', '#475569', '#64748b', '#94a3b8', '#cbd5e1', '#e2e8f0'],
            };

            // 1. Trend Chart (Area)
            const trendOptions = {
                series: [{
                    name: 'Responses',
                    data: chartData.trend.series
                }],
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: { show: false },
                    zoom: { enabled: true },
                    fontFamily: 'Inter, sans-serif',
                    foreColor: '#64748b',
                },
                colors: [colors.primary],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2.5 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.35,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },
                xaxis: {
                    categories: chartData.trend.labels,
                    labels: { show: true, rotate: -45, offsetY: 5 },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: {
                    labels: { show: true },
                    forceNiceScale: true,
                },
                grid: {
                    borderColor: '#e2e8f0',
                    strokeDashArray: 4,
                    xaxis: { lines: { show: false } }
                },
                tooltip: {
                    theme: 'light',
                    y: { formatter: (val) => `${val} responses` }
                },
                legend: { show: false }
            };

            if (document.getElementById('client-trend-chart')) {
                new ApexCharts(document.getElementById('client-trend-chart'), trendOptions).render();
            }

            // 2. Sentiment Donut Chart
            const sentimentTotal = chartData.sentiment.positive + chartData.sentiment.neutral + chartData.sentiment.negative;
            const sentimentOptions = {
                series: [chartData.sentiment.positive, chartData.sentiment.neutral, chartData.sentiment.negative],
                chart: {
                    type: 'donut',
                    height: 300,
                    fontFamily: 'Inter, sans-serif',
                    foreColor: '#64748b',
                },
                colors: [colors.success, colors.warning, colors.danger],
                labels: sentimentTotal > 0 
                    ? [`Positive (${chartData.sentiment.positive})`, `Neutral (${chartData.sentiment.neutral})`, `Negative (${chartData.sentiment.negative})`]
                    : ['No Data'],
                dataLabels: { enabled: false },
                legend: { position: 'bottom', fontSize: '12px' },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: () => sentimentTotal.toString()
                                }
                            }
                        }
                    }
                },
                tooltip: {
                    y: { formatter: (val) => `${val} responses` }
                },
                responsive: [{
                    breakpoint: 480,
                    options: { chart: { height: 250 }, legend: { position: 'bottom' } }
                }]
            };

            if (document.getElementById('client-sentiment-chart')) {
                new ApexCharts(document.getElementById('client-sentiment-chart'), sentimentOptions).render();
            }

            // 3. Devices Donut Chart
            const deviceData = chartData.devices;
            const deviceLabels = Object.keys(deviceData);
            const deviceValues = Object.values(deviceData);
            if (deviceLabels.length > 0 && document.getElementById('client-devices-chart')) {
                new ApexCharts(document.getElementById('client-devices-chart'), {
                    series: deviceValues,
                    chart: { type: 'donut', height: 250, fontFamily: 'Inter, sans-serif', foreColor: '#64748b' },
                    labels: deviceLabels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
                    colors: [colors.primary, colors.success, colors.warning, colors.info, colors.purple],
                    dataLabels: { enabled: false },
                    legend: { position: 'bottom', fontSize: '11px', itemMargin: { horizontal: 8 } },
                    plotOptions: {
                        pie: { donut: { size: '60%', labels: { show: false } } }
                    },
                    tooltip: { y: { formatter: (val) => `${val} responses` } }
                }).render();
            } else if (document.getElementById('client-devices-chart')) {
                document.getElementById('client-devices-chart').innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-table display-4 d-block mb-2"></i><p class="mb-0 small">No device data</p></div>';
            }

            // 4. Sources Pie Chart
            const sourceData = chartData.sources;
            const sourceLabels = Object.keys(sourceData);
            const sourceValues = Object.values(sourceData);
            if (sourceLabels.length > 0 && document.getElementById('client-sources-chart')) {
                new ApexCharts(document.getElementById('client-sources-chart'), {
                    series: sourceValues,
                    chart: { type: 'pie', height: 250, fontFamily: 'Inter, sans-serif', foreColor: '#64748b' },
                    labels: sourceLabels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
                    colors: [colors.primary, colors.info, colors.success, colors.warning, colors.danger, colors.purple],
                    dataLabels: { enabled: false },
                    legend: { position: 'bottom', fontSize: '11px', itemMargin: { horizontal: 8 } },
                    tooltip: { y: { formatter: (val) => `${val} responses` } }
                }).render();
            } else if (document.getElementById('client-sources-chart')) {
                document.getElementById('client-sources-chart').innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-diagram-3 display-4 d-block mb-2"></i><p class="mb-0 small">No source data</p></div>';
            }

            // 5. Countries Bar Chart
            const countryData = chartData.countries;
            const countryLabels = Object.keys(countryData);
            const countryValues = Object.values(countryData);
            if (countryLabels.length > 0 && document.getElementById('client-countries-chart')) {
                new ApexCharts(document.getElementById('client-countries-chart'), {
                    series: [{ name: 'Responses', data: countryValues }],
                    chart: { type: 'bar', height: 250, fontFamily: 'Inter, sans-serif', foreColor: '#64748b', toolbar: { show: false } },
                    colors: [colors.info],
                    plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '60%' } },
                    dataLabels: { enabled: false },
                    xaxis: { categories: countryLabels, labels: { show: true } },
                    grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
                    tooltip: { y: { formatter: (val) => `${val} responses` } }
                }).render();
            } else if (document.getElementById('client-countries-chart')) {
                document.getElementById('client-countries-chart').innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-geo display-4 d-block mb-2"></i><p class="mb-0 small">No country data</p></div>';
            }

            // 6. Hourly Distribution Chart
            if (document.getElementById('client-hourly-chart')) {
                new ApexCharts(document.getElementById('client-hourly-chart'), {
                    series: [{ name: 'Responses', data: chartData.hourly.series }],
                    chart: { type: 'bar', height: 200, fontFamily: 'Inter, sans-serif', foreColor: '#64748b', toolbar: { show: false } },
                    colors: [colors.primary],
                    plotOptions: { bar: { borderRadius: 2, columnWidth: '70%' } },
                    dataLabels: { enabled: false },
                    xaxis: {
                        categories: chartData.hourly.labels,
                        labels: { rotate: -45, offsetY: 5, fontSize: '10px' },
                        tickAmount: 24,
                    },
                    grid: { borderColor: '#e2e8f0', strokeDashArray: 4, xaxis: { lines: { show: false } } },
                    tooltip: { y: { formatter: (val) => `${val} responses` } }
                }).render();
            }

            // 7. Day of Week Chart
            if (document.getElementById('client-dayofweek-chart')) {
                new ApexCharts(document.getElementById('client-dayofweek-chart'), {
                    series: [{ name: 'Responses', data: chartData.day_of_week.series }],
                    chart: { type: 'bar', height: 200, fontFamily: 'Inter, sans-serif', foreColor: '#64748b', toolbar: { show: false } },
                    colors: [colors.success],
                    plotOptions: { bar: { borderRadius: 4, columnWidth: '60%', distributed: false } },
                    dataLabels: { enabled: false },
                    xaxis: {
                        categories: chartData.day_of_week.labels,
                        labels: { rotate: -20, fontSize: '10px' },
                    },
                    grid: { borderColor: '#e2e8f0', strokeDashArray: 4, xaxis: { lines: { show: false } } },
                    tooltip: { y: { formatter: (val) => `${val} responses` } }
                }).render();
            }

            // 8. Completion Stats Stacked Bar
            const completionStats = chartData.completion_stats || [];
            if (completionStats.length > 0 && document.getElementById('client-completion-chart')) {
                const compLabels = completionStats.map(s => s.title.length > 25 ? s.title.substring(0, 25) + '...' : s.title);
                new ApexCharts(document.getElementById('client-completion-chart'), {
                    series: [
                        { name: 'Completed', data: completionStats.map(s => s.completed) },
                        { name: 'Abandoned', data: completionStats.map(s => s.abandoned) },
                        { name: 'Pending', data: completionStats.map(s => s.pending) },
                    ],
                    chart: { type: 'bar', height: 300, stacked: true, fontFamily: 'Inter, sans-serif', foreColor: '#64748b', toolbar: { show: false } },
                    colors: [colors.success, colors.danger, colors.warning],
                    plotOptions: { bar: { borderRadius: 2, horizontal: true, barHeight: '50%' } },
                    dataLabels: { enabled: false },
                    xaxis: { categories: compLabels, labels: { fontSize: '11px' } },
                    grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
                    tooltip: { y: { formatter: (val) => `${val} responses` } },
                    legend: { position: 'bottom', fontSize: '12px', itemMargin: { horizontal: 12 } }
                }).render();
            }

            // 9. NPS Breakdown
            const npsData = chartData.metrics.nps;
            if (npsData && document.getElementById('client-nps-chart')) {
                new ApexCharts(document.getElementById('client-nps-chart'), {
                    series: [npsData.promoter_pct || 0, npsData.passive_pct || 0, npsData.detractor_pct || 0],
                    chart: { type: 'donut', height: 300, fontFamily: 'Inter, sans-serif', foreColor: '#64748b' },
                    labels: [`Promoters (${npsData.promoters})`, `Passives (${npsData.passives})`, `Detractors (${npsData.detractors})`],
                    colors: [colors.success, colors.warning, colors.danger],
                    dataLabels: { enabled: false },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '60%',
                                labels: {
                                    show: true,
                                    name: { show: true, fontSize: '14px' },
                                    value: { show: true, fontSize: '16px', formatter: () => `${npsData.value}` },
                                    total: { show: true, label: 'NPS Score', formatter: () => `${npsData.value}` }
                                }
                            }
                        }
                    },
                    legend: { position: 'bottom', fontSize: '12px' },
                    tooltip: { y: { formatter: (val, { seriesIndex }) => `${val}%` } }
                }).render();
            }

            // 10. Review Clicks Chart
            const reviewClicks = chartData.review_clicks;
            const reviewTotal = Object.values(reviewClicks).reduce((a, b) => a + b, 0);
            if (reviewTotal > 0 && document.getElementById('client-review-clicks-chart')) {
                const reviewLabels = {
                    google_review: 'Google Review',
                    facebook: 'Facebook',
                    website: 'Website',
                    whatsapp: 'WhatsApp',
                    support_call: 'Support Call',
                    complaint_form: 'Complaint Form'
                };
                const reviewIcons = {
                    google_review: 'bi-google',
                    facebook: 'bi-facebook',
                    website: 'bi-globe',
                    whatsapp: 'bi-whatsapp',
                    support_call: 'bi-telephone',
                    complaint_form: 'bi-chat-left-text'
                };
                const rLabels = Object.keys(reviewClicks).filter(k => reviewClicks[k] > 0);
                const rValues = rLabels.map(k => reviewClicks[k]);
                
                new ApexCharts(document.getElementById('client-review-clicks-chart'), {
                    series: [{ name: 'Clicks', data: rValues }],
                    chart: { type: 'bar', height: 200, fontFamily: 'Inter, sans-serif', foreColor: '#64748b', toolbar: { show: false } },
                    colors: [colors.purple],
                    plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '50%' } },
                    dataLabels: { enabled: true, formatter: (val) => `${val}`, style: { fontSize: '11px', colors: ['#475569'] } },
                    xaxis: { categories: rLabels.map(k => reviewLabels[k] || k), labels: { fontSize: '12px' } },
                    grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
                    tooltip: { y: { formatter: (val) => `${val} clicks` } }
                }).render();
            }
        });
    </script>
    @endpush
</x-admin-layout>