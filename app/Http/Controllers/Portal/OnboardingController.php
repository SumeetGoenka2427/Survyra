<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\OnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function __construct(private readonly OnboardingService $onboarding) {}

    public function dismiss(Request $request): RedirectResponse
    {
        $this->onboarding->dismiss($request->user()->client);

        return back()->with('status', 'Onboarding checklist dismissed.');
    }
}