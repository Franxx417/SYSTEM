<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Models\SystemSetting;
use App\Models\SystemActivityLog;

class SystemSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the settings dashboard
     */
    public function index()
    {
        $auth = session('auth_user');
        
        if (!$auth || $auth['role'] !== 'superadmin') {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access');
        }

        $settings = SystemSetting::all()->groupBy('category');
        
        return view('settings.index', compact('settings'));
    }

    /**
     * Get settings by category for AJAX requests
     */
    public function getByCategory(Request $request, $category)
    {
        $auth = session('auth_user');
        
        if (!$auth || $auth['role'] !== 'superadmin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $settings = SystemSetting::byCategory($category)
            ->ordered()
            ->get()
            ->map(function ($setting) {
                return [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'type' => $setting->type,
                    'description' => $setting->description,
                    'validation_rules' => $setting->validation_rules,
                    'is_encrypted' => $setting->is_encrypted,
                ];
            });

        return response()->json($settings);
    }

    /**
     * Update multiple settings
     */
    public function updateBatch(Request $request)
    {
        $auth = session('auth_user');
        
        if (!$auth || $auth['role'] !== 'superadmin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $settings = $request->input('settings', []);
        $category = $request->input('category');
        $errors = [];
        $updated = [];

        DB::beginTransaction();

        try {
            foreach ($settings as $key => $value) {
                $setting = SystemSetting::where('category', $category)
                    ->where('key', $key)
                    ->first();

                if (!$setting) {
                    $errors[$key] = 'Setting not found';
                    continue;
                }

                // Validate the value
                if (!$this->validateSettingValue($setting, $value)) {
                    $errors[$key] = 'Invalid value for this setting';
                    continue;
                }

                // Update the setting
                $oldValue = $setting->value;
                $setting->value = $value;
                $setting->updated_by = $auth['user_id'];
                $setting->save();

                $updated[$key] = $value;

                // Log the change
                SystemActivityLog::logSecurityEvent(
                    'setting_updated',
                    "Setting '{$category}.{$key}' updated from '{$oldValue}' to '{$value}'",
                    'medium',
                    [
                        'category' => $category,
                        'key' => $key,
                        'old_value' => $oldValue,
                        'new_value' => $value,
                    ]
                );
            }

            if (empty($errors)) {
                DB::commit();
                SystemSetting::clearCache();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Settings updated successfully',
                    'updated' => $updated
                ]);
            } else {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Some settings could not be updated',
                    'errors' => $errors,
                    'updated' => $updated
                ], 422);
            }

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a single setting
     */
    public function updateSingle(Request $request)
    {
        $auth = session('auth_user');
        
        if (!$auth || $auth['role'] !== 'superadmin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'category' => 'required|string',
            'key' => 'required|string',
            'value' => 'nullable',
        ]);

        try {
            $setting = SystemSetting::where('category', $request->category)
                ->where('key', $request->key)
                ->first();

            if (!$setting) {
                return response()->json(['error' => 'Setting not found'], 404);
            }

            // Validate the value
            if (!$this->validateSettingValue($setting, $request->value)) {
                return response()->json(['error' => 'Invalid value for this setting'], 422);
            }

            $oldValue = $setting->value;
            $setting->value = $request->value;
            $setting->updated_by = $auth['user_id'];
            $setting->save();

            SystemSetting::clearCache();

            // Log the change
            SystemActivityLog::logSecurityEvent(
                'setting_updated',
                "Setting '{$request->category}.{$request->key}' updated",
                'medium',
                [
                    'category' => $request->category,
                    'key' => $request->key,
                    'old_value' => $oldValue,
                    'new_value' => $request->value,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update setting: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset settings to defaults
     */
    public function resetToDefaults(Request $request)
    {
        $auth = session('auth_user');
        
        if (!$auth || $auth['role'] !== 'superadmin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $category = $request->input('category');

        try {
            if ($category) {
                // Reset specific category
                SystemSetting::where('category', $category)->delete();
            } else {
                // Reset all settings
                SystemSetting::truncate();
            }

            // Reseed defaults
            SystemSetting::seedDefaults();
            SystemSetting::clearCache();

            SystemActivityLog::logSecurityEvent(
                'settings_reset',
                $category ? "Settings reset for category: {$category}" : 'All settings reset to defaults',
                'high',
                ['category' => $category]
            );

            return response()->json([
                'success' => true,
                'message' => $category ? 'Category settings reset successfully' : 'All settings reset successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export settings
     */
    public function export(Request $request)
    {
        $auth = session('auth_user');
        
        if (!$auth || $auth['role'] !== 'superadmin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $settings = SystemSetting::all()->map(function ($setting) {
                return [
                    'category' => $setting->category,
                    'key' => $setting->key,
                    'value' => $setting->is_encrypted ? '[ENCRYPTED]' : $setting->value,
                    'type' => $setting->type,
                    'description' => $setting->description,
                ];
            });

            $export = [
                'exported_at' => now()->toISOString(),
                'exported_by' => $auth['name'],
                'settings' => $settings,
            ];

            SystemActivityLog::logSecurityEvent(
                'settings_exported',
                'System settings exported',
                'medium'
            );

            return response()->json($export);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import settings
     */
    public function import(Request $request)
    {
        $auth = session('auth_user');
        
        if (!$auth || $auth['role'] !== 'superadmin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'settings' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $imported = 0;
            $skipped = 0;

            foreach ($request->settings as $settingData) {
                if (!isset($settingData['category']) || !isset($settingData['key'])) {
                    $skipped++;
                    continue;
                }

                // Skip encrypted settings for security
                if (isset($settingData['value']) && $settingData['value'] === '[ENCRYPTED]') {
                    $skipped++;
                    continue;
                }

                SystemSetting::updateOrCreate(
                    [
                        'category' => $settingData['category'],
                        'key' => $settingData['key']
                    ],
                    [
                        'value' => $settingData['value'] ?? null,
                        'type' => $settingData['type'] ?? 'string',
                        'description' => $settingData['description'] ?? null,
                        'updated_by' => $auth['user_id'],
                    ]
                );

                $imported++;
            }

            DB::commit();
            SystemSetting::clearCache();

            SystemActivityLog::logSecurityEvent(
                'settings_imported',
                "Settings imported: {$imported} imported, {$skipped} skipped",
                'high',
                ['imported' => $imported, 'skipped' => $skipped]
            );

            return response()->json([
                'success' => true,
                'message' => "Settings imported successfully. {$imported} imported, {$skipped} skipped.",
                'imported' => $imported,
                'skipped' => $skipped
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to import settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test setting value
     */
    public function testSetting(Request $request)
    {
        $auth = session('auth_user');
        
        if (!$auth || $auth['role'] !== 'superadmin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'category' => 'required|string',
            'key' => 'required|string',
            'value' => 'nullable',
        ]);

        try {
            $setting = SystemSetting::where('category', $request->category)
                ->where('key', $request->key)
                ->first();

            if (!$setting) {
                return response()->json(['error' => 'Setting not found'], 404);
            }

            $isValid = $this->validateSettingValue($setting, $request->value);

            return response()->json([
                'valid' => $isValid,
                'message' => $isValid ? 'Value is valid' : 'Value is invalid for this setting'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Validation error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Validate setting value
     */
    private function validateSettingValue($setting, $value)
    {
        if (!$setting->validation_rules) {
            return true;
        }

        $validator = Validator::make(
            ['value' => $value],
            ['value' => $setting->validation_rules]
        );

        return $validator->passes();
    }
}
