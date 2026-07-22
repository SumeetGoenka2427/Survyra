(function () {
    const app = document.getElementById('analytics-app');
    if (!app) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const dataUrl = app.dataset.dataUrl;
    const responsesUrl = app.dataset.responsesUrl;
    const responseShowUrlTemplate = app.dataset.responseShowUrlTemplate;
    const reportsUrl = app.dataset.reportsUrl;
    const exportUrlTemplate = app.dataset.exportUrlTemplate;

    const filtersForm = document.getElementById('analytics-filters');
    const dashboardFragment = document.getElementById('analytics-dashboard-fragment');
    const responsesFragment = document.getElementById('analytics-responses-fragment');
    const reportsFragment = document.getElementById('analytics-reports-fragment');

    const loadedPanes = { dashboard: true, responses: false, reports: false };
    let activeTab = 'dashboard';

    function currentFilters() {
        const data = new FormData(filtersForm);
        const params = new URLSearchParams();
        for (const [key, value] of data.entries()) {
            if (value !== '') params.append(key, value);
        }
        return params;
    }

    function jsonFetch(url, options = {}) {
        return fetch(url, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                ...(options.headers || {}),
            },
            ...options,
        }).then((response) => response.json().then((data) => ({ ok: response.ok, status: response.status, data })));
    }

    // --- Charts ---------------------------------------------------------
    let trendChart = null;
    let sentimentChart = null;

    function initCharts() {
        const seed = JSON.parse(document.getElementById('analytics-initial-chart-data').textContent);

        const trendEl = document.getElementById('analytics-trend-chart');
        if (trendEl && window.ApexCharts) {
            trendChart = new ApexCharts(trendEl, {
                chart: { type: 'area', height: 260, toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
                series: [{ name: 'Responses', data: seed.trend.series }],
                xaxis: { categories: seed.trend.labels },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2.5 },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 90, 100] } },
                grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
                colors: ['#4f46e5'],
            });
            trendChart.render();
        }

        const sentimentEl = document.getElementById('analytics-sentiment-chart');
        if (sentimentEl && window.ApexCharts) {
            sentimentChart = new ApexCharts(sentimentEl, {
                chart: { type: 'donut', height: 260, fontFamily: 'Inter, sans-serif' },
                series: [seed.sentiment.positive, seed.sentiment.neutral, seed.sentiment.negative],
                labels: ['Positive', 'Neutral', 'Negative'],
                colors: ['#10b981', '#94a3b8', '#f43f5e'],
                legend: { position: 'bottom' },
                dataLabels: { style: { fontFamily: 'Inter, sans-serif' } },
            });
            sentimentChart.render();
        }
    }

    function updateCharts(chart) {
        if (!chart) return;
        if (trendChart) trendChart.updateSeries([{ name: 'Responses', data: chart.trend.series }]);
        if (sentimentChart) sentimentChart.updateSeries([chart.sentiment.positive, chart.sentiment.neutral, chart.sentiment.negative]);
        if (trendChart) trendChart.updateOptions({ xaxis: { categories: chart.trend.labels } });
    }

    // --- Dashboard --------------------------------------------------------
    function refreshDashboard() {
        const params = currentFilters();
        return jsonFetch(`${dataUrl}?${params.toString()}`).then(({ data }) => {
            dashboardFragment.innerHTML = data.html || '<div class="text-center text-muted py-5">No client selected.</div>';
            updateCharts(data.chart);
        });
    }

    // --- Responses ----------------------------------------------------
    function refreshResponses(url) {
        const target = url || `${responsesUrl}?${currentFilters().toString()}`;
        responsesFragment.innerHTML = '<div class="text-center text-muted py-5"><div class="spinner-border spinner-border-sm"></div> Loading…</div>';
        return jsonFetch(target).then(({ data }) => {
            responsesFragment.innerHTML = data.html;
        });
    }

    responsesFragment.addEventListener('click', function (event) {
        const link = event.target.closest('.analytics-pagination a');
        if (link) {
            event.preventDefault();
            refreshResponses(link.href);
        }
    });

    // --- Reports --------------------------------------------------------
    function refreshReports() {
        const params = currentFilters();
        reportsFragment.innerHTML = '<div class="text-center text-muted py-5"><div class="spinner-border spinner-border-sm"></div> Loading…</div>';
        return jsonFetch(`${reportsUrl}?${params.toString()}`).then(({ data }) => {
            reportsFragment.innerHTML = data.html;
        });
    }

    reportsFragment.addEventListener('submit', function (event) {
        if (event.target.id !== 'report-create-form') return;
        event.preventDefault();

        const form = event.target;
        const errorBox = document.getElementById('report-form-errors');
        errorBox.textContent = '';

        const payload = {};
        new FormData(form).forEach((value, key) => {
            payload[key] = value;
        });
        const clientSelect = filtersForm.querySelector('[name="client_id"]');
        if (clientSelect) payload.client_id = clientSelect.value;

        jsonFetch(form.dataset.storeUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        }).then(({ ok, status, data }) => {
            if (status === 422) {
                errorBox.textContent = Object.values(data.errors || {})[0]?.[0] || 'Please check the form and try again.';
                return;
            }
            if (!ok) {
                errorBox.textContent = 'Something went wrong. Please try again.';
                return;
            }
            reportsFragment.innerHTML = data.html;
        });
    });

    reportsFragment.addEventListener('click', function (event) {
        const button = event.target.closest('[data-delete-report]');
        if (!button) return;

        if (!confirm('Delete this scheduled report?')) return;

        jsonFetch(button.dataset.deleteUrl, { method: 'DELETE' }).then(({ data }) => {
            reportsFragment.innerHTML = data.html;
        });
    });

    // --- Response detail modal -----------------------------------------
    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-view-response]');
        if (!trigger) return;

        const url = responseShowUrlTemplate.replace('__ID__', trigger.dataset.viewResponse);
        const modalContent = document.getElementById('responseModalContent');
        modalContent.innerHTML = '<div class="modal-body text-center py-5"><div class="spinner-border spinner-border-sm"></div></div>';

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('responseModal'));
        modal.show();

        jsonFetch(url).then(({ data }) => {
            modalContent.innerHTML = data.html;
        });
    });

    // --- Tabs ---------------------------------------------------------
    document.querySelectorAll('[data-analytics-tab]').forEach((button) => {
        button.addEventListener('click', function () {
            const tab = button.dataset.analyticsTab;
            activeTab = tab;

            document.querySelectorAll('[data-analytics-tab]').forEach((b) => b.classList.remove('active'));
            button.classList.add('active');

            document.querySelectorAll('[data-analytics-pane]').forEach((pane) => {
                pane.classList.toggle('d-none', pane.dataset.analyticsPane !== tab);
            });

            if (!loadedPanes[tab]) {
                loadedPanes[tab] = true;
                if (tab === 'responses') refreshResponses();
                if (tab === 'reports') refreshReports();
            }
        });
    });

    // --- Filters --------------------------------------------------------
    let filterDebounce = null;

    function onFiltersChanged() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(function () {
            refreshDashboard();
            if (loadedPanes.responses) refreshResponses();
            if (loadedPanes.reports) refreshReports();
        }, 150);
    }

    filtersForm.addEventListener('change', onFiltersChanged);

    filtersForm.querySelectorAll('[data-preset-days]').forEach((button) => {
        button.addEventListener('click', function () {
            const days = parseInt(button.dataset.presetDays, 10);
            const to = new Date();
            const from = new Date();
            from.setDate(from.getDate() - days);

            filtersForm.querySelector('[name="to"]').value = to.toISOString().slice(0, 10);
            filtersForm.querySelector('[name="from"]').value = from.toISOString().slice(0, 10);
            onFiltersChanged();
        });
    });

    filtersForm.querySelectorAll('[data-export-format]').forEach((link) => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            const format = link.dataset.exportFormat;
            const params = currentFilters();
            window.location.href = `${exportUrlTemplate.replace('__FORMAT__', format)}?${params.toString()}`;
        });
    });

    initCharts();

    // Real-time polling: refresh dashboard every 30 seconds
    let liveInterval = null;
    const liveIndicator = document.getElementById('analytics-live-indicator');

    function startLivePolling() {
        if (liveInterval) return;
        liveInterval = setInterval(function () {
            if (activeTab === 'dashboard') refreshDashboard();
        }, 30000);
        if (liveIndicator) liveIndicator.classList.remove('d-none');
    }

    function stopLivePolling() {
        clearInterval(liveInterval);
        liveInterval = null;
        if (liveIndicator) liveIndicator.classList.add('d-none');
    }

    const liveToggle = document.getElementById('analytics-live-toggle');
    if (liveToggle) {
        liveToggle.addEventListener('click', function () {
            liveInterval ? stopLivePolling() : startLivePolling();
            liveToggle.textContent = liveInterval ? 'Stop Live' : 'Live';
        });
    }

    startLivePolling();
})();
