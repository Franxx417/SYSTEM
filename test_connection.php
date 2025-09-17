<?php

/**
 * SQL Server PDO Connection Test Script
 * 
 * This script tests the PDO connection to SQL Server database and provides
 * detailed information about the connection status, server information,
 * and troubleshooting guidance.
 * 
 * Usage: php test_connection.php
 */

// Include the connection class
require_once 'SqlServerPDOConnection.php';

// Load configuration
$config = require 'config.php';

echo "=== SQL Server PDO Connection Test ===\n\n";

// Display configuration being used
echo "Configuration:\n";
echo "- Host: " . $config['database']['host'] . "\n";
echo "- Port: " . $config['database']['port'] . "\n";
echo "- Database: " . $config['database']['database'] . "\n";
echo "- Username: " . $config['database']['username'] . "\n";
echo "- Password: " . str_repeat('*', strlen($config['database']['password'])) . "\n\n";

try {
    // Create connection instance
    $connection = new SqlServerPDOConnection(
        $config['database']['host'],
        $config['database']['database'],
        $config['database']['username'],
        $config['database']['password'],
        $config['database']['port'],
        $config['database']['options']
    );
    
    echo "Step 1: Checking PHP Extensions...\n";
    $extensionCheck = $connection->checkRequiredExtensions();
    
    foreach ($extensionCheck['extensions'] as $extension => $loaded) {
        $status = $loaded ? '✓' : '✗';
        echo "  {$status} {$extension}: " . ($loaded ? 'Loaded' : 'NOT LOADED') . "\n";
    }
    
    if (!$extensionCheck['all_loaded']) {
        echo "\n❌ ERROR: Required PHP extensions are missing!\n";
        echo "Please install the missing extensions and restart your web server.\n";
        echo "See INSTALLATION_GUIDE.md for detailed instructions.\n";
        exit(1);
    }
    
    echo "\n✅ All required extensions are loaded.\n\n";
    
    echo "Step 2: Testing Database Connection...\n";
    $testResults = $connection->testConnection();
    
    if ($testResults['success']) {
        echo "✅ Connection successful!\n\n";
        
        echo "Server Information:\n";
        if ($testResults['server_info']) {
            foreach ($testResults['server_info'] as $key => $value) {
                echo "  - " . ucfirst(str_replace('_', ' ', $key)) . ": {$value}\n";
            }
        }
        
        echo "\nConnection Details:\n";
        echo "  - Driver: " . $testResults['driver_info'] . "\n";
        echo "  - DSN: " . $testResults['details']['dsn'] . "\n";
        
    } else {
        echo "❌ Connection failed!\n";
        echo "Error: " . $testResults['message'] . "\n\n";
        
        if (isset($testResults['details']['error'])) {
            $error = $testResults['details']['error'];
            echo "Error Details:\n";
            echo "  - Code: " . $error['code'] . "\n";
            echo "  - Message: " . $error['message'] . "\n";
            echo "  - File: " . $error['file'] . "\n";
            echo "  - Line: " . $error['line'] . "\n\n";
        }
        
        echo "Please check the troubleshooting section below.\n";
    }
    
    // If connection is successful, run additional tests
    if ($testResults['success']) {
        echo "\nStep 3: Testing Query Execution...\n";
        
        // Test basic query
        $queryResult = $connection->executeTestQuery("SELECT 1 as test_value, GETDATE() as current_time");
        
        if ($queryResult['success']) {
            echo "✅ Query execution successful!\n";
            echo "  - Execution time: " . $queryResult['execution_time'] . " ms\n";
            echo "  - Result: " . json_encode($queryResult['data']) . "\n";
        } else {
            echo "❌ Query execution failed!\n";
            echo "  - Error: " . $queryResult['message'] . "\n";
        }
        
        // Test database-specific query
        echo "\nStep 4: Testing Database-Specific Queries...\n";
        
        $dbQueries = [
            "Database Version" => "SELECT @@VERSION as version",
            "Current Database" => "SELECT DB_NAME() as current_database",
            "Server Name" => "SELECT @@SERVERNAME as server_name",
            "Current User" => "SELECT SYSTEM_USER as current_user",
            "Database Tables" => "SELECT COUNT(*) as table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'"
        ];
        
        foreach ($dbQueries as $description => $query) {
            $result = $connection->executeTestQuery($query);
            if ($result['success']) {
                echo "  ✅ {$description}: " . json_encode($result['data'][0]) . "\n";
            } else {
                echo "  ❌ {$description}: " . $result['message'] . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
}

echo "\n=== Connection Test Complete ===\n";

// Display troubleshooting information if there were issues
if (!isset($testResults) || !$testResults['success']) {
    displayTroubleshootingInfo();
}

/**
 * Display troubleshooting information
 */
function displayTroubleshootingInfo(): void
{
    echo "\n=== Troubleshooting Guide ===\n";
    
    echo "\n1. Extension Issues:\n";
    echo "   - Install Microsoft SQL Server Driver for PHP\n";
    echo "   - Add to php.ini: extension=pdo_sqlsrv\n";
    echo "   - Restart web server\n";
    echo "   - Verify: php -m | grep sql\n";
    
    echo "\n2. Connection Issues:\n";
    echo "   - Verify SQL Server is running\n";
    echo "   - Check SQL Server Configuration Manager:\n";
    echo "     * Enable TCP/IP protocol\n";
    echo "     * Set TCP port to 1433\n";
    echo "     * Restart SQL Server service\n";
    echo "   - Check firewall settings\n";
    
    echo "\n3. Authentication Issues:\n";
    echo "   - Verify username and password\n";
    echo "   - Check SQL Server authentication mode (Mixed Mode)\n";
    echo "   - Test with SQL Server Management Studio first\n";
    echo "   - Verify user has database permissions\n";
    
    echo "\n4. Database Issues:\n";
    echo "   - Verify database 'Database' exists\n";
    echo "   - Check database is online\n";
    echo "   - Verify user has access to the database\n";
    
    echo "\n5. Network Issues:\n";
    echo "   - Test connectivity: telnet localhost 1433\n";
    echo "   - Check if SQL Server Browser service is running\n";
    echo "   - Verify named pipes or TCP/IP is enabled\n";
    
    echo "\nFor detailed installation instructions, see INSTALLATION_GUIDE.md\n";
}

// Display system information
echo "\n=== System Information ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Operating System: " . PHP_OS . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n";
echo "Extension Directory: " . ini_get('extension_dir') . "\n";
echo "Configuration File: " . php_ini_loaded_file() . "\n";

// Display available PDO drivers
echo "\nAvailable PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";

echo "\n=== End of Test ===\n";