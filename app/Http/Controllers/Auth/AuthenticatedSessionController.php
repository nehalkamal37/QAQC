<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show login page.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Attempt authentication
        $request->authenticate();

        // Regenerate session after successful login
        $request->session()->regenerate();

        // Redirect to dashboard or intended page
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Handle logout.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        // Invalidate session and regenerate token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been logged out.');
    }
}
