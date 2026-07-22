<x-admin-layout title="Add Client">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.clients.store') }}" enctype="multipart/form-data">
                @csrf

                <h5 class="mb-3">Company Details</h5>
                <div class="row">
                    <div class="col-md-6">
                        <x-form-input name="company_name" label="Company Name" required />
                    </div>
                    <div class="col-md-6">
                        <x-form-input name="industry" label="Industry" />
                    </div>
                </div>
                <div class="mb-3">
                    <label for="logo" class="form-label">Logo</label>
                    <input type="file" name="logo" id="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                    @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-6"><x-form-input name="email" label="Email" type="email" /></div>
                    <div class="col-md-6"><x-form-input name="phone" label="Phone" /></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><x-form-input name="website" label="Website" /></div>
                    <div class="col-md-6"><x-form-input name="address" label="Address" /></div>
                </div>

                <h5 class="mt-4 mb-3">Social & Review Links</h5>
                <div class="row">
                    <div class="col-md-6"><x-form-input name="google_review_url" label="Google Review URL" /></div>
                    <div class="col-md-6"><x-form-input name="facebook_url" label="Facebook URL" /></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><x-form-input name="instagram_url" label="Instagram URL" /></div>
                    <div class="col-md-6"><x-form-input name="linkedin_url" label="LinkedIn URL" /></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><x-form-input name="youtube_url" label="YouTube URL" /></div>
                </div>

                <h5 class="mt-4 mb-3">Contact & Branding</h5>
                <div class="row">
                    <div class="col-md-6"><x-form-input name="support_number" label="Support Number" /></div>
                    <div class="col-md-6"><x-form-input name="whatsapp_number" label="WhatsApp Number" /></div>
                </div>
                <div class="row">
                    <div class="col-md-3"><x-form-input name="brand_color" label="Brand Color" type="color" value="#0d6efd" /></div>
                    <div class="col-md-3"><x-form-input name="secondary_color" label="Secondary Color" type="color" value="#6c757d" /></div>
                    <div class="col-md-3">
                        <x-form-select name="timezone" label="Timezone" :options="array_combine(timezone_identifiers_list(), timezone_identifiers_list())" value="Asia/Kolkata" required />
                    </div>
                    <div class="col-md-3"><x-form-input name="language" label="Language" value="en" required /></div>
                </div>

                <h5 class="mt-4 mb-3">Subscription</h5>
                <div class="row">
                    <div class="col-md-6">
                        <x-form-select name="status" label="Status" :options="['trial' => 'Trial', 'active' => 'Active', 'inactive' => 'Inactive']" value="trial" required />
                    </div>
                    <div class="col-md-6">
                        <x-form-select name="subscription_plan_id" label="Subscription Plan" :options="$plans->pluck('name', 'id')" placeholder="No plan" />
                    </div>
                </div>

                <h5 class="mt-4 mb-3">Portal Owner Login</h5>
                <p class="text-muted small">This creates the first login the client uses to access their portal.</p>
                <div class="row">
                    <div class="col-md-4"><x-form-input name="owner_name" label="Owner Name" required /></div>
                    <div class="col-md-4"><x-form-input name="owner_email" label="Owner Email" type="email" required /></div>
                    <div class="col-md-4"><x-form-input name="owner_password" label="Owner Password" type="password" required /></div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Create Client</button>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
