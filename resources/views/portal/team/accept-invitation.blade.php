<x-guest-layout>
    <div class="card border-0 shadow-sm" style="max-width:420px;margin:auto;">
        <div class="card-body p-4">
            <h5 class="mb-1">Accept Invitation</h5>
            <p class="text-muted small mb-4">Set your password to activate your account at <strong>{{ $member->client->company_name }}</strong>.</p>

            <form method="POST" action="{{ route('portal.team.complete-invitation', $token) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Activate Account</button>
            </form>
        </div>
    </div>
</x-guest-layout>
