<x-admin-layout :title="'Import Contacts - '.$client->company_name">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <p class="text-muted small">
                Upload a CSV or Excel file with columns: <code>name</code>, <code>phone</code>, <code>email</code>, <code>city</code>, <code>tags</code> (comma-separated), <code>consent</code> (yes/no).
            </p>

            <form method="POST" action="{{ route('admin.clients.contacts.import', $client) }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".csv,.txt,.xlsx" required>
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Import</button>
                    <a href="{{ route('admin.clients.contacts.index', $client) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
