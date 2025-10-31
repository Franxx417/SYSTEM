<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Roles
    |--------------------------------------------------------------------------
    |
    | This application supports exactly 2 roles. These are hardcoded by design
    | as part of the core business logic. DO NOT add additional roles without
    | also updating all role-based logic throughout the application.
    |
    */

    'types' => [
        'SUPERADMIN' => 'superadmin',
        'REQUESTOR' => 'requestor',
    ],

    'labels' => [
        'superadmin' => 'Super Admin',
        'requestor' => 'Requestor',
    ],

    'descriptions' => [
        'superadmin' => 'Full system administration, approvals, user management, database tools, status configuration',
        'requestor' => 'Creates and manages purchase orders, views own PO dashboard',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Permissions
    |--------------------------------------------------------------------------
    |
    | Define what each role can access. These are used for validation
    | and UI display logic.
    |
    */

    'permissions' => [
        'superadmin' => [
            'view_all_pos',
            'approve_pos',
            'manage_users',
            'manage_suppliers',
            'manage_items',
            'manage_statuses',
            'access_database',
            'view_logs',
            'manage_security',
            'manage_settings',
        ],
        'requestor' => [
            'create_po',
            'view_own_pos',
            'manage_items',
            'view_suppliers',
        ],
    ],

];
