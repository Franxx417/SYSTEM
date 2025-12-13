<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class BrandingService
{
    /**
     * Cache duration for branding settings (in seconds)
     */
    const CACHE_DURATION = 3600; // 1 hour

    /**
     * Default branding values
     */
    const DEFAULTS = [
        'app.name' => 'Procurement System',
        'app.tagline' => 'Management System',
        'app.description' => 'Comprehensive procurement and purchase order management',
        'branding.logo_path' => null,
        'branding.logo_position' => 'left',
        'branding.logo_size' => 50,
        'branding.primary_color' => '#0d6efd',
        'branding.secondary_color' => '#6c757d',
        'branding.accent_color' => '#198754',
        'branding.font_family' => 'system-ui',
        'branding.font_size' => 14,
    ];

    /**
     * Get all branding settings with caching
     */
    public function getAll(): array
    {
        return Cache::remember('branding_settings', self::CACHE_DURATION, function () {
            try {
                if (! Schema::hasTable('settings')) {
                    return self::DEFAULTS;
                }

                $settings = DB::table('settings')
                    ->whereIn('key', array_keys(self::DEFAULTS))
                    ->pluck('value', 'key')
                    ->toArray();

                return array_merge(self::DEFAULTS, $settings);
            } catch (\Throwable $e) {
                Log::warning('Failed to load branding settings: '.$e->getMessage());

                return self::DEFAULTS;
            }
        });
    }

    /**
     * Get a specific branding setting
     */
    public function get(string $key, $default = null)
    {
        $settings = $this->getAll();

        return $settings[$key] ?? $default ?? self::DEFAULTS[$key] ?? null;
    }

    /**
     * Get application name
     */
    public function getAppName(): string
    {
        return $this->get('app.name');
    }

    /**
     * Get application tagline
     */
    public function getAppTagline(): string
    {
        return $this->get('app.tagline') ?? '';
    }

    /**
     * Get application description
     */
    public function getAppDescription(): string
    {
        return $this->get('app.description') ?? '';
    }

    /**
     * Get logo path
     */
    public function getLogoPath(): ?string
    {
        return $this->get('branding.logo_path');
    }

    /**
     * Check if logo exists
     */
    public function hasLogo(): bool
    {
        return ! empty($this->getLogoPath());
    }

    /**
     * Get logo size (height in pixels)
     */
    public function getLogoSize(): int
    {
        return (int) $this->get('branding.logo_size');
    }

    /**
     * Get logo position
     */
    public function getLogoPosition(): string
    {
        return $this->get('branding.logo_position');
    }

    /**
     * Get primary color
     */
    public function getPrimaryColor(): string
    {
        return $this->get('branding.primary_color');
    }

    /**
     * Get secondary color
     */
    public function getSecondaryColor(): string
    {
        return $this->get('branding.secondary_color');
    }

    /**
     * Get accent color
     */
    public function getAccentColor(): string
    {
        return $this->get('branding.accent_color');
    }

    /**
     * Get font family
     */
    public function getFontFamily(): string
    {
        return $this->get('branding.font_family');
    }

    /**
     * Get font size
     */
    public function getFontSize(): float
    {
        return (float) $this->get('branding.font_size');
    }

    /**
     * Get brand colors as array
     */
    public function getColors(): array
    {
        return [
            'primary' => $this->getPrimaryColor(),
            'secondary' => $this->getSecondaryColor(),
            'accent' => $this->getAccentColor(),
        ];
    }

    /**
     * Get typography settings
     */
    public function getTypography(): array
    {
        return [
            'font_family' => $this->getFontFamily(),
            'font_size' => $this->getFontSize(),
        ];
    }

    /**
     * Generate dynamic CSS for branding
     */
    public function generateCSS(): string
    {
        $primary = $this->getPrimaryColor();
        $secondary = $this->getSecondaryColor();
        $accent = $this->getAccentColor();
        $fontFamily = $this->getFontFamily();
        $fontSize = $this->getFontSize();

        // Helper function to adjust color brightness
        $adjustBrightness = function ($hex, $steps) {
            $hex = str_replace('#', '', $hex);
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));

            $r = max(0, min(255, $r + $steps));
            $g = max(0, min(255, $g + $steps));
            $b = max(0, min(255, $b + $steps));

            return '#'.str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
                       .str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
                       .str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
        };

        $primaryDark = $adjustBrightness($primary, -20);
        $primaryLight = $adjustBrightness($primary, 20);
        $accentDark = $adjustBrightness($accent, -20);

        return <<<CSS
/* Dynamic Branding Styles */
:root {
    --brand-primary: {$primary};
    --brand-primary-dark: {$primaryDark};
    --brand-primary-light: {$primaryLight};
    --brand-secondary: {$secondary};
    --brand-accent: {$accent};
    --brand-accent-dark: {$accentDark};
    --brand-font-family: {$fontFamily}, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    --brand-font-size: {$fontSize}px;
}

body {
    font-family: var(--brand-font-family);
    font-size: var(--brand-font-size);
}

/* Primary color applications */
.btn-primary,
.bg-primary {
    background-color: var(--brand-primary) !important;
    border-color: var(--brand-primary) !important;
}

.btn-primary:hover,
.btn-primary:focus {
    background-color: var(--brand-primary-dark) !important;
    border-color: var(--brand-primary-dark) !important;
}

.text-primary,
a {
    color: var(--brand-primary) !important;
}

a:hover {
    color: var(--brand-primary-dark) !important;
}

/* Sidebar active nav link */
.sidebar .nav-link.active {
    background-color: var(--brand-primary) !important;
    color: white !important;
}

/* Badges */
.badge.bg-primary {
    background-color: var(--brand-primary) !important;
}

/* Form controls focus */
.form-control:focus,
.form-select:focus {
    border-color: var(--brand-primary-light);
    box-shadow: 0 0 0 0.25rem rgba(var(--brand-primary), 0.25);
}

/* Accent color applications */
.btn-success,
.bg-success {
    background-color: var(--brand-accent) !important;
    border-color: var(--brand-accent) !important;
}

.btn-success:hover,
.btn-success:focus {
    background-color: var(--brand-accent-dark) !important;
    border-color: var(--brand-accent-dark) !important;
}

.text-success {
    color: var(--brand-accent) !important;
}

/* Status badges with brand colors */
.status-online,
.badge.bg-success {
    background-color: var(--brand-accent) !important;
}

/* Card headers with brand primary */
.card-header.bg-primary {
    background-color: var(--brand-primary) !important;
    color: white;
}

/* Progress bars */
.progress-bar {
    background-color: var(--brand-primary);
}

/* Links in navigation */
.nav-link:hover {
    color: var(--brand-primary) !important;
}

/* Bootstrap overrides for consistent branding */
.btn-outline-primary {
    color: var(--brand-primary);
    border-color: var(--brand-primary);
}

.btn-outline-primary:hover {
    background-color: var(--brand-primary);
    border-color: var(--brand-primary);
}

/* Alert primary */
.alert-primary {
    background-color: var(--brand-primary-light);
    border-color: var(--brand-primary);
    color: var(--brand-primary-dark);
}

/* Pagination */
.page-link {
    color: var(--brand-primary);
}

.page-item.active .page-link {
    background-color: var(--brand-primary);
    border-color: var(--brand-primary);
}

/* Tables */
.table-primary {
    --bs-table-bg: var(--brand-primary-light);
    --bs-table-border-color: var(--brand-primary);
}

/* Custom checkbox/radio colors */
.form-check-input:checked {
    background-color: var(--brand-primary);
    border-color: var(--brand-primary);
}

/* Spinner */
.spinner-border.text-primary {
    color: var(--brand-primary) !important;
}

/* Dropdown hover */
.dropdown-item:hover,
.dropdown-item:focus {
    background-color: var(--brand-primary-light);
}

CSS;
    }

    /**
     * Clear branding cache
     */
    public function clearCache(): void
    {
        Cache::forget('branding_settings');
    }

    /**
     * Update multiple branding settings
     */
    public function updateSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            if (array_key_exists($key, self::DEFAULTS)) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        $this->clearCache();
    }

    /**
     * Get print-friendly branding data
     */
    public function getPrintData(): array
    {
        $logoPath = $this->getLogoPath();
        $logoSrc = null;
        if (! empty($logoPath)) {
            if (preg_match('~^https?://~i', $logoPath)) {
                $logoSrc = $logoPath;
            } else {
                $relative = ltrim($logoPath, '/');
                $absolute = public_path($relative);

                if (is_file($absolute) && is_readable($absolute)) {
                    $data = @file_get_contents($absolute);
                    if ($data !== false) {
                        $ext = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));
                        $mime = match ($ext) {
                            'svg' => 'image/svg+xml',
                            'png' => 'image/png',
                            'jpg', 'jpeg' => 'image/jpeg',
                            'webp' => 'image/webp',
                            default => null,
                        };

                        if ($mime && strlen($data) <= 1024 * 1024) {
                            $logoSrc = 'data:'.$mime.';base64,'.base64_encode($data);
                        } else {
                            $logoSrc = asset($relative);
                        }
                    }
                }
            }
        }

        return [
            'company_name' => $this->getAppName(),
            'company_logo' => $logoSrc,
            'company_tagline' => $this->getAppTagline(),
            'logo_position' => $this->getLogoPosition(),
            'logo_size' => $this->getLogoSize(),
            'primary_color' => $this->getPrimaryColor(),
            'accent_color' => $this->getAccentColor(),
            'font_family' => $this->getFontFamily(),
            'font_size' => $this->getFontSize(),
        ];
    }
}
