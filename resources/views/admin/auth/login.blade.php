<x-guest-layout title="Admin Login">
    <div class="text-center mb-4">
        <div class="ds-kpi-icon bg-primary-subtle text-primary mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
            <i class="bi bi-shield-lock"></i>
        </div>
        <h5 class="mb-1">Admin Login</h5>
        <p class="text-muted small mb-0">Sign in to manage clients, surveys, and campaigns.</p>
    </div>

    <x-alert />

    <form method="POST" action="{{ route('admin.login') }}">
        @csrf

        <x-form-input name="email" label="Email" type="email" required autofocus autocomplete="username" />
        <x-form-input name="password" label="Password" type="password" required autocomplete="current-password" />

        <div class="form-check mb-3">
            <input type="checkbox" name="remember" id="remember" class="form-check-input">
            <label for="remember" class="form-check-label">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-2">Log in</button>

        <div class="text-center">
            <a href="{{ route('admin.password.request') }}" class="small text-decoration-none">Forgot your password?</a>
        </div>
    </form>
</x-guest-layout>
