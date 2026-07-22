@props(['clients' => null, 'selectedClientId' => null, 'surveys', 'selectedSurveyId', 'from', 'to'])

<form id="analytics-filters" class="card border-0 shadow-sm mb-3">
    <div class="card-body d-flex flex-wrap align-items-end gap-3">
        @if ($clients !== null)
            <div>
                <label class="form-label small text-muted mb-1">Client</label>
                <select name="client_id" class="form-select form-select-sm" style="min-width: 180px;">
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected($selectedClientId == $client->id)>{{ $client->company_name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label class="form-label small text-muted mb-1">Survey</label>
            <select name="survey_id" class="form-select form-select-sm" style="min-width: 200px;">
                <option value="">All surveys</option>
                @foreach ($surveys as $survey)
                    <option value="{{ $survey->id }}" @selected($selectedSurveyId == $survey->id)>{{ $survey->title }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label small text-muted mb-1">From</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control form-control-sm">
        </div>

        <div>
            <label class="form-label small text-muted mb-1">To</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control form-control-sm">
        </div>

        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary" data-preset-days="7">7d</button>
            <button type="button" class="btn btn-outline-secondary" data-preset-days="30">30d</button>
            <button type="button" class="btn btn-outline-secondary" data-preset-days="90">90d</button>
        </div>

        <div class="ms-auto d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" data-export-format="pdf" href="#">PDF</a></li>
                    <li><a class="dropdown-item" data-export-format="excel" href="#">Excel</a></li>
                    <li><a class="dropdown-item" data-export-format="csv" href="#">CSV</a></li>
                </ul>
            </div>
        </div>
    </div>
</form>
