<x-portal-layout title="Website Builder">
    @if (! $website)
        <div class="card border-0 shadow-sm">
            <div class="card-body" x-data="{ templateId: '' }">
                <h5 class="mb-3">Build Your Website</h5>
                <p class="text-muted small">Start from an industry template or a blank site - no code required.</p>

                @unless($canCreateWebsite)
                    <div class="alert alert-warning">Your plan does not allow creating a website. Contact us to upgrade.</div>
                @else
                    <form method="POST" action="{{ route('portal.website.store') }}">
                        @csrf
                        <input type="hidden" name="template_id" :value="templateId">

                        <div class="row g-3 mb-3">
                            <div class="col-md-4 col-6">
                                <label class="w-100" style="cursor: pointer;">
                                    <input type="radio" class="btn-check" @click="templateId = ''" checked>
                                    <div class="card h-100" :class="templateId === '' ? 'border-primary' : ''">
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-file-earmark fs-3 text-muted"></i>
                                            <div class="fw-semibold small mt-2">Blank Site</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @foreach ($templates as $template)
                                <div class="col-md-4 col-6">
                                    <label class="w-100" style="cursor: pointer;">
                                        <input type="radio" class="btn-check" @click="templateId = '{{ $template->id }}'">
                                        <div class="card h-100" :class="templateId === '{{ $template->id }}' ? 'border-primary' : ''">
                                            @if ($template->preview_image)
                                                <img src="{{ $template->preview_image }}" class="card-img-top" alt="{{ $template->name }}" style="height: 100px; object-fit: cover; border-radius: calc(var(--bs-card-border-radius) - 1px) calc(var(--bs-card-border-radius) - 1px) 0 0;">
                                            @endif
                                            <div class="card-body py-3">
                                                <span class="badge text-bg-light text-dark border small mb-1">{{ $template->industry }}</span>
                                                <div class="fw-semibold small">{{ $template->name }}</div>
                                                <div class="text-muted" style="font-size: 0.75rem;">{{ $template->description }}</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="row g-2">
                            <div class="col-md-8">
                                <input type="text" name="name" class="form-control" placeholder="Website name (e.g. Acme Salon)" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">Create Website</button>
                            </div>
                        </div>
                    </form>
                @endunless
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body" x-data="{ seoOpen: false, socialOpen: false }">
                <form method="POST" action="{{ route('portal.website.update') }}" class="row g-2 align-items-end">
                    @csrf
                    @method('PATCH')
                    <div class="col-md-8">
                        <label class="form-label">Website Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $website->name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-outline-primary">Save</button>
                        <span class="badge text-bg-{{ $website->status === 'published' ? 'success' : 'warning' }} ms-2">
                            {{ ucfirst($website->status) }}
                        </span>
                    </div>

                    <div class="col-12 d-flex gap-3">
                        <button type="button" class="btn btn-link btn-sm px-0" @click="seoOpen = !seoOpen">
                            <i class="bi bi-search"></i> <span x-text="seoOpen ? 'Hide SEO & Branding fields' : 'SEO & Branding fields'"></span>
                        </button>
                        <button type="button" class="btn btn-link btn-sm px-0" @click="socialOpen = !socialOpen">
                            <i class="bi bi-share"></i> <span x-text="socialOpen ? 'Hide Social Links' : 'Social Links'"></span>
                        </button>
                    </div>
                    <div class="col-12" x-show="seoOpen" x-cloak>
                        <div class="row g-2">
                            <div class="col-md-12">
                                <label class="form-label">Meta Description <span class="text-muted small">(shown in search results)</span></label>
                                <input type="text" name="meta_description" class="form-control" maxlength="255" value="{{ old('meta_description', $website->meta_description) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Favicon URL</label>
                                <input type="url" name="favicon_path" class="form-control" placeholder="https://.../favicon.png" value="{{ old('favicon_path', $website->favicon_path) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Social Share Image (OG Image)</label>
                                <input type="url" name="og_image" class="form-control" placeholder="https://.../share-image.jpg" value="{{ old('og_image', $website->og_image) }}">
                                <div class="form-text">Falls back to your first page's hero image if left blank.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12" x-show="socialOpen" x-cloak>
                        <p class="text-muted small mb-2">Add links for any platforms you use - only the ones you fill in will show up in your site's footer.</p>
                        <div class="row g-2">
                            @foreach (\App\Models\Website::socialPlatforms() as $key => $platform)
                                <div class="col-md-6">
                                    <label class="form-label"><i class="bi {{ $platform['icon'] }}"></i> {{ $platform['label'] }}</label>
                                    <input type="url" name="social_links[{{ $key }}]" class="form-control" placeholder="{{ $platform['placeholder'] }}" value="{{ old('social_links.'.$key, $website->social_links[$key] ?? '') }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="mb-2 text-end">
            <a href="{{ route('portal.website.leads') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-envelope"></i> View Contact Form Leads
            </a>
        </div>

        <ul class="nav nav-tabs" id="websiteBuilderTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-pages" type="button">Pages</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-theme" type="button">Theme</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-preview" type="button" id="tab-preview-btn">Preview</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-publish" type="button">Publish</button></li>
        </ul>

        <div class="tab-content bg-white border border-top-0 p-4 shadow-sm">
            <div class="tab-pane fade show active" id="tab-pages">
                @include('portal.website._pages-tab')
            </div>
            <div class="tab-pane fade" id="tab-theme">
                @include('portal.website._theme-tab')
            </div>
            <div class="tab-pane fade" id="tab-preview">
                @include('portal.website._preview-tab')
            </div>
            <div class="tab-pane fade" id="tab-publish">
                @include('portal.website._publish-tab')
            </div>
        </div>
    @endif
</x-portal-layout>
