<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];
    
    protected $primaryKey = 'key';
    
    public $incrementing = false;
    
    protected $keyType = 'string';

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = static::find($key);
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value)
    {
        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Get the company logo URL from settings
     */
    public static function getCompanyLogo()
    {
        // Prefer the value set from Superadmin > Branding page
        $brandingLogo = static::get('branding.logo_path');
        if (!empty($brandingLogo)) {
            return $brandingLogo;
        }
        // Fallback to legacy key if present
        return static::get('company_logo', null);
    }

    /**
     * Get the company name from settings
     */
    public static function getCompanyName()
    {
        return static::get('company_name', config('app.name', 'Global Agility'));
    }
}
