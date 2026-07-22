<x-admin-layout title="New Campaign">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET">
                <label class="form-label">1. Choose Client</label>
                <select name="client_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Select a client...</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected($selectedClient?->id === $client->id)>{{ $client->company_name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    @if ($selectedClient)
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.campaigns.store') }}">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $selectedClient->id }}">

                    <x-form-input name="name" label="Campaign Name" required />

                    <div class="mb-3">
                        <label class="form-label">Survey</label>
                        <select name="survey_id" class="form-select" required>
                            <option value="">Select a published survey...</option>
                            @foreach ($surveys as $survey)
                                <option value="{{ $survey->id }}">{{ $survey->title }}</option>
                            @endforeach
                        </select>
                        @if ($surveys->isEmpty())
                            <div class="form-text text-danger">This client has no published surveys yet.</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Channel</label>
                        <select name="type" class="form-select" required>
                            <option value="sms">SMS</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="email">Email</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message_template" class="form-control" rows="4" placeholder="Hi {name}, we'd love your feedback! {link}" required></textarea>
                        <div class="form-text">Use <code>{name}</code> and <code>{link}</code> - they're replaced per recipient.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Target Tags (leave empty for all contacts)</label>
                        <div class="d-flex flex-wrap gap-3">
                            @forelse ($tags as $tag)
                                <div class="form-check">
                                    <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" class="form-check-input" id="tag-{{ $tag->id }}">
                                    <label class="form-check-label" for="tag-{{ $tag->id }}">{{ $tag->name }}</label>
                                </div>
                            @empty
                                <span class="text-muted small">No tags yet for this client.</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Create Campaign</button>
                        <a href="{{ route('admin.campaigns.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-admin-layout>
