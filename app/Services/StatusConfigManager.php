<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Status Configuration Manager
 * Handles dynamic status configuration without database changes
 */
class StatusConfigManager
{
    private const CACHE_KEY = 'status_config';

    private const CACHE_TTL = 3600; // 1 hour

    private const DEFAULT_STATUS = 'Pending';

    private string $configPath;

    public function __construct()
    {
        $this->configPath = config_path('status_config.php');
    }

    /**
     * Get all status configurations
     */
    public function getConfig(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            if (! File::exists($this->configPath)) {
                $this->createDefaultConfig();
            }

            return require $this->configPath;
        });
    }

    /**
     * Get status colors mapping
     */
    public function getStatusColors(): array
    {
        $config = $this->getConfig();

        return $config['status_colors'] ?? [];
    }

    /**
     * Get color for a specific status
     */
    public function getStatusColor(string $statusName): array
    {
        $colors = $this->getStatusColors();

        return $colors[$statusName] ?? $this->getDefaultStatusConfig();
    }

    /**
     * Get default status configuration
     */
    private function getDefaultStatusConfig(): array
    {
        return [
            'color' => '#6c757d',
            'css_class' => 'status-secondary',
            'text_color' => '#ffffff',
            'description' => 'Unknown status',
        ];
    }

    /**
     * Get CSS class for a specific status
     */
    public function getStatusCssClass(string $statusName): string
    {
        $color = $this->getStatusColor($statusName);

        return $color['css_class'] ?? 'status-secondary';
    }

    /**
     * Update status configuration
     */
    public function updateConfig(array $config): bool
    {
        try {
            $this->validateConfig($config);
            $content = $this->generateConfigFileContent($config);

            File::put($this->configPath, $content);
            Cache::forget(self::CACHE_KEY);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update status config: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Validate configuration structure
     */
    private function validateConfig(array $config): void
    {
        if (! isset($config['status_colors']) || ! is_array($config['status_colors'])) {
            throw new \InvalidArgumentException('Configuration must contain status_colors array');
        }

        if (! isset($config['status_order']) || ! is_array($config['status_order'])) {
            throw new \InvalidArgumentException('Configuration must contain status_order array');
        }
    }

    /**
     * Generate configuration file content
     */
    private function generateConfigFileContent(array $config): string
    {
        $header = "<?php\n\n/**\n * Status Configuration\n * This file manages status colors and settings without modifying the database structure.\n * Can be dynamically updated by superadmin users.\n */\n\n";

        return $header.'return '.var_export($config, true).';';
    }

    /**
     * Add or update a status
     */
    public function updateStatus(string $statusName, array $statusConfig): bool
    {
        $config = $this->getConfig();
        $config['status_colors'][$statusName] = $statusConfig;

        // Add to order if not exists
        if (! in_array($statusName, $config['status_order'])) {
            $config['status_order'][] = $statusName;
        }

        return $this->updateConfig($config);
    }

    /**
     * Remove a status
     */
    public function removeStatus(string $statusName): bool
    {
        $config = $this->getConfig();

        // Don't allow removal of default status
        if ($statusName === ($config['default_status'] ?? self::DEFAULT_STATUS)) {
            return false;
        }

        unset($config['status_colors'][$statusName]);
        $config['status_order'] = array_values(array_filter(
            $config['status_order'],
            fn ($status) => $status !== $statusName
        ));

        return $this->updateConfig($config);
    }

    /**
     * Reorder statuses
     */
    public function reorderStatuses(array $statusOrder): bool
    {
        $config = $this->getConfig();
        $config['status_order'] = $statusOrder;

        return $this->updateConfig($config);
    }

    /**
     * Generate dynamic CSS for status colors
     */
    public function generateStatusCss(): string
    {
        $colors = $this->getStatusColors();
        $css = "/* Dynamic Status Colors - Auto Generated */\n";

        foreach ($colors as $statusName => $config) {
            $cssClass = $config['css_class'];
            $color = $config['color'];

            $css .= ".{$cssClass} {\n";
            $css .= "    background-color: {$color};\n";
            $css .= "}\n\n";
        }

        return $css;
    }

    /**
     * Get status statistics
     */
    public function getStatusStats(): array
    {
        $config = $this->getConfig();

        return [
            'total_statuses' => count($config['status_colors'] ?? []),
            'default_status' => $config['default_status'] ?? 'Pending',
            'settings' => $config['settings'] ?? [],
        ];
    }

    /**
     * Create default configuration file
     */
    private function createDefaultConfig(): void
    {
        $this->updateConfig($this->getDefaultConfiguration());
    }

    /**
     * Get default configuration structure
     */
    private function getDefaultConfiguration(): array
    {
        return [
            'status_colors' => [
                'Pending' => [
                    'color' => '#ffc107',
                    'css_class' => 'status-warning',
                    'text_color' => '#000000',
                    'description' => 'Purchase order is awaiting review',
                ],
                'Verified' => [
                    'color' => '#0dcaf0',
                    'css_class' => 'status-info',
                    'text_color' => '#000000',
                    'description' => 'Purchase order has been verified',
                ],
                'Approved' => [
                    'color' => '#28a745',
                    'css_class' => 'status-online',
                    'text_color' => '#ffffff',
                    'description' => 'Purchase order has been approved',
                ],
                'Received' => [
                    'color' => '#20c997',
                    'css_class' => 'status-success',
                    'text_color' => '#ffffff',
                    'description' => 'Purchase order items have been received',
                ],
                'Rejected' => [
                    'color' => '#dc3545',
                    'css_class' => 'status-offline',
                    'text_color' => '#ffffff',
                    'description' => 'Purchase order has been rejected',
                ],
            ],
            'status_order' => ['Pending', 'Verified', 'Approved', 'Received', 'Rejected'],
            'default_status' => self::DEFAULT_STATUS,
            'settings' => [
                'allow_status_creation' => true,
                'allow_status_deletion' => true,
                'require_remarks_on_change' => true,
                'show_status_history' => true,
            ],
        ];
    }
}
