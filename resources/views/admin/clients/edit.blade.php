<x-admin-layout title="Edit Client">
    @can('viewAny', \App\Models\Contact::class)
        <div class="d-flex justify-content-end gap-2 mb-3">
            <a href="{{ route('admin.clients.contacts.index', $client) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-person-lines-fill"></i> Contacts
            </a>
        </div>
    @endcan

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.clients.update', $client) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <h5 class="mb-3">Company Details</h5>
                @if ($client->logo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($client->logo_path) }}" alt="{{ $client->company_name }}" class="mb-3 rounded" style="max-height: 64px;">
                @endif
                <div class="row">
                    <div class="col-md-6">
                        <x-form-input name="company_name" label="Company Name" :value="$client->company_name" required />
                    </div>
                    <div class="col-md-6">
                        <x-form-input name="industry" label="Industry" :value="$client->industry" />
                    </div>
                </div>
                <div class="mb-3">
                    <label for="logo" class="form-label">Logo</label>
                    <input type="file" name="logo" id="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                    @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-6"><x-form-input name="email" label="Email" type="email" :value="$client->email" /></div>
                    <div class="col-md-6"><x-form-input name="phone" label="Phone" :value="$client->phone" /></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><x-form-input name="website" label="Website" :value="$client->website" /></div>
                    <div class="col-md-6"><x-form-input name="address" label="Address" :value="$client->address" /></div>
                </div>

                <h5 class="mt-4 mb-3">Social & Review Links</h5>
                <div class="row">
                    <div class="col-md-6"><x-form-input name="google_review_url" label="Google Review URL" :value="$client->google_review_url" /></div>
                    <div class="col-md-6"><x-form-input name="facebook_url" label="Facebook URL" :value="$client->facebook_url" /></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><x-form-input name="instagram_url" label="Instagram URL" :value="$client->instagram_url" /></div>
                    <div class="col-md-6"><x-form-input name="linkedin_url" label="LinkedIn URL" :value="$client->linkedin_url" /></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><x-form-input name="youtube_url" label="YouTube URL" :value="$client->youtube_url" /></div>
                </div>

                <h5 class="mt-4 mb-3">Contact & Branding</h5>
                <div class="row">
                    <div class="col-md-6"><x-form-input name="support_number" label="Support Number" :value="$client->support_number" /></div>
                    <div class="col-md-6"><x-form-input name="whatsapp_number" label="WhatsApp Number" :value="$client->whatsapp_number" /></div>
                </div>
                <div class="row">
                    <div class="col-md-3"><x-form-input name="brand_color" label="Brand Color" type="color" :value="$client->brand_color" /></div>
                    <div class="col-md-3"><x-form-input name="secondary_color" label="Secondary Color" type="color" :value="$client->secondary_color" /></div>
                    <div class="col-md-3">
                        <x-form-select name="timezone" label="Timezone" :options="array_combine(timezone_identifiers_list(), timezone_identifiers_list())" :value="$client->timezone" required />
                    </div>
                    <div class="col-md-3"><x-form-input name="language" label="Language" :value="$client->language" required /></div>
                </div>

                <h5 class="mt-4 mb-3">Subscription</h5>
                <div class="row">
                    <div class="col-md-6">
                        <x-form-select name="status" label="Status" :options="['trial' => 'Trial', 'active' => 'Active', 'inactive' => 'Inactive']" :value="$client->status" required />
                    </div>
                    <div class="col-md-6">
                        <x-form-select name="subscription_plan_id" label="Subscription Plan" :options="$plans->pluck('name', 'id')" :value="$client->subscription_plan_id" placeholder="No plan" />
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><strong>Portal Users</strong></div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($client->clientUsers as $clientUser)
                        <tr>
                            <td>{{ $clientUser->name }}</td>
                            <td>{{ $clientUser->email }}</td>
                            <td>{{ ucfirst($clientUser->role) }}</td>
                            <td><span class="badge text-bg-{{ $clientUser->is_active ? 'success' : 'secondary' }}">{{ $clientUser->is_active ? 'Active' : 'Inactive' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No portal users yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
