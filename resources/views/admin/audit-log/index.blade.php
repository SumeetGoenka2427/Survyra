<x-admin-layout title="Audit Log">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted">User Type</label>
                    <select name="causer_type" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach ($causerTypes as $type)
                            <option value="{{ $type }}" @selected(request('causer_type') === $type)>{{ class_basename($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Subject Type</label>
                    <select name="subject_type" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach ($subjectTypes as $type)
                            <option value="{{ $type }}" @selected(request('subject_type') === $type)>{{ class_basename($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.audit-log.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Subject</th>
                            <th>Changes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="text-nowrap small">{{ $log->created_at->format('M j, g:ia') }}</td>
                                <td>
                                    @if ($log->causer)
                                        <span class="small">{{ $log->causer->name ?? $log->causer->email }}</span>
                                        <span class="text-muted small">({{ class_basename($log->causer_type) }})</span>
                                    @else
                                        <span class="text-muted small">System</span>
                                    @endif
                                </td>
                                <td><code class="small">{{ $log->description }}</code></td>
                                <td>
                                    @if ($log->subject)
                                        <span class="small">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($log->properties && $log->properties->has('attributes'))
                                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#log-detail-{{ $log->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <div class="modal fade" id="log-detail-{{ $log->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h6 class="modal-title">Change Details</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <pre class="small mb-0" style="max-height: 400px; overflow-y: auto;">{{ json_encode($log->properties->toArray(), JSON_PRETTY_PRINT) }}</pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-journal-text d-block fs-3 mb-2"></i>
                                    No audit log entries found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>