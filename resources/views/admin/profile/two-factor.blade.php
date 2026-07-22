<x-admin-layout title="Two-Factor Authentication">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if (session('recovery_codes'))
        <div class="alert alert-warning">
            <strong><i class="bi bi-exclamation-triangle me-1"></i> Save These Recovery Codes</strong>
            <p class="small mb-2">Each code can be used once to access your account if you lose your device.</p>
            <div class="bg-dark text-light p-3 rounded font-monospace small">
                @foreach (session('recovery_codes') as $code)
                    <div>{{ $code }}</div>
                @endforeach
            </div>
            <form method="POST" action="{{ route('admin.profile.two-factor.recovery-codes') }}" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light">Download Recovery Codes</button>
            </form>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center mb-4">
                <i class="bi bi-shield-lock fs-1 me-3 text-primary"></i>
                <div>
                    <h5 class="mb-1">Two-Factor Authentication</h5>
                    <p class="text-muted mb-0">Add an extra layer of security to your account using an authenticator app.</p>
                </div>
            </div>

            @if ($enabled)
                <div class="alert alert-success d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Two-factor authentication is <strong>enabled</strong>.
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.profile.two-factor.recovery-codes') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-key me-1"></i> View Recovery Codes
                    </a>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#disable2fa">
                        <i class="bi bi-x-circle me-1"></i> Disable 2FA
                    </button>
                </div>

                <div class="collapse mt-3" id="disable2fa">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.profile.two-factor.disable') }}" class="row g-3">
                                @csrf
                                <div class="col-md-4">
                                    <label class="form-label small">Enter code from authenticator app</label>
                                    <input type="text" name="code" class="form-control" placeholder="000000" required maxlength="6" pattern="[0-9]{6}">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-danger">Disable 2FA</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <p class="mb-3">Two-factor authentication is currently <strong>disabled</strong>.</p>
                <a href="{{ route('admin.profile.two-factor.setup') }}" class="btn btn-primary">
                    <i class="bi bi-qr-code me-1"></i> Set Up Two-Factor Authentication
                </a>
            @endif
        </div>
    </div>
</x-admin-layout>