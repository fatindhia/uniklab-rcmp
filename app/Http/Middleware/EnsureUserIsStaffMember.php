<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the whole admin panel to active accounts holding a panel role.
 *
 * Checked on every request, not only at sign-in, so deactivating someone or
 * taking their role away in Manage Staff takes effect on their next click —
 * including a session resumed from a "keep me signed in" cookie, which never
 * passes through the login form at all.
 */
class EnsureUserIsStaffMember
{
    public const NOT_AUTHORISED_MESSAGE = 'You are not authorised to access the administrator system. Please contact the system administrator if you require access.';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && (! $user->is_active || ! $user->isStaffMember())) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                abort(403);
            }

            return redirect()->route('login')->withErrors(['staff_id' => self::NOT_AUTHORISED_MESSAGE]);
        }

        return $next($request);
    }
}
