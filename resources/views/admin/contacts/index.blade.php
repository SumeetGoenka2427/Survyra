<x-admin-layout :title="'Contacts - '.$client->company_name">
    @if (session('importErrors'))
        @foreach (session('importErrors') as $error)
            <div class="alert alert-warning">{{ $error }}</div>
        @endforeach
    @endif

    <div class="d-flex justify-content-end gap-2 mb-3">
        <a href="{{ route('admin.clients.contacts.import-form', $client) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-upload"></i> Import CSV/Excel
        </a>
        <a href="{{ route('admin.clients.contacts.create', $client) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add Contact
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>City</th>
                        <th>Tags</th>
                        <th>Consent</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contacts as $contact)
                        <tr>
                            <td>{{ $contact->name }}</td>
                            <td>{{ $contact->city ?? '—' }}</td>
                            <td>
                                @foreach ($contact->tags as $tag)
                                    <span class="badge text-bg-light text-dark border">{{ $tag->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $contact->consent ? 'success' : 'secondary' }}">
                                    {{ $contact->consent ? 'Consented' : 'No consent' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.clients.contacts.edit', [$client, $contact]) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('admin.clients.contacts.destroy', [$client, $contact]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this contact?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No contacts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $contacts->links() }}
        </div>
    </div>
</x-admin-layout>
