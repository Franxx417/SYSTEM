<?php

use App\Services\BrandingService;

if (!function_exists('branding')) {
    /**
     * Get the branding service instance or a specific branding value
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function branding(?string $key = null, $default = null)
    {
        $service = app(BrandingService::class);
        
        if ($key === null) {
            return $service;
        }
        
        return $service->get($key, $default);
    }
}

if (!function_exists('app_name')) {
    /**
     * Get the application name from branding settings
     *
     * @return string
     */
    function app_name(): string
    {
        return app(BrandingService::class)->getAppName();
    }
}

if (!function_exists('app_logo')) {
    /**
     * Get the application logo path
     *
     * @return string|null
     */
    function app_logo(): ?string
    {
        return app(BrandingService::class)->getLogoPath();
    }
}

if (!function_exists('brand_color')) {
    /**
     * Get a brand color (primary, secondary, or accent)
     *
     * @param string $type
     * @return string
     */
    function brand_color(string $type = 'primary'): string
    {
        $service = app(BrandingService::class);
        
        return match($type) {
            'primary' => $service->getPrimaryColor(),
            'secondary' => $service->getSecondaryColor(),
            'accent' => $service->getAccentColor(),
            default => $service->getPrimaryColor(),
        };
    }
}
