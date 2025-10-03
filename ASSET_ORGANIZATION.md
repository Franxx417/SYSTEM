# Asset Organization Documentation

## Overview
This document outlines the new organized structure for JavaScript and CSS files in the Laravel procurement system. The reorganization follows Laravel best practices and improves maintainability.

## Directory Structure

### Resources Folder (`resources/`)
**Purpose**: Contains source files that need to be compiled/processed by Vite

```
resources/
├── css/
│   ├── app.css                    # Main application CSS (Vite entry point)
│   ├── components/
│   │   └── custom.css             # Global custom styles and utilities
│   └── pages/
│       └── superadmin-dashboard.css # SuperAdmin dashboard specific styles
├── js/
│   ├── app.js                     # Main application JS (Vite entry point)
│   ├── bootstrap.js               # Laravel Echo, Axios setup
│   ├── components/                # Reusable JavaScript components
│   │   ├── modal-manager.js       # Global modal management
│   │   ├── status-sync.js         # Status synchronization utilities
│   │   └── status-management.js   # Status management functionality
│   ├── dashboards/                # Dashboard-specific JavaScript
│   │   ├── requestor-dashboard.js
│   │   ├── superadmin-dashboard.js
│   │   └── superadmin-dashboard-enhanced.js
│   ├── pages/                     # Page-specific JavaScript
│   │   ├── admin-users-index.js
│   │   ├── items-create.js
│   │   ├── items-edit.js
│   │   ├── items-index.js
│   │   ├── po-create.js
│   │   ├── po-edit.js
│   │   ├── po-edit-extras.js
│   │   ├── po-index.js
│   │   ├── po-print.js
│   │   └── suppliers-index.js
│   ├── po-create.js              # Legacy file (to be refactored)
│   └── po-form.js                # Legacy file (to be refactored)
└── views/                        # Blade templates
```

### Public Folder (`public/`)
**Purpose**: Contains compiled assets and static files served directly by the web server

```
public/
├── build/                        # Vite compiled assets (auto-generated)
│   └── assets/
├── css/
│   └── bootstrap-fallback.css    # Bootstrap fallback (kept for compatibility)
├── js/                          # (Now empty - files moved to resources)
├── uploads/                     # User uploaded files
└── storage -> ../storage/app/public # Symbolic link for file storage
```

## File Categorization

### Core Files
- **`resources/js/app.js`** - Main application entry point
- **`resources/js/bootstrap.js`** - Laravel framework setup
- **`resources/css/app.css`** - Main CSS entry point

### Component Files (Reusable)
- **`modal-manager.js`** - Global modal functionality used across multiple pages
- **`status-sync.js`** - Status synchronization utilities
- **`status-management.js`** - Status management components
- **`custom.css`** - Global custom styles and utility classes

### Dashboard Files
- **`requestor-dashboard.js`** - Requestor dashboard functionality
- **`superadmin-dashboard.js`** - Basic superadmin dashboard
- **`superadmin-dashboard-enhanced.js`** - Enhanced superadmin features
- **`superadmin-dashboard.css`** - SuperAdmin specific styles

### Page-Specific Files
- **Purchase Orders**: `po-create.js`, `po-edit.js`, `po-index.js`, etc.
- **Items Management**: `items-create.js`, `items-edit.js`, `items-index.js`
- **User Management**: `admin-users-index.js`
- **Suppliers**: `suppliers-index.js`

## Vite Configuration

The `vite.config.js` file has been updated to include all organized assets:

```javascript
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
    
    // Dashboard JS
    'resources/js/dashboards/requestor-dashboard.js',
    'resources/js/dashboards/superadmin-dashboard.js',
    'resources/js/dashboards/superadmin-dashboard-enhanced.js',
    
    // Page-specific JS
    'resources/js/pages/po-create.js',
    'resources/js/pages/po-edit.js',
    // ... other page files
]
```

## View File Updates

All Blade templates have been updated to use Vite directives instead of direct script/link tags:

### Before:
```html
<script src="/js/po-create.js"></script>
<link rel="stylesheet" href="/css/custom.css">
```

### After:
```blade
@vite(['resources/js/pages/po-create.js'])
@vite(['resources/css/components/custom.css'])
```

## Benefits of This Organization

1. **Clear Separation**: Development files in `resources/`, compiled files in `public/`
2. **Logical Grouping**: Files organized by purpose (components, pages, dashboards)
3. **Better Maintainability**: Easy to locate and modify specific functionality
4. **Vite Integration**: Proper asset compilation and optimization
5. **Version Control**: Only source files tracked, compiled assets ignored
6. **Performance**: Optimized bundling and minification through Vite
7. **Development Experience**: Hot reloading and fast builds

## Migration Notes

### Completed Actions:
1. ✅ Created organized directory structure in `resources/`
2. ✅ Moved all JS files from `public/js/` to appropriate `resources/js/` subdirectories
3. ✅ Moved CSS files from `public/css/` to appropriate `resources/css/` subdirectories
4. ✅ Updated `vite.config.js` to include all organized assets
5. ✅ Updated all Blade templates to use `@vite()` directives
6. ✅ Updated main layout file (`layouts/app.blade.php`) for core assets

### Legacy Files:
- `resources/js/po-create.js` and `resources/js/po-form.js` remain in root for now
- These should be refactored and moved to appropriate subdirectories in future updates

### Build Process:
To compile assets after this reorganization:
```bash
npm run build        # Production build
npm run dev          # Development build
npm run dev --watch  # Development with file watching
```

## Future Improvements

1. **Refactor Legacy Files**: Move remaining root-level JS files to appropriate subdirectories
2. **Component Consolidation**: Consider combining related small components
3. **CSS Modules**: Implement CSS modules for better scoping
4. **Tree Shaking**: Optimize bundle size by removing unused code
5. **Code Splitting**: Implement dynamic imports for better performance
