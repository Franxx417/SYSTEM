<?php

/**
 * Status Configuration
 * This file manages status colors and settings without modifying the database structure.
 * Can be dynamically updated by superadmin users.
 */

return [
    'status_colors' => [
        'Pending' => [
            'color' => '#ffc107',
            'css_class' => 'status-warning',
            'text_color' => '#000000',
            'description' => 'Purchase order is awaiting review'
        ],
        'Verified' => [
            'color' => '#0dcaf0',
            'css_class' => 'status-info',
            'text_color' => '#000000',
            'description' => 'Purchase order has been verified'
        ],
        'Approved' => [
            'color' => '#28a745',
            'css_class' => 'status-online',
            'text_color' => '#ffffff',
            'description' => 'Purchase order has been approved'
        ],
        'Received' => [
            'color' => '#20c997',
            'css_class' => 'status-success',
            'text_color' => '#ffffff',
            'description' => 'Purchase order items have been received'
        ],
        'Rejected' => [
            'color' => '#dc3545',
            'css_class' => 'status-offline',
            'text_color' => '#ffffff',
            'description' => 'Purchase order has been rejected'
        ]
    ],
    
    'status_order' => [
        'Pending',
        'Verified', 
        'Approved',
        'Received',
        'Rejected'
    ],
    
    'default_status' => 'Pending',
    
    'settings' => [
        'allow_status_creation' => true,
        'allow_status_deletion' => true,
        'require_remarks_on_change' => true,
        'show_status_history' => true
    ]
];
