@props(['title' => 'Dashboard'])
@php
    $recentNotifications = auth('client')->user()->notifications()->latest()->limit(10)->get();
    $unreadCount = auth('client')->user()->unreadNotifications()->count();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - Survyra Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('ds-theme') || 'dark');
        })();
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('portal.dashboard') }}">
                <i class="bi bi-hexagon-fill"></i> Survyra Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#portalNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="portalNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}" href="{{ route('portal.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('portal.company.*') ? 'active' : '' }}" href="{{ route('portal.company.edit') }}">Company Profile</a>
                    </li>
                </ul>
                <ul class="navbar-nav align-items-lg-center gap-lg-2">
                    <li class="nav-item">
                        @include('notifications._bell', [
                            'notifications' => $recentNotifications,
                            'unreadCount' => $unreadCount,
                            'markReadUrlTemplate' => route('portal.notifications.read', ['id' => '__ID__']),
                        ])
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> {{ auth('client')->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('portal.profile.edit') }}">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('portal.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Log Out</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <div class="mb-4">
            <h2 class="mb-0">{{ $title }}</h2>
        </div>

        @include('portal.partials.onboarding-checklist')

        <x-alert />

        {{ $slot }}
    </main>

    <script src="{{ asset('assets/js/toast.js') }}"></script>
    <script src="{{ asset('assets/js/admin-shell.js') }}" defer></script>
    @if (session('status'))
        <script>document.addEventListener('DOMContentLoaded', () => Toast.success(@json(session('status'))));</script>
    @endif
    @stack('scripts')
</body>
</html>
