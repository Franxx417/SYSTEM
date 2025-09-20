<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Handles user authentication using the custom login table.
 * Stores a small user payload in session to drive role-based access.
 */
class AuthController extends Controller
{
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
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $row = DB::table('login')
            ->join('users', 'login.user_id', '=', 'users.user_id')
            ->leftJoin('roles', 'roles.user_id', '=', 'users.user_id')
            ->leftJoin('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->select('login.*', 'users.name', 'users.email', 'users.position', 'users.department', 'role_types.user_role_type')
            ->where('login.username', $credentials['username'])
            ->first();

        if (!$row || !Hash::check($credentials['password'], $row->password)) {
            throw ValidationException::withMessages([
                'username' => 'The provided credentials are incorrect.',
            ]);
        }

        // Store minimal user info in session (used for dashboards/guards)
        $request->session()->put('auth_user', [
            'user_id' => $row->user_id,
            'name' => $row->name,
            'email' => $row->email,
            'position' => $row->position,
            'department' => $row->department,
            'role' => $row->user_role_type,
        ]);

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




