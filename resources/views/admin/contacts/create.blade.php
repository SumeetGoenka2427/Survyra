<x-admin-layout :title="'Add Contact - '.$client->company_name">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.clients.contacts.store', $client) }}">
                @csrf

                <x-form-input name="name" label="Name" required />
                <div class="row">
                    <div class="col-md-6"><x-form-input name="phone" label="Phone" /></div>
                    <div class="col-md-6"><x-form-input name="email" label="Email" type="email" /></div>
                </div>
                <x-form-input name="city" label="City" />

                <div class="mb-3">
                    <label class="form-label">Tags (comma separated)</label>
                    <input type="text" class="form-control" id="tags-input" placeholder="e.g. regulars, vip">
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="consent" value="1" class="form-check-input" id="consent">
                    <label class="form-check-label" for="consent">Has given consent to be contacted</label>
                </div>
                <x-form-input name="consent_source" label="Consent Source" placeholder="e.g. Signed up in-store" />

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Add Contact</button>
                    <a href="{{ route('admin.clients.contacts.index', $client) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function () {
            const raw = document.getElementById('tags-input').value;
            const tags = raw.split(',').map(t => t.trim()).filter(Boolean);
            tags.forEach(tag => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'tags[]';
                input.value = tag;
                this.appendChild(input);
            });
        });
    </script>
</x-admin-layout>
