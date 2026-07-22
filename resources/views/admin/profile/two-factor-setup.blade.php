<x-admin-layout title="Set Up Two-Factor Authentication">
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center">
            <h5 class="mb-3">Scan QR Code</h5>
            <p class="text-muted small mb-4">
                Scan this QR code with your authenticator app (e.g., Google Authenticator, Authy, or Microsoft Authenticator).
            </p>

            <div class="d-inline-block p-3 bg-light rounded mb-3">
                <img src="data:image/svg+xml,{{ $qrUrl }}" alt="QR Code" class="img-fluid" style="max-width: 200px;">
            </div>

            <div class="mb-4">
                <p class="small text-muted mb-1">Or enter this key manually:</p>
                <code class="bg-dark text-light p-2 rounded d-inline-block user-select-all">{{ $secret }}</code>
            </div>

            <form method="POST" action="{{ route('admin.profile.two-factor.confirm') }}" class="row justify-content-center g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Verify code from app</label>
                    <input type="text" name="code" class="form-control text-center fs-4"
                           placeholder="000000" required maxlength="6" pattern="[0-9]{6}"
                           inputmode="numeric" autocomplete="one-time-code">
                    @error('code')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-shield-check me-1"></i> Enable Two-Factor Authentication
                    </button>
                </div>
            </form>

            <div class="mt-4">
                <a href="{{ route('admin.profile.two-factor') }}" class="text-muted small">
                    <i class="bi bi-arrow-left me-1"></i> Back to 2FA settings
                </a>
            </div>
        </div>
    </div>
</x-admin-layout>