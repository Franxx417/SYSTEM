<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Setting;

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
        
        // Get current logo
        $currentLogo = Setting::getCompanyLogo();
        
        return view('settings.index', compact('auth', 'user', 'currentLogo'));
    }
    
    /**
     * Update user profile information
     */
    public function updateProfile(Request $request)
    {
        $auth = session('auth_user');
        
        if (!$auth || !isset($auth['user_id'])) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $auth['user_id'],
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $user = User::find($auth['user_id']);
        if (!$user) {
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
        
        if (!$auth || !isset($auth['user_id'])) {
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
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }
        
        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
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
        $auth = session('auth_user');
        
        if (!$auth) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }
        
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,jpg,png,gif,svg,webp|max:2048', // 2MB max
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        
        try {
            // Delete old logo if exists
            $oldLogo = Setting::getCompanyLogo();
            if ($oldLogo && Storage::exists(str_replace('/storage/', 'public/', $oldLogo))) {
                Storage::delete(str_replace('/storage/', 'public/', $oldLogo));
            }
            
            // Store new logo
            $path = $request->file('logo')->store('public/logos');
            $publicPath = Storage::url($path);
            
            // Save to settings
            Setting::set('company_logo', $publicPath);
            Setting::set('branding.logo_path', $publicPath); // Also save to branding path for consistency
            
            return redirect()->back()->with('success', 'Logo uploaded successfully.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to upload logo: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove company logo
     */
    public function removeLogo(Request $request)
    {
        $auth = session('auth_user');
        
        if (!$auth) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }
        
        try {
            // Delete logo file if exists
            $currentLogo = Setting::getCompanyLogo();
            if ($currentLogo && Storage::exists(str_replace('/storage/', 'public/', $currentLogo))) {
                Storage::delete(str_replace('/storage/', 'public/', $currentLogo));
            }
            
            // Remove from settings
            Setting::where('key', 'company_logo')->delete();
            Setting::where('key', 'branding.logo_path')->delete();
            
            return redirect()->back()->with('success', 'Logo removed successfully.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to remove logo: ' . $e->getMessage());
        }
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
        return "
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
        ";
    }
    
    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request)
    {
        $auth = session('auth_user');
        
        if (!$auth || !isset($auth['user_id'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $validator = Validator::make($request->all(), [
            'language' => 'required|in:en,fil,zh',
            'date_format' => 'required|in:MM/DD/YYYY,DD/MM/YYYY,YYYY-MM-DD',
            'time_format' => 'required|in:12,24',
            'timezone' => 'required|string|max:100',
            'auto_save' => 'boolean',
            'compact_view' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $userId = $auth['user_id'];
            
            // Store preferences in settings table with user-specific keys
            Setting::set("user.{$userId}.language", $request->language);
            Setting::set("user.{$userId}.date_format", $request->date_format);
            Setting::set("user.{$userId}.time_format", $request->time_format);
            Setting::set("user.{$userId}.timezone", $request->timezone);
            Setting::set("user.{$userId}.auto_save", $request->boolean('auto_save'));
            Setting::set("user.{$userId}.compact_view", $request->boolean('compact_view'));
            
            return response()->json([
                'success' => true,
                'message' => 'Preferences saved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save preferences: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get user preferences
     */
    public function getPreferences()
    {
        $auth = session('auth_user');
        
        if (!$auth || !isset($auth['user_id'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $userId = $auth['user_id'];
        
        $preferences = [
            'language' => Setting::get("user.{$userId}.language", 'en'),
            'date_format' => Setting::get("user.{$userId}.date_format", 'DD/MM/YYYY'),
            'time_format' => Setting::get("user.{$userId}.time_format", '12'),
            'timezone' => Setting::get("user.{$userId}.timezone", 'Asia/Manila'),
            'auto_save' => (bool) Setting::get("user.{$userId}.auto_save", true),
            'compact_view' => (bool) Setting::get("user.{$userId}.compact_view", false),
        ];
        
        return response()->json([
            'success' => true,
            'preferences' => $preferences
        ]);
    }
    
    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        $auth = session('auth_user');
        
        if (!$auth || !isset($auth['user_id'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $validator = Validator::make($request->all(), [
            'notif_po_created' => 'boolean',
            'notif_po_approved' => 'boolean',
            'notif_po_rejected' => 'boolean',
            'notif_system_updates' => 'boolean',
            'notif_security' => 'boolean',
            'email_daily_summary' => 'boolean',
            'email_weekly_report' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $userId = $auth['user_id'];
            
            // Store notification preferences
            Setting::set("user.{$userId}.notif_po_created", $request->boolean('notif_po_created'));
            Setting::set("user.{$userId}.notif_po_approved", $request->boolean('notif_po_approved'));
            Setting::set("user.{$userId}.notif_po_rejected", $request->boolean('notif_po_rejected'));
            Setting::set("user.{$userId}.notif_system_updates", $request->boolean('notif_system_updates'));
            Setting::set("user.{$userId}.notif_security", $request->boolean('notif_security'));
            Setting::set("user.{$userId}.email_daily_summary", $request->boolean('email_daily_summary'));
            Setting::set("user.{$userId}.email_weekly_report", $request->boolean('email_weekly_report'));
            
            return response()->json([
                'success' => true,
                'message' => 'Notification settings saved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save notification settings: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get notification settings
     */
    public function getNotifications()
    {
        $auth = session('auth_user');
        
        if (!$auth || !isset($auth['user_id'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $userId = $auth['user_id'];
        
        $notifications = [
            'notif_po_created' => (bool) Setting::get("user.{$userId}.notif_po_created", true),
            'notif_po_approved' => (bool) Setting::get("user.{$userId}.notif_po_approved", true),
            'notif_po_rejected' => (bool) Setting::get("user.{$userId}.notif_po_rejected", true),
            'notif_system_updates' => (bool) Setting::get("user.{$userId}.notif_system_updates", true),
            'notif_security' => (bool) Setting::get("user.{$userId}.notif_security", false),
            'email_daily_summary' => (bool) Setting::get("user.{$userId}.email_daily_summary", false),
            'email_weekly_report' => (bool) Setting::get("user.{$userId}.email_weekly_report", false),
        ];
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }
}
