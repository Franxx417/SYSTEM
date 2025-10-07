import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Core CSS
                'resources/css/app.css',
                'resources/css/components/custom.css',
                'resources/css/pages/superadmin-dashboard.css',
                
                // Core JS
                'resources/js/app.js',
                'resources/js/bootstrap.js',
                
                // Component JS
                'resources/js/components/modal-manager.js',
                'resources/js/components/status-sync.js',
                'resources/js/components/status-management.js',
                'resources/js/status-color-sync.js',
                
                // Dashboard JS
                'resources/js/dashboards/requestor-dashboard.js',
                'resources/js/dashboards/superadmin-dashboard.js',
                'resources/js/dashboards/superadmin-dashboard-enhanced.js',
                
                // Page-specific JS
                'resources/js/pages/po-create.js',
                'resources/js/pages/po-edit.js',
                'resources/js/pages/po-edit-extras.js',
                'resources/js/pages/po-index.js',
                'resources/js/pages/po-print.js',
                'resources/js/pages/items-create.js',
                'resources/js/pages/items-edit.js',
                'resources/js/pages/items-index.js',
                'resources/js/pages/suppliers-index.js',
                'resources/js/pages/admin-users-index.js',
                
                // Legacy files (to be refactored)
                'resources/js/po-form.js',
            ],
            refresh: true,
        }),
    ],
});
