<?php

namespace App\Helpers;

use App\Services\StatusConfigManager;

/**
 * Status Helper
 * Provides helper functions for status-related operations
 */
class StatusHelper
{
    private static ?StatusConfigManager $statusManager = null;

    /**
     * Get status manager instance
     */
    private static function getStatusManager(): StatusConfigManager
    {
        return self::$statusManager ??= app(StatusConfigManager::class);
    }

    /**
     * Get CSS class for a status
     */
    public static function getStatusClass(string $statusName): string
    {
        return self::getStatusManager()->getStatusCssClass($statusName);
    }

    /**
     * Get color configuration for a status
     */
    public static function getStatusColor(string $statusName): array
    {
        return self::getStatusManager()->getStatusColor($statusName);
    }

    /**
     * Get all status colors
     */
    public static function getAllStatusColors(): array
    {
        return self::getStatusManager()->getStatusColors();
    }

    /**
     * Generate status indicator HTML
     */
    public static function statusIndicator(string $statusName, array $attributes = []): string
    {
        $config = self::getStatusColor($statusName);
        $cssClass = $config['css_class'] ?? 'status-secondary';
        
        $defaultAttributes = [
            'class' => "status-indicator {$cssClass}",
            'style' => "background-color: {$config['color']}",
            'title' => $config['description'] ?? $statusName
        ];
        
        $mergedAttributes = array_merge($defaultAttributes, $attributes);
        $attributeString = self::buildAttributeString($mergedAttributes);
        
        return "<span{$attributeString}></span>";
    }

    /**
     * Generate status badge HTML
     */
    public static function statusBadge(string $statusName, array $attributes = []): string
    {
        $config = self::getStatusColor($statusName);
        
        $defaultAttributes = [
            'class' => 'badge status-badge',
            'style' => "background-color: {$config['color']}; color: {$config['text_color']}"
        ];
        
        $mergedAttributes = array_merge($defaultAttributes, $attributes);
        $attributeString = self::buildAttributeString($mergedAttributes);
        
        return "<span{$attributeString}>{$statusName}</span>";
    }

    /**
     * Build HTML attribute string from array
     */
    private static function buildAttributeString(array $attributes): string
    {
        $parts = [];
        foreach ($attributes as $key => $value) {
            $parts[] = $key . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }
        
        return empty($parts) ? '' : ' ' . implode(' ', $parts);
    }

    /**
     * Generate dynamic CSS for all statuses
     */
    public static function generateDynamicCss(): string
    {
        return self::getStatusManager()->generateStatusCss();
    }

    /**
     * Check if a status exists in configuration
     */
    public static function statusExists(string $statusName): bool
    {
        $colors = self::getAllStatusColors();
        return isset($colors[$statusName]);
    }

    /**
     * Get status order
     */
    public static function getStatusOrder(): array
    {
        $config = self::getStatusManager()->getConfig();
        return $config['status_order'] ?? [];
    }

    /**
     * Get default status
     */
    public static function getDefaultStatus(): string
    {
        $config = self::getStatusManager()->getConfig();
        return $config['default_status'] ?? 'Pending';
    }
}
