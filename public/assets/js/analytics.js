(function () {
    const app = document.getElementById('analytics-app');
    if (!app) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const dataUrl = app.dataset.dataUrl;
    const responsesUrl = app.dataset.responsesUrl;
    const responseShowUrlTemplate = app.dataset.responseShowUrlTemplate;
    const reportsUrl = app.dataset.reportsUrl;
    const exportUrlTemplate = app.dataset.exportUrlTemplate;
    const pollUrlTemplate = app.dataset.pollUrlTemplate;

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

    // Chart libraries don't follow the CSS data-bs-theme toggle automatically —
    // read it once per render so text/grid/empty-cell colors stay legible in
    // both themes instead of hardcoding a light-mode palette.
    function themeColors() {
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        return {
            foreColor: isDark ? '#94a3b8' : '#64748b',
            gridColor: isDark ? '#334155' : '#e2e8f0',
            heatmapEmpty: isDark ? '#1e293b' : '#f1f5f9',
        };
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
    let weeklyChart = null;
    let sentimentChart = null;

    function initCharts() {
        const seed = JSON.parse(document.getElementById('analytics-initial-chart-data').textContent);
        const theme = themeColors();

        const trendEl = document.getElementById('analytics-trend-chart');
        if (trendEl && window.ApexCharts) {
            trendChart = new ApexCharts(trendEl, {
                chart: { type: 'area', height: 260, toolbar: { show: false }, fontFamily: 'Inter, sans-serif', foreColor: theme.foreColor },
                series: [{ name: 'Responses', data: seed.trend.series }],
                xaxis: { categories: seed.trend.labels },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2.5 },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 90, 100] } },
                grid: { borderColor: theme.gridColor, strokeDashArray: 4 },
                colors: ['#4f46e5'],
            });
            trendChart.render();
        }

        const weeklyEl = document.getElementById('analytics-weekly-chart');
        if (weeklyEl && window.ApexCharts) {
            weeklyChart = new ApexCharts(weeklyEl, {
                chart: { type: 'bar', height: 260, toolbar: { show: false }, fontFamily: 'Inter, sans-serif', foreColor: theme.foreColor },
                series: [{ name: 'Responses', data: seed.weekly_trend.series }],
                xaxis: { categories: seed.weekly_trend.labels },
                plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
                dataLabels: { enabled: false },
                grid: { borderColor: theme.gridColor, strokeDashArray: 4 },
                colors: ['#6366f1'],
            });
            weeklyChart.render();
        }

        const sentimentEl = document.getElementById('analytics-sentiment-chart');
        if (sentimentEl && window.ApexCharts) {
            sentimentChart = new ApexCharts(sentimentEl, {
                chart: { type: 'donut', height: 260, fontFamily: 'Inter, sans-serif', foreColor: theme.foreColor },
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
        if (trendChart) {
            trendChart.updateSeries([{ name: 'Responses', data: chart.trend.series }]);
            trendChart.updateOptions({ xaxis: { categories: chart.trend.labels } });
        }
        if (weeklyChart) {
            weeklyChart.updateSeries([{ name: 'Responses', data: chart.weekly_trend.series }]);
            weeklyChart.updateOptions({ xaxis: { categories: chart.weekly_trend.labels } });
        }
        if (sentimentChart) sentimentChart.updateSeries([chart.sentiment.positive, chart.sentiment.neutral, chart.sentiment.negative]);
    }

    // --- Fragment charts (live inside the AJAX-swapped dashboard fragment) --
    // innerHTML swaps don't execute <script> tags, so every chart/behavior
    // inside #analytics-dashboard-fragment is (re)initialized from here,
    // both on first paint and after every refresh/poll.
    let fragmentCharts = [];

    function destroyFragmentCharts() {
        fragmentCharts.forEach((chart) => chart.destroy());
        fragmentCharts = [];
    }

    function renderChart(id, options) {
        const el = document.getElementById(id);
        if (!el || !window.ApexCharts) return;
        const chart = new ApexCharts(el, options);
        chart.render();
        fragmentCharts.push(chart);
    }

    function initFragmentCharts() {
        destroyFragmentCharts();

        const seedEl = document.getElementById('analytics-fragment-chart-data');
        if (!seedEl) return;
        const data = JSON.parse(seedEl.textContent);
        const fontFamily = 'Inter, sans-serif';
        const theme = themeColors();
        const foreColor = theme.foreColor;

        renderChart('sd-heatmap-chart', {
            chart: { type: 'heatmap', height: 280, toolbar: { show: false }, fontFamily, foreColor },
            series: data.hour_day_heatmap,
            dataLabels: { enabled: false },
            colors: ['#4f46e5'],
            plotOptions: { heatmap: { radius: 2, colorScale: { ranges: [{ from: 0, to: 0, color: theme.heatmapEmpty }] } } },
            tooltip: { y: { formatter: (val) => `${val} responses` } },
        });

        renderChart('sd-completion-ring', {
            chart: { type: 'radialBar', height: 220, fontFamily, foreColor },
            series: [data.completion_rate],
            labels: ['Completed'],
            colors: ['#10b981'],
            plotOptions: { radialBar: { hollow: { size: '60%' }, dataLabels: { value: { fontSize: '22px', formatter: (v) => `${v}%` } } } },
        });

        renderChart('sd-abandonment-ring', {
            chart: { type: 'radialBar', height: 220, fontFamily, foreColor },
            series: [data.abandonment_rate],
            labels: ['Abandoned'],
            colors: ['#f43f5e'],
            plotOptions: { radialBar: { hollow: { size: '60%' }, dataLabels: { value: { fontSize: '22px', formatter: (v) => `${v}%` } } } },
        });

        const deviceLabels = Object.keys(data.devices);
        if (deviceLabels.length) {
            renderChart('sd-devices-chart', {
                chart: { type: 'donut', height: 240, fontFamily, foreColor },
                series: Object.values(data.devices),
                labels: deviceLabels.map((l) => l.charAt(0).toUpperCase() + l.slice(1)),
                colors: ['#4f46e5', '#10b981', '#f59e0b', '#6366f1', '#8b5cf6'],
                legend: { position: 'bottom', fontSize: '11px' },
                dataLabels: { enabled: false },
            });
        }

        const sourceLabels = Object.keys(data.sources);
        if (sourceLabels.length) {
            renderChart('sd-sources-chart', {
                chart: { type: 'pie', height: 240, fontFamily, foreColor },
                series: Object.values(data.sources),
                labels: sourceLabels.map((l) => l.charAt(0).toUpperCase() + l.slice(1)),
                colors: ['#4f46e5', '#6366f1', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6'],
                legend: { position: 'bottom', fontSize: '11px' },
                dataLabels: { enabled: false },
            });
        }

        const countryLabels = Object.keys(data.countries);
        if (countryLabels.length) {
            renderChart('sd-countries-chart', {
                chart: { type: 'bar', height: 240, fontFamily, foreColor, toolbar: { show: false } },
                series: [{ name: 'Responses', data: Object.values(data.countries) }],
                xaxis: { categories: countryLabels },
                plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%' } },
                colors: ['#6366f1'],
                dataLabels: { enabled: false },
                grid: { borderColor: theme.gridColor, strokeDashArray: 4 },
            });
        }

        if (data.drop_off && data.drop_off.length) {
            renderChart('sd-dropoff-chart', {
                chart: { type: 'bar', height: Math.max(200, data.drop_off.length * 40), fontFamily, foreColor, toolbar: { show: false } },
                series: [{ name: 'Drop-offs', data: data.drop_off.map((d) => d.drop_count) }],
                xaxis: { categories: data.drop_off.map((d) => d.question_text) },
                plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
                colors: ['#f43f5e'],
                dataLabels: { enabled: false },
                grid: { borderColor: theme.gridColor, strokeDashArray: 4 },
            });
        }

        const reviewLabels = { google_review: 'Google Review', facebook: 'Facebook', website: 'Website', whatsapp: 'WhatsApp', support_call: 'Support Call', complaint_form: 'Complaint Form' };
        const reviewKeys = Object.keys(data.review_clicks || {}).filter((k) => data.review_clicks[k] > 0);
        if (reviewKeys.length) {
            renderChart('sd-review-clicks-chart', {
                chart: { type: 'bar', height: 200, fontFamily, foreColor, toolbar: { show: false } },
                series: [{ name: 'Clicks', data: reviewKeys.map((k) => data.review_clicks[k]) }],
                xaxis: { categories: reviewKeys.map((k) => reviewLabels[k] || k) },
                plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '50%' } },
                colors: ['#8b5cf6'],
                dataLabels: { enabled: true, style: { fontSize: '11px' } },
                grid: { borderColor: theme.gridColor, strokeDashArray: 4 },
            });
        }

        const browserLabels = Object.keys(data.browsers || {});
        if (browserLabels.length) {
            renderChart('sd-browsers-chart', {
                chart: { type: 'donut', height: 240, fontFamily, foreColor },
                series: Object.values(data.browsers),
                labels: browserLabels,
                colors: ['#4f46e5', '#10b981', '#f59e0b', '#6366f1', '#8b5cf6'],
                legend: { position: 'bottom', fontSize: '11px' },
                dataLabels: { enabled: false },
            });
        }

        (data.question_breakdown_js || []).forEach((item, index) => {
            if (item.type === 'choice') {
                renderChart(`sd-qb-${index}`, {
                    chart: { type: 'bar', height: Math.max(180, item.labels.length * 36), fontFamily, foreColor, toolbar: { show: false } },
                    series: [{ name: 'Responses', data: item.values }],
                    xaxis: { categories: item.labels },
                    plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%' } },
                    colors: ['#4f46e5'],
                    dataLabels: { enabled: false },
                    grid: { borderColor: theme.gridColor, strokeDashArray: 4 },
                });
            } else if (item.type === 'scale') {
                renderChart(`sd-qb-${index}`, {
                    chart: { type: 'radialBar', height: 200, fontFamily, foreColor },
                    series: [Math.round((item.avg / item.max) * 100)],
                    labels: [`Avg ${item.avg}`],
                    colors: ['#4f46e5'],
                    plotOptions: { radialBar: { hollow: { size: '55%' }, dataLabels: { value: { fontSize: '18px', formatter: () => item.avg } } } },
                });
            }
        });

        initSurveyPerformanceTable();
        initAiPanel();
    }

    // --- Survey performance table: client-side sort + search -------------
    function initSurveyPerformanceTable() {
        const table = document.getElementById('sd-survey-performance-table');
        if (!table) return;

        const searchInput = document.getElementById('sd-survey-search');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr[data-row]'));

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const term = searchInput.value.trim().toLowerCase();
                rows.forEach((row) => {
                    const title = row.dataset.title || '';
                    row.classList.toggle('d-none', term.length > 0 && !title.includes(term));
                });
            });
        }

        table.querySelectorAll('[data-sort-key]').forEach((header) => {
            header.addEventListener('click', function () {
                const key = header.dataset.sortKey;
                const currentDir = header.dataset.sortDir === 'asc' ? 'desc' : 'asc';
                table.querySelectorAll('[data-sort-key]').forEach((h) => h.removeAttribute('data-sort-dir'));
                header.dataset.sortDir = currentDir;

                const sorted = rows.slice().sort((a, b) => {
                    const av = parseFloat(a.dataset[key]) || 0;
                    const bv = parseFloat(b.dataset[key]) || 0;
                    return currentDir === 'asc' ? av - bv : bv - av;
                });

                sorted.forEach((row) => tbody.appendChild(row));
            });
        });
    }

    // --- AI Insights panel: on-demand, never auto-run (real API calls cost
    // money/time) - each button fetches its own result and renders it inline.
    function initAiPanel() {
        const results = document.getElementById('ai-insights-results');
        if (!results) return;

        document.querySelectorAll('#ai-insights-buttons [data-ai-action]').forEach((button) => {
            button.addEventListener('click', function () {
                const action = button.dataset.aiAction;
                const url = button.dataset.aiUrl;

                document.querySelectorAll('#ai-insights-buttons [data-ai-action]').forEach((b) => (b.disabled = true));
                results.innerHTML = '<div class="text-muted small"><div class="spinner-border spinner-border-sm me-2"></div>Generating…</div>';

                jsonFetch(url).then(({ ok, data }) => {
                    document.querySelectorAll('#ai-insights-buttons [data-ai-action]').forEach((b) => (b.disabled = false));
                    if (!ok) {
                        results.innerHTML = '<div class="text-danger small">Could not generate this insight. Please try again.</div>';
                        return;
                    }
                    results.innerHTML = renderAiResult(action, data);
                }).catch(() => {
                    document.querySelectorAll('#ai-insights-buttons [data-ai-action]').forEach((b) => (b.disabled = false));
                    results.innerHTML = '<div class="text-danger small">Network error - please try again.</div>';
                });
            });
        });
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = String(value);
        return div.innerHTML;
    }

    function renderAiResult(action, data) {
        if (action === 'quality-score') {
            const color = data.score >= 90 ? 'success' : data.score >= 75 ? 'primary' : data.score >= 50 ? 'warning' : 'danger';
            const feedback = (data.feedback || []).map((f) => `<li>${escapeHtml(f)}</li>`).join('');
            const suggestions = (data.suggestions || []).map((s) => `<li>${escapeHtml(s)}</li>`).join('');
            return `
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="display-6 fw-bold text-${color}">${data.score}</div>
                    <div><span class="badge bg-${color}">${escapeHtml(data.grade)}</span></div>
                </div>
                ${feedback ? `<div class="mb-2"><strong class="small">Issues</strong><ul class="small mb-0">${feedback}</ul></div>` : ''}
                ${suggestions ? `<div><strong class="small">Suggestions</strong><ul class="small mb-0">${suggestions}</ul></div>` : ''}
            `;
        }

        if (action === 'summary') {
            return `<p class="mb-0">${escapeHtml(data.summary || 'No summary available.')}</p>`;
        }

        if (action === 'sentiment') {
            const pct = (v) => Math.round((v || 0) * 100);
            return `
                <div class="row g-3 mb-3 text-center">
                    <div class="col-4"><div class="fs-4 fw-bold text-success">${pct(data.positive)}%</div><div class="small text-muted">Positive</div></div>
                    <div class="col-4"><div class="fs-4 fw-bold text-secondary">${pct(data.neutral)}%</div><div class="small text-muted">Neutral</div></div>
                    <div class="col-4"><div class="fs-4 fw-bold text-danger">${pct(data.negative)}%</div><div class="small text-muted">Negative</div></div>
                </div>
                <p class="mb-0 small">${escapeHtml(data.summary || '')}</p>
            `;
        }

        if (action === 'keywords') {
            if (!Array.isArray(data) || !data.length) return '<p class="text-muted small mb-0">No keywords found.</p>';
            return `<div class="d-flex flex-wrap gap-2">${data.map((k) => `<span class="badge bg-light text-dark border">${escapeHtml(k.word)} <span class="text-muted">(${k.count})</span></span>`).join('')}</div>`;
        }

        if (action === 'actions') {
            if (!Array.isArray(data) || !data.length) return '<p class="text-muted small mb-0">No recommendations available.</p>';
            const priorityColor = { high: 'danger', medium: 'warning', low: 'secondary', info: 'info' };
            return `<div class="list-group list-group-flush">${data.map((a) => `
                <div class="list-group-item px-0">
                    <span class="badge bg-${priorityColor[a.priority] || 'secondary'} text-uppercase me-2">${escapeHtml(a.priority)}</span>
                    <span>${escapeHtml(a.action)}</span>
                    ${a.impact ? `<div class="small text-muted mt-1">${escapeHtml(a.impact)}</div>` : ''}
                </div>
            `).join('')}</div>`;
        }

        if (action === 'executive-report') {
            // Server-generated (admin-only route), not user input - safe to render as-is.
            return data.html || '<p class="text-muted small mb-0">No report generated.</p>';
        }

        return '<p class="text-muted small mb-0">No data.</p>';
    }

    // --- Dashboard --------------------------------------------------------
    function refreshDashboard() {
        const params = currentFilters();
        return jsonFetch(`${dataUrl}?${params.toString()}`).then(({ data }) => {
            dashboardFragment.innerHTML = data.html || '<div class="text-center text-muted py-5">No client selected.</div>';
            updateCharts(data.chart);
            initFragmentCharts();
        });
    }

    // --- Responses ----------------------------------------------------
    const responsesSearch = document.getElementById('responses-search');
    const responsesStatus = document.getElementById('responses-status');

    function responsesFilters() {
        const params = currentFilters();
        if (responsesSearch && responsesSearch.value.trim()) params.set('search', responsesSearch.value.trim());
        if (responsesStatus && responsesStatus.value) params.set('status', responsesStatus.value);
        return params;
    }

    function refreshResponses(url) {
        const target = url || `${responsesUrl}?${responsesFilters().toString()}`;
        responsesFragment.innerHTML = '<div class="text-center text-muted py-5"><div class="spinner-border spinner-border-sm"></div> Loading…</div>';
        return jsonFetch(target).then(({ data }) => {
            responsesFragment.innerHTML = data.html;
        });
    }

    responsesFragment.addEventListener('click', function (event) {
        const link = event.target.closest('.analytics-pagination a, [data-sort-link]');
        if (link) {
            event.preventDefault();
            refreshResponses(link.href);
        }
    });

    let responsesSearchDebounce = null;
    if (responsesSearch) {
        responsesSearch.addEventListener('input', function () {
            clearTimeout(responsesSearchDebounce);
            responsesSearchDebounce = setTimeout(() => refreshResponses(), 250);
        });
    }
    if (responsesStatus) {
        responsesStatus.addEventListener('change', () => refreshResponses());
    }

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
            resetPollBaseline();
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
    initFragmentCharts();

    // Charts don't recolor themselves when the dark-mode toggle flips
    // data-bs-theme live (no page reload) — rebuild them so text/grid colors
    // stay legible instead of waiting for the next 30s poll to catch up.
    function reinitAllCharts() {
        if (trendChart) trendChart.destroy();
        if (weeklyChart) weeklyChart.destroy();
        if (sentimentChart) sentimentChart.destroy();
        trendChart = null;
        weeklyChart = null;
        sentimentChart = null;
        initCharts();
        initFragmentCharts();
    }

    new MutationObserver(function (mutations) {
        if (mutations.some((m) => m.attributeName === 'data-bs-theme')) reinitAllCharts();
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });

    // Real-time polling. When a single survey is selected, use the
    // lightweight incremental /poll endpoint (server-cached 5s, just a
    // response count) and only pay for a full dashboard refresh when it
    // reports actual new data - avoids re-rendering every chart every 30s
    // when nothing changed. Falls back to the old blind full-refresh when
    // viewing "all surveys" for a client, since poll() is scoped to one survey.
    let liveInterval = null;
    let lastKnownCount = null;
    const liveIndicator = document.getElementById('analytics-live-indicator');

    function resetPollBaseline() {
        lastKnownCount = null;
    }

    function checkForUpdates() {
        const surveyId = filtersForm.querySelector('[name="survey_id"]')?.value;

        if (!surveyId || !pollUrlTemplate) {
            refreshDashboard();
            return;
        }

        const url = pollUrlTemplate.replace('__SURVEY__', surveyId) + `?last_count=${lastKnownCount ?? 0}`;

        jsonFetch(url).then(({ data }) => {
            if (lastKnownCount === null || data.has_updates) {
                lastKnownCount = data.total_responses;
                refreshDashboard();
            }
        }).catch(() => {
            // Ignore - next tick tries again
        });
    }

    function startLivePolling() {
        if (liveInterval) return;
        liveInterval = setInterval(function () {
            if (activeTab === 'dashboard') checkForUpdates();
        }, 10000);
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
