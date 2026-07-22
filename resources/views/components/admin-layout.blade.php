@props(['title' => 'Dashboard', 'breadcrumb' => null])
@php
    $showSurveyToolsSection = auth()->user()->can('viewAny', \App\Models\Survey::class)
        || auth()->user()->can('viewAny', \App\Models\SurveyTemplate::class)
        || auth()->user()->can('viewAny', \App\Models\SurveyTheme::class);

    $showQuickCreate = auth()->user()->can('create', \App\Models\Client::class)
        || auth()->user()->can('create', \App\Models\Survey::class)
        || auth()->user()->can('create', \App\Models\SurveyTemplate::class)
        || auth()->user()->can('create', \App\Models\Campaign::class);

    $recentNotifications = auth()->user()->notifications()->latest()->limit(10)->get();
    $unreadCount = auth()->user()->unreadNotifications()->count();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - Survyra Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" defer></script>
    <script>
        (function () {
            document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('ds-theme') || 'light');
        })();
    </script>
</head>
<body>
    <div class="d-flex">
        <nav id="ds-sidebar" class="ds-sidebar d-flex flex-column vh-100 position-sticky top-0 py-3">
            <div class="d-flex align-items-center justify-content-between px-3 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2 text-white text-decoration-none fs-5 fw-bold">
                    <i class="bi bi-hexagon-fill text-primary"></i>
                    <span class="ds-sidebar-brand-text">Survyra</span>
                </a>
                <button type="button" id="ds-sidebar-toggle" class="btn btn-sm btn-link text-white-50 p-0 border-0" title="Collapse sidebar">
                    <i class="bi bi-layout-sidebar-inset"></i>
                </button>
            </div>

            <div class="flex-grow-1 overflow-auto ds-scrollbar-thin px-2">
                <div class="ds-sidebar-section-title">Overview</div>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Dashboard">
                            <i class="bi bi-speedometer2"></i> <span class="ds-sidebar-label">Dashboard</span>
                        </a>
                    </li>
                    @can('view-analytics')
                    <li class="nav-item">
                        <a href="{{ route('admin.analytics.index') }}" class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}" title="Analytics">
                            <i class="bi bi-graph-up"></i> <span class="ds-sidebar-label">Analytics</span>
                        </a>
                    </li>
                    @endcan
                </ul>

                @can('viewAny', \App\Models\Client::class)
                <div class="ds-sidebar-section-title">Workspace</div>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a href="{{ route('admin.clients.index') }}" class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}" title="Clients">
                            <i class="bi bi-buildings"></i> <span class="ds-sidebar-label">Clients</span>
                        </a>
                    </li>
                    @can('viewAuditLog', \App\Models\User::class)
                    <li class="nav-item">
                        <a href="{{ route('admin.audit-log.index') }}" class="nav-link {{ request()->routeIs('admin.audit-log.*') ? 'active' : '' }}" title="Audit Log">
                            <i class="bi bi-journal-text"></i> <span class="ds-sidebar-label">Audit Log</span>
                        </a>
                    </li>
                    @endcan
                </ul>
                @endcan

                @if ($showSurveyToolsSection)
                <div class="ds-sidebar-section-title">Survey Tools</div>
                <ul class="nav flex-column mb-2">
                    @can('viewAny', \App\Models\Survey::class)
                    <li class="nav-item">
                        <a href="{{ route('admin.surveys.index') }}" class="nav-link {{ request()->routeIs('admin.surveys.*') ? 'active' : '' }}" title="Surveys">
                            <i class="bi bi-ui-checks"></i> <span class="ds-sidebar-label">Surveys</span>
                        </a>
                    </li>
                    @endcan
                    @can('viewAny', \App\Models\SurveyTemplate::class)
                    <li class="nav-item">
                        <a href="{{ route('admin.templates.index') }}" class="nav-link {{ request()->routeIs('admin.templates.*') ? 'active' : '' }}" title="Templates">
                            <i class="bi bi-clipboard-data"></i> <span class="ds-sidebar-label">Templates</span>
                        </a>
                    </li>
                    @endcan
                    @can('viewAny', \App\Models\SurveyTheme::class)
                    <li class="nav-item">
                        <a href="{{ route('admin.themes.index') }}" class="nav-link {{ request()->routeIs('admin.themes.*') ? 'active' : '' }}" title="Themes">
                            <i class="bi bi-palette"></i> <span class="ds-sidebar-label">Themes</span>
                        </a>
                    </li>
                    @endcan
                </ul>
                @endif

                @can('viewAny', \App\Models\Campaign::class)
                <div class="ds-sidebar-section-title">Engagement</div>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a href="{{ route('admin.campaigns.index') }}" class="nav-link {{ request()->routeIs('admin.campaigns.*') ? 'active' : '' }}" title="Campaigns">
                            <i class="bi bi-megaphone"></i> <span class="ds-sidebar-label">Campaigns</span>
                        </a>
                    </li>
                </ul>
                @endcan
            </div>

            <div class="px-2 pt-2 border-top border-secondary-subtle">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center gap-2 text-white text-decoration-none dropdown-toggle px-2 py-2 rounded" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-5"></i>
                        <span class="ds-sidebar-label small">{{ auth()->user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                        <li><a class="dropdown-item" href="{{ route('admin.profile.edit') }}">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Log Out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
            <header class="ds-topbar d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3" style="min-width: 0;">
                    @if ($breadcrumb)
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                @foreach ($breadcrumb as $crumb)
                                    @if (!$loop->last)
                                        <li class="breadcrumb-item"><a href="{{ $crumb['url'] }}" class="text-decoration-none">{{ $crumb['label'] }}</a></li>
                                    @else
                                        <li class="breadcrumb-item active fw-semibold" aria-current="page">{{ $crumb['label'] }}</li>
                                    @endif
                                @endforeach
                            </ol>
                        </nav>
                    @else
                        <h5 class="mb-0 fw-bold">{{ $title }}</h5>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2">
                    <div id="ds-search-wrapper" class="position-relative d-none d-md-block">
                        <i class="bi bi-search position-absolute" style="left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--ds-slate-400); font-size: 0.85rem;"></i>
                        <input
                            type="search"
                            id="ds-search-trigger"
                            class="ds-search-input"
                            placeholder="Search... (Ctrl+K)"
                            data-search-url="{{ route('admin.search') }}"
                            autocomplete="off"
                            readonly
                        >
                    </div>

                    @if ($showQuickCreate)
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-plus-lg"></i> <span class="d-none d-lg-inline">Quick Create</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            @can('create', \App\Models\Client::class)
                            <li><a class="dropdown-item" href="{{ route('admin.clients.create') }}"><i class="bi bi-buildings me-2"></i>New Client</a></li>
                            @endcan
                            @can('create', \App\Models\Survey::class)
                            <li><a class="dropdown-item" href="{{ route('admin.surveys.create') }}"><i class="bi bi-ui-checks me-2"></i>New Survey</a></li>
                            @endcan
                            @can('create', \App\Models\SurveyTemplate::class)
                            <li><a class="dropdown-item" href="{{ route('admin.templates.create') }}"><i class="bi bi-clipboard-data me-2"></i>New Template</a></li>
                            @endcan
                            @can('create', \App\Models\Campaign::class)
                            <li><a class="dropdown-item" href="{{ route('admin.campaigns.create') }}"><i class="bi bi-megaphone me-2"></i>New Campaign</a></li>
                            @endcan
                        </ul>
                    </div>
                    @endif

                    @include('notifications._bell', [
                        'notifications' => $recentNotifications,
                        'unreadCount' => $unreadCount,
                        'markReadUrlTemplate' => route('admin.notifications.read', ['id' => '__ID__']),
                    ])

                    <button type="button" id="ds-theme-toggle" class="ds-icon-btn" title="Toggle dark mode">
                        <i class="bi bi-moon-stars"></i>
                    </button>
                </div>
            </header>

            <main class="flex-grow-1 p-4">
                @if ($breadcrumb)
                    <div class="mb-4"><h2 class="mb-0">{{ $title }}</h2></div>
                @endif

                <x-alert />

                {{ $slot }}
            </main>
        </div>
    </div>

    <div id="command-palette-overlay" class="command-palette-overlay d-none">
        <div class="command-palette">
            <div class="command-palette-input-row">
                <i class="bi bi-search"></i>
                <input
                    type="search"
                    id="command-palette-input"
                    placeholder="Search clients, surveys, templates, campaigns..."
                    data-search-url="{{ route('admin.search') }}"
                    autocomplete="off"
                >
                <kbd>Esc</kbd>
            </div>
            <div id="command-palette-results" class="command-palette-results"></div>
        </div>
    </div>

    <script src="{{ asset('assets/js/toast.js') }}"></script>
    <script src="{{ asset('assets/js/admin-shell.js') }}"></script>
    @if (session('status'))
        <script>document.addEventListener('DOMContentLoaded', () => Toast.success(@json(session('status'))));</script>
    @endif
    @stack('scripts')
</body>
</html>
