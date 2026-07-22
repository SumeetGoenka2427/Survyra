@props(['reports', 'surveys', 'storeUrl', 'deleteUrlTemplate'])

<div class="row g-3">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong>Schedule a Report</strong></div>
            <div class="card-body">
                <form id="report-create-form" data-store-url="{{ $storeUrl }}">
                    <div class="mb-3">
                        <label class="form-label small">Survey</label>
                        <select name="survey_id" class="form-select form-select-sm">
                            <option value="">All surveys</option>
                            @foreach ($surveys as $survey)
                                <option value="{{ $survey->id }}">{{ $survey->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Format</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Frequency</label>
                        <select name="frequency" class="form-select form-select-sm">
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Recipient emails (comma separated)</label>
                        <input type="text" name="recipients" class="form-control form-control-sm" placeholder="owner@example.com, manager@example.com" required>
                    </div>
                    <div id="report-form-errors" class="text-danger small mb-2"></div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> Schedule Report
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong>Scheduled Reports</strong></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Survey</th>
                            <th>Format</th>
                            <th>Frequency</th>
                            <th>Next Run</th>
                            <th>Recipients</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td>{{ $report->survey->title ?? 'All surveys' }}</td>
                                <td><span class="badge text-bg-light text-dark border">{{ strtoupper($report->type) }}</span></td>
                                <td>{{ ucfirst($report->frequency) }}</td>
                                <td>{{ $report->next_run_at?->format('M j, Y') }}</td>
                                <td class="small text-muted">{{ implode(', ', $report->recipients) }}</td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        data-delete-report
                                        data-delete-url="{{ str_replace('__ID__', $report->id, $deleteUrlTemplate) }}"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No scheduled reports yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
