<x-guest-layout title="Two-Factor Verification">
    <div class="text-center mb-4">
        <div class="ds-kpi-icon bg-primary-subtle text-primary mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
            <i class="bi bi-shield-check"></i>
        </div>
        <h5 class="mb-1">Two-Factor Verification</h5>
        <p class="text-muted small mb-0">Enter the 6-digit code from your authenticator app, or a recovery code.</p>
    </div>

    <x-alert />

    <form method="POST" action="{{ route('admin.two-factor.challenge.store') }}">
        @csrf

        <x-form-input name="code" label="Authentication code" type="text" required autofocus autocomplete="one-time-code" inputmode="numeric" />

        <button type="submit" class="btn btn-primary w-100 mb-2">Verify</button>

        <div class="text-center">
            <a href="{{ route('admin.login') }}" class="small text-decoration-none">Back to login</a>
        </div>
    </form>
</x-guest-layout>
