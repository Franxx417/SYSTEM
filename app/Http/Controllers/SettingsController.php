<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        $auth = session('auth_user');
        $user = null;

        if ($auth && isset($auth['user_id'])) {
            $user = User::find($auth['user_id']);
        }

        // Get current logo from SystemSetting
        $currentLogo = SystemSetting::getValue('branding.logo_path') ?: null;

        return view('settings.index', compact('auth', 'user', 'currentLogo'));
    }

    /**
     * Update user profile information
     */
    public function updateProfile(Request $request)
    {
        $auth = session('auth_user');

        if (! $auth || ! isset($auth['user_id'])) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$auth['user_id'].',user_id',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::find($auth['user_id']);
        if (! $user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'position' => $request->position,
            'department' => $request->department,
        ]);

        // Update session data
        $auth['name'] = $request->name;
        $auth['email'] = $request->email;
        $auth['position'] = $request->position;
        $auth['department'] = $request->department;
        session(['auth_user' => $auth]);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $auth = session('auth_user');

        if (! $auth || ! isset($auth['user_id'])) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $user = User::find($auth['user_id']);
        if (! $user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        // Verify current password
        if (! Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    /**
     * Upload company logo
     */
    public function uploadLogo(Request $request)
    {
        abort(404);
    }

    /**
     * Remove company logo
     */
    public function removeLogo(Request $request)
    {
        abort(404);
    }

    /**
     * Generate dynamic status CSS from database
     * NOTE: Color functionality has been removed - returns minimal CSS
     */
    public function dynamicStatusCss()
    {
        // Color functionality has been removed from the system
        // Return minimal CSS to prevent errors
        $css = "/* Status CSS - Color indicators disabled */\n";
        $css .= "/* All status styling is now text-based */\n";

        return response($css, 200, [
            'Content-Type' => 'text/css',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Calculate contrast color (black or white) based on background color
     */
    private function getContrastColor($hexColor)
    {
        // Remove # if present
        $hexColor = ltrim($hexColor, '#');

        // Convert to RGB
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));

        // Calculate luminance
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        // Return black for light backgrounds, white for dark backgrounds
        return $luminance > 0.5 ? '#000' : '#fff';
    }

    /**
     * Get default status CSS as fallback
     */
    private function getDefaultStatusCSS()
    {
        return '
        .status-pending { background-color: #ffc107; color: #000; }
        .status-verified { background-color: #17a2b8; color: #fff; }
        .status-approved { background-color: #28a745; color: #fff; }
        .status-rejected { background-color: #dc3545; color: #fff; }
        .status-received { background-color: #6f42c1; color: #fff; }
        .status-cancelled { background-color: #6c757d; color: #fff; }
        
        .badge.status-pending { background-color: #ffc107 !important; color: #000 !important; }
        .badge.status-verified { background-color: #17a2b8 !important; color: #fff !important; }
        .badge.status-approved { background-color: #28a745 !important; color: #fff !important; }
        .badge.status-rejected { background-color: #dc3545 !important; color: #fff !important; }
        .badge.status-received { background-color: #6f42c1 !important; color: #fff !important; }
        .badge.status-cancelled { background-color: #6c757d !important; color: #fff !important; }
        ';
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request)
    {
        abort(404);
    }

    /**
     * Get user preferences
     */
    public function getPreferences()
    {
        abort(404);
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        abort(404);
    }

    /**
     * Get notification settings
     */
    public function getNotifications()
    {
        abort(404);
    }
}
