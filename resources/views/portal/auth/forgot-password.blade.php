<x-guest-layout title="Forgot Password">
    <div class="text-center mb-4">
        <div class="ds-kpi-icon bg-warning-subtle text-warning mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
            <i class="bi bi-envelope-paper"></i>
        </div>
        <h5 class="mb-1">Forgot Password</h5>
        <p class="text-muted small mb-0">Enter your email and we'll send you a password reset link.</p>
    </div>

    <x-alert />

    <form method="POST" action="{{ route('portal.password.email') }}">
        @csrf

        <x-form-input name="email" label="Email" type="email" required autofocus />

        <button type="submit" class="btn btn-primary w-100">Email Password Reset Link</button>
    </form>
</x-guest-layout>
