<?php

namespace App\Http\Controllers;

use App\Domain\Auth\AuthenticateUserAction;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;

/**
 * Handles user authentication using the custom login table.
 * Stores a small user payload in session to drive role-based access.
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthenticateUserAction $authenticateUser
    ) {}

    /**
     * Show the login page
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Process login form and create a simple session payload
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $sessionPayload = $this->authenticateUser->handle(
            $credentials['username'],
            $credentials['password']
        );

        // Store minimal user info in session (used for dashboards/guards)
        $request->session()->put('auth_user', $sessionPayload);

        return redirect()->route('dashboard');
    }

    /**
     * Destroy session and redirect to login
     */
    public function logout(Request $request)
    {
        $request->session()->forget('auth_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
