<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class TwoFactorController extends Controller
{
    public function __construct(private readonly TwoFactorAuthService $twoFactor) {}

    /**
     * Show 2FA setup page.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('admin.profile.two-factor', [
            'user' => $user,
            'enabled' => $user->two_factor_enabled ?? false,
        ]);
    }

    /**
     * Generate a new secret and show QR code.
     */
    public function setup(Request $request): View
    {
        $user = $request->user();
        $secret = $this->twoFactor->generateSecret();
        $qrUrl = $this->twoFactor->qrCodeUrl($user->email, 'Survyra', $secret);

        // Store secret temporarily in session
        session(['two_factor_pending_secret' => $secret]);

        return view('admin.profile.two-factor-setup', [
            'secret' => $secret,
            'qrUrl' => $qrUrl,
        ]);
    }

    /**
     * Confirm and enable 2FA.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $secret = session('two_factor_pending_secret');

        if (! $secret) {
            return back()->withErrors(['code' => 'Session expired. Please start again.']);
        }

        try {
            $result = $this->twoFactor->enable($request->user(), $secret, $request->input('code'));
            session()->forget('two_factor_pending_secret');

            return redirect()->route('admin.profile.two-factor')
                ->with('status', 'Two-factor authentication enabled.')
                ->with('recovery_codes', $result['recovery_codes']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['code' => $e->getMessage()]);
        }
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        if (! $this->twoFactor->verifyForUser($request->user(), $request->input('code'))) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        $this->twoFactor->disable($request->user());

        return redirect()->route('admin.profile.two-factor')
            ->with('status', 'Two-factor authentication disabled.');
    }

    /**
     * Show recovery codes.
     */
    public function recoveryCodes(Request $request): View
    {
        $codes = [];
        if ($request->user()->two_factor_recovery_codes) {
            $codes = json_decode(decrypt($request->user()->two_factor_recovery_codes), true);
        }

        return view('admin.profile.two-factor-recovery', ['codes' => $codes]);
    }
}