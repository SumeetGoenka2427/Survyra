<x-portal-layout title="Company Profile">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <p class="text-muted small">
                You can update your logo, contact details, and social links. Company name, industry, and subscription are managed by Survyra.
            </p>
            <form method="POST" action="{{ route('portal.company.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                @if ($client->logo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($client->logo_path) }}" alt="{{ $client->company_name }}" class="mb-3 rounded" style="max-height: 64px;">
                @endif
                <div class="mb-3">
                    <label for="logo" class="form-label">Logo</label>
                    <input type="file" name="logo" id="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                    @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6"><x-form-input name="phone" label="Phone" :value="$client->phone" /></div>
                    <div class="col-md-6"><x-form-input name="website" label="Website" :value="$client->website" /></div>
                </div>
                <x-form-input name="address" label="Address" :value="$client->address" />

                <div class="row">
                    <div class="col-md-6"><x-form-input name="google_review_url" label="Google Review URL" :value="$client->google_review_url" /></div>
                    <div class="col-md-6"><x-form-input name="facebook_url" label="Facebook URL" :value="$client->facebook_url" /></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><x-form-input name="instagram_url" label="Instagram URL" :value="$client->instagram_url" /></div>
                    <div class="col-md-6"><x-form-input name="linkedin_url" label="LinkedIn URL" :value="$client->linkedin_url" /></div>
                </div>
                <x-form-input name="youtube_url" label="YouTube URL" :value="$client->youtube_url" />

                <div class="row">
                    <div class="col-md-6"><x-form-input name="support_number" label="Support Number" :value="$client->support_number" /></div>
                    <div class="col-md-6"><x-form-input name="whatsapp_number" label="WhatsApp Number" :value="$client->whatsapp_number" /></div>
                </div>

                <button type="submit" class="btn btn-primary mt-2">Save Changes</button>
            </form>
        </div>
    </div>
</x-portal-layout>
