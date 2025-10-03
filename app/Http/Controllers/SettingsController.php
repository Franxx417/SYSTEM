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
     * Generate dynamic status CSS
     */
    public function dynamicStatusCss()
    {
        $css = "
        /* Dynamic Status Colors */
        .status-pending { background-color: #ffc107; color: #000; }
        .status-verified { background-color: #17a2b8; color: #fff; }
        .status-approved { background-color: #28a745; color: #fff; }
        .status-rejected { background-color: #dc3545; color: #fff; }
        .status-received { background-color: #6f42c1; color: #fff; }
        .status-cancelled { background-color: #6c757d; color: #fff; }
        
        /* Badge variants */
        .badge.status-pending { background-color: #ffc107 !important; color: #000 !important; }
        .badge.status-verified { background-color: #17a2b8 !important; color: #fff !important; }
        .badge.status-approved { background-color: #28a745 !important; color: #fff !important; }
        .badge.status-rejected { background-color: #dc3545 !important; color: #fff !important; }
        .badge.status-received { background-color: #6f42c1 !important; color: #fff !important; }
        .badge.status-cancelled { background-color: #6c757d !important; color: #fff !important; }
        ";
        
        return response($css, 200, [
            'Content-Type' => 'text/css',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
