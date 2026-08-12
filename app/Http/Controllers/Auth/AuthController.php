<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        $quickLoginUsers = User::with('role')->where('is_active', true)->orderBy('full_name')->get();

        return view('auth.login', compact('quickLoginUsers'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'staff_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['staff_id' => 'Those credentials do not match our records.'])
                ->onlyInput('staff_id');
        }

        $request->session()->regenerate();
        Auth::user()->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('admin.dashboard'));
    }

    // TODO: remove once real Microsoft SSO is live
    public function quickLogin(Request $request)
    {
        $data = $request->validate([
            'staff_id' => ['required', 'string', 'exists:users,staff_id'],
        ]);

        $user = User::where('staff_id', $data['staff_id'])->first();

        if (! $user || ! $user->is_active) {
            return back()->withErrors(['staff_id' => 'That account is not available for quick login.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

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
