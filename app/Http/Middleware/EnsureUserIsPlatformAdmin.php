<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPlatformAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, __('Unauthorized.'));
        }

        $mode = config('scool.deployment_mode', 'general');

        if ($mode === 'expert') {
            if (! $user->isDeploymentOwner()) {
                abort(403, __('Unauthorized. Deployment Owner privileges required in expert mode.'));
            }
        } elseif ($mode === 'general') {
            if (! $user->isPlatformAdmin()) {
                abort(403, __('Unauthorized. Platform Administrator privileges required.'));
            }
        } else {
            abort(403, __('Unauthorized. Invalid deployment mode.'));
        }

        return $next($request);
    }
}
