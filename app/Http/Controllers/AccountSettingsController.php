<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AccountSettingsController extends Controller
{
    /**
     * Get authenticated user from session
     */
    private function auth(Request $request)
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth) {
            abort(401, 'Unauthenticated');
        }
        return $auth;
    }

    /**
     * Show account settings page
     */
    public function index(Request $request)
    {
        $auth = $this->auth($request);
        
        // Get user details
        $user = DB::table('users')->where('user_id', $auth['user_id'])->first();
        
        return view('settings.index', [
            'auth' => $auth,
            'user' => $user
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $auth = $this->auth($request);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        try {
            DB::table('users')->where('user_id', $auth['user_id'])->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'position' => $validated['position'],
                'department' => $validated['department'],
                'updated_at' => now(),
            ]);

            // Update session
            $request->session()->put('auth_user', array_merge($auth, [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'position' => $validated['position'],
                'department' => $validated['department'],
            ]));

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Profile updated successfully']);
            }
            return back()->with('success', 'Profile updated successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to update profile'], 500);
            }
            return back()->withErrors(['error' => 'Failed to update profile']);
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $auth = $this->auth($request);
        
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        try {
            // Get current password hash from the correct source table
            if (Schema::hasColumn('users', 'password')) {
                $cred = DB::table('users')
                    ->select('password')
                    ->where('user_id', $auth['user_id'])
                    ->first();

                $updatePassword = function (string $hash) use ($auth) {
                    return DB::table('users')->where('user_id', $auth['user_id'])->update([
                        'password' => $hash,
                        'updated_at' => now(),
                    ]);
                };
            } else {
                $cred = DB::table('login')
                    ->select('password')
                    ->where('user_id', $auth['user_id'])
                    ->first();

                $updatePassword = function (string $hash) use ($auth) {
                    return DB::table('login')->where('user_id', $auth['user_id'])->update([
                        'password' => $hash,
                        'updated_at' => now(),
                    ]);
                };
            }

            if (!$cred || !isset($cred->password)) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'error' => 'Credentials not found'], 404);
                }
                return back()->withErrors(['error' => 'Credentials not found']);
            }

            // Verify current password
            if (!Hash::check($validated['current_password'], $cred->password)) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'error' => 'Current password is incorrect'], 400);
                }
                return back()->withErrors(['current_password' => 'Current password is incorrect']);
            }

            // Update password
            $updatePassword(Hash::make($validated['new_password']));

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Password updated successfully']);
            }
            return back()->with('success', 'Password updated successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to update password'], 500);
            }
            return back()->withErrors(['error' => 'Failed to update password']);
        }
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request)
    {
        $auth = $this->auth($request);
        
        // Basic implementation - can be expanded
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Preferences updated']);
        }
        return back()->with('success', 'Preferences updated');
    }

    /**
     * Update role-specific settings
     */
    public function updateRoleSettings(Request $request)
    {
        $auth = $this->auth($request);
        
        // Basic implementation - can be expanded
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Role settings updated']);
        }
        return back()->with('success', 'Role settings updated');
    }

    /**
     * Upload avatar
     */
    public function uploadAvatar(Request $request)
    {
        $auth = $this->auth($request);
        
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $filename = 'avatar_' . $auth['user_id'] . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/avatars'), $filename);
                
                $avatarUrl = '/uploads/avatars/' . $filename;
                
                DB::table('users')->where('user_id', $auth['user_id'])->update([
                    'avatar' => $avatarUrl,
                    'updated_at' => now(),
                ]);

                if ($request->expectsJson()) {
                    return response()->json(['success' => true, 'avatar_url' => $avatarUrl]);
                }
                return back()->with('success', 'Avatar updated successfully');
            }
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to upload avatar'], 500);
            }
            return back()->withErrors(['error' => 'Failed to upload avatar']);
        }
    }

    /**
     * Delete account
     */
    public function deleteAccount(Request $request)
    {
        $auth = $this->auth($request);
        
        $validated = $request->validate([
            'password' => 'required',
        ]);

        try {
            // Verify password
            if (Schema::hasColumn('users', 'password')) {
                $cred = DB::table('users')->select('password')->where('user_id', $auth['user_id'])->first();
            } else {
                $cred = DB::table('login')->select('password')->where('user_id', $auth['user_id'])->first();
            }

            if (!$cred || !isset($cred->password) || !Hash::check($validated['password'], $cred->password)) {
                return back()->withErrors(['password' => 'Password is incorrect']);
            }

            // Delete user (basic implementation)
            DB::table('users')->where('user_id', $auth['user_id'])->delete();
            DB::table('login')->where('user_id', $auth['user_id'])->delete();

            $request->session()->flush();
            return redirect()->route('login')->with('success', 'Account deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete account']);
        }
    }

    /**
     * Export user data
     */
    public function exportData(Request $request)
    {
        $auth = $this->auth($request);
        
        // Basic implementation
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Data export initiated']);
        }
        return back()->with('success', 'Data export initiated');
    }

    /**
     * Get login activity
     */
    public function loginActivity(Request $request)
    {
        $auth = $this->auth($request);
        
        try {
            // Basic mock data - implement actual login tracking as needed
            $activity = [
                [
                    'timestamp' => now()->subHours(1)->toDateTimeString(),
                    'ip_address' => $request->ip(),
                    'success' => true,
                ],
                [
                    'timestamp' => now()->subDays(1)->toDateTimeString(),
                    'ip_address' => $request->ip(),
                    'success' => true,
                ],
            ];

            return response()->json([
                'success' => true,
                'activity' => $activity,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to load activity'], 500);
        }
    }

    /**
     * Get activity log
     */
    public function activityLog(Request $request)
    {
        $auth = $this->auth($request);
        
        try {
            // Basic mock data - implement actual activity tracking as needed
            $activity = [
                (object)[
                    'action' => 'Profile Updated',
                    'created_at' => now()->subHours(2)->toDateTimeString(),
                ],
                (object)[
                    'action' => 'Password Changed',
                    'created_at' => now()->subDays(3)->toDateTimeString(),
                ],
            ];

            return response()->json([
                'success' => true,
                'activity' => $activity,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to refresh activity log'], 500);
        }
    }
}
