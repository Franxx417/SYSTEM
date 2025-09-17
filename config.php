<?php

/**
 * SQL Server Database Configuration
 * 
 * This file contains all the database configuration settings for SQL Server PDO connection.
 * Modify these values according to your SQL Server setup.
 */

return [
    // Database Connection Settings
    'database' => [
        'host' => 'localhost',           // SQL Server host
        'port' => 1433,                  // SQL Server port (default: 1433)
        'database' => 'Database',        // Database name
        'username' => 'Admin',           // Database username
        'password' => '122002',          // Database password
        
        // Connection Options
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8,
            PDO::SQLSRV_ATTR_QUERY_TIMEOUT => 30,
            PDO::ATTR_TIMEOUT => 30,
        ],
        
        // Additional SQL Server specific options
        'sqlsrv_options' => [
            // Uncomment and modify as needed
            // 'TrustServerCertificate' => true,
            // 'Encrypt' => true,
            // 'ConnectionPooling' => true,
            // 'MultipleActiveResultSets' => false,
        ]
    ],
    
    // Environment-specific configurations
    'environments' => [
        'development' => [
            'host' => 'localhost',
            'port' => 1433,
            'database' => 'Database_Dev',
            'username' => 'Admin',
            'password' => '122002',
        ],
        
        'testing' => [
            'host' => 'localhost',
            'port' => 1433,
            'database' => 'Database_Test',
            'username' => 'Admin',
            'password' => '122002',
        ],
        
        'production' => [
            'host' => 'your-production-server',
            'port' => 1433,
            'database' => 'Database_Prod',
            'username' => 'Admin',
            'password' => 'your-secure-password',
        ]
    ],
    
    // Logging and Debug Settings
    'logging' => [
        'enabled' => true,
        'log_queries' => false,
        'log_errors' => true,
        'log_file' => __DIR__ . '/logs/database.log',
    ],
    
    // Connection Pool Settings
    'connection_pool' => [
        'max_connections' => 10,
        'timeout' => 30,
        'retry_attempts' => 3,
        'retry_delay' => 1, // seconds
    ]
];