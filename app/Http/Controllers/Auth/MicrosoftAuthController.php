<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Sso\AdminAccountResolver;
use App\Support\Sso\MicrosoftEntraClient;
use App\Support\Sso\ProfileLinker;
use App\Support\Sso\SsoException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Admin panel sign-in through Microsoft Entra ID. Only served while
 * SSO_ENABLED=true; AuthController's password login covers the other case.
 */
class MicrosoftAuthController extends Controller
{
    public function __construct(
        private readonly MicrosoftEntraClient $entra,
        private readonly AdminAccountResolver $accounts,
        private readonly ProfileLinker $linker,
    ) {}

    /**
     * Hands the browser to Microsoft. A POST behind the CSRF token, so another
     * site can't start a sign-in in someone's browser.
     */
    public function redirect(Request $request)
    {
        abort_unless(config('sso.enabled'), 404);

        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        try {
            return redirect()->away($this->entra->authorizationUrl($request->session()));
        } catch (SsoException $e) {
            return $this->refuse($request, $e);
        }
    }

    public function callback(Request $request)
    {
        abort_unless(config('sso.enabled'), 404);

        try {
            $profile = $this->entra->handleCallback($request);
            $user = $this->accounts->resolve($profile);
        } catch (SsoException $e) {
            return $this->refuse($request, $e);
        } catch (Throwable $e) {
            report($e);

            return $this->refuse($request, new SsoException('Unexpected error during Microsoft sign-in'));
        }

        // The first Microsoft sign-in links the account to its Entra object
        // ID, so it's still found if the address later changes in Entra, and
        // fills in the name and staff ID Manage Staff left for Microsoft.
        $user = $this->linker->link($user, $profile);

        Auth::login($user);
        $request->session()->regenerate();

        Log::info('Admin signed in with Microsoft', ['staff_id' => $user->staff_id]);

        return redirect()->intended(route('admin.dashboard'));
    }

    private function refuse(Request $request, SsoException $e)
    {
        Log::log(
            $e->userMessage() === SsoException::FAILED ? 'error' : 'warning',
            'Microsoft sign-in refused: '.$e->getMessage(),
            $e->context() + ['ip' => $request->ip()],
        );

        return redirect()->route('login')->withErrors(['sso' => $e->userMessage()]);
    }
}
