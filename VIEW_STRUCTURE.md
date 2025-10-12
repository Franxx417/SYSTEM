# View Structure Documentation

## Overview
This document explains the reorganized view structure for better maintainability and clear separation of concerns between user roles.

## Directory Structure

```
resources/views/
├── superadmin/               # All superadmin views
│   ├── dashboard.blade.php   # Main superadmin dashboard
│   ├── database-settings.blade.php  # Database configuration
│   ├── logs.blade.php        # System logs viewer
│   └── tabs/                 # Dashboard tab components
│       ├── branding.blade.php
│       ├── database.blade.php
│       ├── logs.blade.php
│       ├── overview.blade.php
│       ├── pos.blade.php
│       ├── security.blade.php
│       ├── status.blade.php
│       ├── system.blade.php
│       └── users.blade.php
│
├── dashboards/               # Role-specific dashboards
│   └── requestor.blade.php   # Requestor dashboard
│
├── po/                       # Purchase Order views
├── suppliers/                # Supplier management views
├── items/                    # Item management views
├── settings/                 # General settings views
├── auth/                     # Authentication views
├── layouts/                  # Layout templates
└── partials/                 # Reusable components
```

## System Roles

This system uses **only two roles**:

### Superadmin (`superadmin` role)
- **Route Prefix**: `/superadmin`
- **Controller**: `App\Http\Controllers\SuperAdminController`
- **Views**: `resources/views/superadmin/`
- **Features**:
  - Complete system administration
  - User management
  - Database configuration
  - System logs
  - Security settings
  - Branding customization
  - Status management
  - Full access to all system features

### Requestor (`requestor` role)
- **Dashboard**: `resources/views/dashboards/requestor.blade.php`
- **Features**:
  - Create and manage purchase orders
  - View supplier information
  - Track PO status

## Recent Changes (2025-10-09)

### Phase 1: View Reorganization
1. **Moved** `resources/views/dashboards/superadmin.blade.php` → `resources/views/superadmin/dashboard.blade.php`
2. **Moved** `resources/views/admin/database-settings.blade.php` → `resources/views/superadmin/database-settings.blade.php`
3. **Moved** `resources/views/admin/logs.blade.php` → `resources/views/superadmin/logs.blade.php`
4. **Moved** `resources/views/dashboards/superadmin/tabs/*` → `resources/views/superadmin/tabs/*`
5. **Removed** empty directory `resources/views/dashboards/superadmin/`

### Phase 2: Role Consolidation (System now has only 2 roles: requestor and superadmin)
1. **Removed** `resources/views/admin/` directory (was for authorized_personnel role)
2. **Removed** `app/Http/Controllers/Admin/UserController.php`
3. **Removed** admin routes (`/admin/users`)
4. **Updated** role validation to only allow `requestor` and `superadmin`
5. **Updated** navigation in `layouts/app.blade.php` to remove authorized_personnel links
6. **Updated** `SupplierController` to only allow requestor and superadmin access

### Controller Updates
Updated `SuperAdminController` view references:
- `dashboards.superadmin` → `superadmin.dashboard`
- `admin.database-settings` → `superadmin.database-settings`
- `admin.logs` → `superadmin.logs`
- `dashboards.superadmin.tabs.*` → `superadmin.tabs.*`
- Role validation: Removed `authorized_personnel`, `finance_controller`, `department_head`

## Benefits of This Structure

1. **Simplified Roles**: Only 2 roles (requestor and superadmin) reduces complexity
2. **Clear Separation**: Each role has its dedicated directory/views
3. **Maintainability**: Easier to locate and update role-specific views
4. **Logical Grouping**: Related views are grouped together
5. **No Redundancy**: All admin functionality consolidated under superadmin

## Navigation Reference

### Superadmin Routes
```php
/superadmin              → superadmin.dashboard
/superadmin/database     → superadmin.database-settings
/superadmin/logs         → superadmin.logs
```

### Requestor Routes
```php
/dashboard               → dashboards.requestor
/po                      → Purchase Order views
/suppliers               → Supplier views
```

## Blade View Naming Convention

- **Dot notation** is used in controllers: `'superadmin.dashboard'`
- **Directory structure** mirrors the dot notation: `superadmin/dashboard.blade.php`
- **Tabs** are included using: `@include('superadmin.tabs.overview')`

## Future Considerations

If adding new views or features:
- Superadmin-only features → `resources/views/superadmin/`
- Requestor features → Use existing PO, supplier, or item views
- Role-specific dashboards → `resources/views/dashboards/{role}.blade.php`
- Shared components → `resources/views/partials/`

**Note**: If you need to add more roles in the future (e.g., finance_controller, department_head), create dedicated directories for them rather than mixing with existing roles.

## Maintenance Notes

When modifying views:
1. Ensure controller routes match the view paths
2. Update any `@include()` or `@extends()` directives
3. Test with appropriate user role permissions
4. Check for any hardcoded paths in JavaScript

## Contact

For questions about this structure, refer to the project's main developer or check the Git history for this reorganization.
