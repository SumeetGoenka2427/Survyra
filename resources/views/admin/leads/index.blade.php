<x-admin-layout title="Leads">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-0">Leads</h4>
            <p class="text-muted small mb-0">Businesses that requested a demo from the Survyra landing page.</p>
        </div>
    </div>

    <div class="card border-0">
        @if ($leads->isEmpty())
            <x-ds-empty-state
                icon="bi-person-lines-fill"
                title="No leads yet"
                description="Submissions from the 'Get Free Demo' form on the public site will show up here."
            />
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Contact</th>
                            <th>Type</th>
                            <th>Interested In</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leads as $lead)
                            <tr class="ds-fade-in">
                                <td class="fw-semibold">{{ $lead->business_name }}</td>
                                <td class="small">
                                    <div>{{ $lead->name }}</div>
                                    <div class="text-muted">{{ $lead->phone }} &middot; {{ $lead->email }}</div>
                                    @if ($lead->preferred_contact)
                                        <div class="text-muted">Prefers: {{ ucfirst($lead->preferred_contact) }}</div>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $lead->category ?? '—' }}</td>
                                <td class="text-muted small">
                                    {{ $lead->interests ? collect($lead->interests)->map(fn ($i) => ucfirst($i))->join(', ') : '—' }}
                                </td>
                                <td class="text-muted small" style="max-width: 260px;">
                                    {{ $lead->message ? \Illuminate\Support\Str::limit($lead->message, 80) : '—' }}
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.leads.update-status', $lead) }}" class="d-flex align-items-center gap-1">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                                            @foreach (\App\Models\Lead::STATUSES as $status)
                                                <option value="{{ $status }}" @selected($lead->status === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="text-muted small">{{ $lead->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $leads->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
