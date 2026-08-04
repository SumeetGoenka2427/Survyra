<x-portal-layout title="Website Leads">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Contact Form Submissions</h5>

            @if ($leads->isEmpty())
                <p class="text-muted">No submissions yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Received</th>
                                <th>Page</th>
                                <th>Details</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leads as $lead)
                                <tr>
                                    <td class="text-nowrap">{{ $lead->created_at->diffForHumans() }}</td>
                                    <td>{{ $lead->page->title ?? '-' }}</td>
                                    <td>
                                        @foreach ($lead->data ?? [] as $key => $value)
                                            <div><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</div>
                                        @endforeach
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $lead->status === 'new' ? 'primary' : 'secondary' }}">{{ ucfirst($lead->status) }}</span>
                                    </td>
                                    <td>
                                        @if ($lead->status === 'new')
                                            <form method="POST" action="{{ route('portal.website.leads.handled', $lead) }}">
                                                @csrf @method('PATCH')
                                                <button class="btn btn-sm btn-outline-secondary">Mark Handled</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $leads->links() }}
            @endif
        </div>
    </div>
</x-portal-layout>
