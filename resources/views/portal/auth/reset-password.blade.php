<x-guest-layout title="Reset Password">
    <div class="text-center mb-4">
        <div class="ds-kpi-icon bg-success-subtle text-success mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
            <i class="bi bi-key"></i>
        </div>
        <h5 class="mb-1">Reset Password</h5>
        <p class="text-muted small mb-0">Choose a new password for your account.</p>
    </div>

    <x-alert />

    <form method="POST" action="{{ route('portal.password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-form-input name="email" label="Email" type="email" :value="$request->email" required autofocus />
        <x-form-input name="password" label="New Password" type="password" required autocomplete="new-password" />
        <x-form-input name="password_confirmation" label="Confirm Password" type="password" required autocomplete="new-password" />

        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
    </form>
</x-guest-layout>
