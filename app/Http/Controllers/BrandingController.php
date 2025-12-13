<?php

namespace App\Http\Controllers;

use App\Services\BrandingService;

class BrandingController extends Controller
{
    protected $brandingService;

    public function __construct(BrandingService $brandingService)
    {
        $this->brandingService = $brandingService;
    }

    /**
     * Generate dynamic CSS based on branding settings
     */
    public function css()
    {
        $css = $this->brandingService->generateCSS();

        return response($css)
            ->header('Content-Type', 'text/css')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
