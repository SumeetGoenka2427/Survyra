@php
    $navId = 'wsNav'.$website->id;
    $metaDescription = $page['meta_description'] ?? $snapshot['meta_description'] ?? null;
    $ogImage = $snapshot['og_image'] ?? null;
    $favicon = $snapshot['favicon_path'] ?? null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $page['title'] }} - {{ $snapshot['name'] }}</title>
    @if ($metaDescription)
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @if ($favicon)
        <link rel="icon" href="{{ $favicon }}">
    @endif

    {{-- Open Graph / Twitter Card --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $page['title'] }} - {{ $snapshot['name'] }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if ($metaDescription)
        <meta property="og:description" content="{{ $metaDescription }}">
    @endif
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
        <meta name="twitter:card" content="summary_large_image">
    @else
        <meta name="twitter:card" content="summary">
    @endif
    <meta name="twitter:title" content="{{ $page['title'] }} - {{ $snapshot['name'] }}">
    @if ($metaDescription)
        <meta name="twitter:description" content="{{ $metaDescription }}">
    @endif

    {{-- Structured data --}}
    <script type="application/ld+json">
        {!! json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $snapshot['name'],
            'url' => route('website.show', $website->slug),
            'description' => $snapshot['meta_description'] ?? null,
            'logo' => $favicon,
        ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $snapshot['name'],
            'url' => route('website.show', $website->slug),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @unless ($page['is_home'])
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => $snapshot['name'], 'item' => route('website.show', $website->slug)],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => $page['title'], 'item' => $canonicalUrl],
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endunless

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&family=Playfair+Display:wght@600;700&family=Nunito+Sans:wght@600;700&display=swap" rel="stylesheet">
    <style>
        .ws-navbar { box-shadow: 0 1px 0 var(--ws-border); backdrop-filter: saturate(180%) blur(6px); }
        .ws-brand { font-family: var(--ws-heading-font); font-weight: 800; font-size: 1.35rem; color: var(--ws-text) !important; }
        .ws-navbar .nav-link { font-weight: 500; color: var(--ws-text); }
        .ws-navbar .nav-link:hover, .ws-navbar .nav-link.active { color: var(--ws-primary); }
        .ws-footer { background: var(--ws-alt-bg); border-top: 1px solid var(--ws-border); padding: 3.5rem 0 1.5rem; }
        .ws-footer-brand { font-family: var(--ws-heading-font); font-weight: 800; font-size: 1.25rem; color: var(--ws-text); }
        .ws-footer a { color: var(--ws-muted); text-decoration: none; }
        .ws-footer a:hover { color: var(--ws-primary); }
        .ws-footer-heading { font-family: var(--ws-heading-font); font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--ws-text); margin-bottom: 1rem; }
        .ws-footer-links { list-style: none; padding: 0; margin: 0; }
        .ws-footer-links li { margin-bottom: 0.6rem; }
        .ws-footer-bottom { border-top: 1px solid var(--ws-border); margin-top: 2.5rem; padding-top: 1.5rem; }
        .ws-social-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 40px; height: 40px; border-radius: 50%;
            background: rgba(var(--ws-primary-rgb), 0.1); color: var(--ws-primary) !important;
            font-size: 1.1rem; transition: transform 0.15s ease, background 0.15s ease;
        }
        .ws-social-icon:hover { background: var(--ws-primary); color: #fff !important; transform: translateY(-2px); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg ws-navbar sticky-top py-3">
        <div class="container">
            <a class="navbar-brand ws-brand" href="{{ route('website.show', $website->slug) }}">{{ $snapshot['name'] }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $navId }}" aria-controls="{{ $navId }}" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="{{ $navId }}">
                <ul class="navbar-nav ms-auto gap-lg-1">
                    @foreach ($snapshot['pages'] as $navPage)
                        <li class="nav-item">
                            <a class="nav-link px-3 @if($navPage['id'] === $page['id']) active @endif"
                               href="{{ $navPage['is_home'] ? route('website.show', $website->slug) : route('website.show.page', [$website->slug, $navPage['slug']]) }}">
                                {{ $navPage['title'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </nav>

    @include('website._page-body', [
        'theme' => $snapshot['theme'] ?? [],
        'sections' => $page['sections'],
        'pageTitle' => $page['title'],
        'pageId' => $page['id'],
        'contactAction' => route('website.contact.store', $website->slug),
        'isPreview' => false,
    ])

    <footer class="ws-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6">
                    <span class="ws-footer-brand d-block mb-2">{{ $snapshot['name'] }}</span>
                    @if ($snapshot['meta_description'] ?? null)
                        <p class="small mb-3" style="color: var(--ws-muted); max-width: 420px;">{{ $snapshot['meta_description'] }}</p>
                    @endif
                    @include('website._social-icons', ['socialLinks' => $snapshot['social_links'] ?? []])
                </div>
                <div class="col-md-6">
                    <span class="ws-footer-heading d-block">Quick Links</span>
                    <ul class="ws-footer-links row row-cols-2 g-0">
                        @foreach ($snapshot['pages'] as $navPage)
                            <li class="col">
                                <a href="{{ $navPage['is_home'] ? route('website.show', $website->slug) : route('website.show.page', [$website->slug, $navPage['slug']]) }}">
                                    {{ $navPage['title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="ws-footer-bottom">
                <p class="small mb-0" style="color: var(--ws-muted);">&copy; {{ date('Y') }} {{ $snapshot['name'] }}. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
