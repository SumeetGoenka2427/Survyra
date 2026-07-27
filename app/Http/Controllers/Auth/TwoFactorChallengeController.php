<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorChallengeController extends Controller
{
    public function __construct(private readonly TwoFactorAuthService $twoFactor)
    {
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('2fa_user_id')) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.two-factor-challenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('2fa_user_id');

        if (! $userId) {
            return redirect()->route('admin.login');
        }

        $request->validate(['code' => ['required', 'string']]);

        $user = User::findOrFail($userId);

        if (! $this->twoFactor->verifyForUser($user, $request->string('code')->toString())) {
            throw ValidationException::withMessages([
                'code' => 'That code is invalid. Please try again.',
            ]);
        }

        $remember = $request->session()->pull('2fa_remember', false);
        $request->session()->forget('2fa_user_id');

        Auth::guard('web')->login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }
}
