@php
    $navId = 'wsPreviewNav';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Preview - {{ $page['title'] }} - {{ $snapshot['name'] }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&family=Playfair+Display:wght@600;700&family=Nunito+Sans:wght@600;700&display=swap" rel="stylesheet">
    <style>
        .ws-navbar { box-shadow: 0 1px 0 var(--ws-border); }
        .ws-brand { font-family: var(--ws-heading-font); font-weight: 800; font-size: 1.2rem; color: var(--ws-text) !important; }
        .ws-navbar .nav-link { font-weight: 500; color: var(--ws-text); font-size: 0.9rem; }
        .ws-navbar .nav-link.active { color: var(--ws-primary); }
        .ws-preview-banner { background: #111827; color: #fff; text-align: center; font-size: 0.75rem; padding: 0.35rem; letter-spacing: 0.03em; }
        .ws-footer { background: var(--ws-alt-bg); border-top: 1px solid var(--ws-border); padding: 2.5rem 0 1.5rem; }
        .ws-footer-brand { font-family: var(--ws-heading-font); font-weight: 800; font-size: 1.1rem; color: var(--ws-text); }
        .ws-footer a { color: var(--ws-muted); text-decoration: none; }
        .ws-footer a:hover { color: var(--ws-primary); }
        .ws-social-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 50%;
            background: rgba(var(--ws-primary-rgb), 0.1); color: var(--ws-primary) !important;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="ws-preview-banner"><i class="bi bi-eye"></i> DRAFT PREVIEW - not the published site</div>
    <nav class="navbar navbar-expand-lg ws-navbar py-2">
        <div class="container">
            <span class="navbar-brand ws-brand">{{ $snapshot['name'] }}</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $navId }}" aria-controls="{{ $navId }}" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="{{ $navId }}">
                <ul class="navbar-nav ms-auto">
                    @foreach ($snapshot['pages'] as $navPage)
                        <li class="nav-item">
                            <a class="nav-link px-2 @if($navPage['id'] === $page['id']) active @endif"
                               href="{{ route('portal.website.preview', array_filter(['page' => $navPage['is_home'] ? null : $navPage['slug']])) }}">
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
        'contactAction' => null,
        'isPreview' => true,
    ])

    <footer class="ws-footer">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
            <span class="ws-footer-brand">{{ $snapshot['name'] }}</span>
            @include('website._social-icons', ['socialLinks' => $snapshot['social_links'] ?? []])
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
