<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureUserIsStaffMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Staff ID + password sign-in, for local, Docker and maintenance use. Off
     * while SSO_ENABLED=true, so it can't become a way around Microsoft
     * sign-in in production.
     */
    public function login(Request $request)
    {
        abort_if(config('sso.enabled'), 404);

        $credentials = $request->validate([
            'staff_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // is_active is part of the lookup, not a check after the fact: a
        // deactivated account should fail exactly like a wrong password, with
        // no hint that the ID itself is real.
        $credentials['is_active'] = true;

        // The role check only runs once the password has matched, so telling
        // the person why they were turned away gives nothing away.
        $lacksPanelRole = false;
        $signedIn = Auth::attemptWhen($credentials, function (User $user) use (&$lacksPanelRole) {
            $lacksPanelRole = ! $user->isStaffMember();

            return ! $lacksPanelRole;
        }, $request->boolean('remember'));

        if ($lacksPanelRole) {
            Log::warning('Local sign-in refused: account holds no admin panel role', [
                'staff_id' => $credentials['staff_id'],
                'ip' => $request->ip(),
            ]);

            return back()
                ->withErrors(['staff_id' => EnsureUserIsStaffMember::NOT_AUTHORISED_MESSAGE])
                ->onlyInput('staff_id');
        }

        if (! $signedIn) {
            return back()
                ->withErrors(['staff_id' => 'Those credentials do not match our records.'])
                ->onlyInput('staff_id');
        }

        $request->session()->regenerate();
        Auth::user()->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
