<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Client-portal "viewer" role is read-only by design (ClientUser::isEditor()
 * already draws this line as owner|editor vs. viewer) - but until now that
 * line was only ever checked inside TeamController. This applies it to every
 * other write action a portal user can reach (company profile, API keys,
 * webhooks, Slack integration, scheduled reports).
 */
class EnsureClientCanEdit
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('client')->user();

        abort_unless($user && $user->isEditor(), 403, 'Viewers do not have permission to make changes.');

        return $next($request);
    }
}
