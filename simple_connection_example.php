<?php

/**
 * Simple SQL Server PDO Connection Example
 * 
 * This is the simplest possible example showing exactly how to connect
 * to SQL Server database named "Database" using Admin user with password 122002.
 * 
 * This example shows:
 * - Exact DSN string syntax
 * - Required connection parameters
 * - Basic error handling with try-catch
 * - Simple query execution
 */

echo "=== Simple SQL Server PDO Connection Example ===\n\n";

// Database connection parameters
$host = 'localhost';
$port = 1433;
$database = 'Database';
$username = 'Admin';
$password = '122002';

// Build the DSN (Data Source Name) string
// Format: sqlsrv:Server=host,port;Database=database_name
$dsn = "sqlsrv:Server={$host},{$port};Database={$database}";

echo "Connection Parameters:\n";
echo "- Host: {$host}\n";
echo "- Port: {$port}\n";
echo "- Database: {$database}\n";
echo "- Username: {$username}\n";
echo "- Password: " . str_repeat('*', strlen($password)) . "\n";
echo "- DSN: {$dsn}\n\n";

// PDO connection options
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Enable exceptions for errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Return associative arrays
    PDO::ATTR_EMULATE_PREPARES => false,  // Use native prepared statements
];

try {
    echo "Step 1: Creating PDO connection...\n";
    
    // Create PDO connection
    $pdo = new PDO($dsn, $username, $password, $options);
    
    echo "✅ Connection successful!\n\n";
    
    echo "Step 2: Testing connection with a simple query...\n";
    
    // Test query to verify connection works
    $query = "SELECT 
        @@VERSION as server_version,
        @@SERVERNAME as server_name,
        DB_NAME() as current_database,
        SYSTEM_USER as current_user,
        GETDATE() as current_time";
    
    $stmt = $pdo->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ Query executed successfully!\n\n";
    
    echo "Server Information:\n";
    echo "- Server Version: " . substr($result['server_version'], 0, 50) . "...\n";
    echo "- Server Name: " . $result['server_name'] . "\n";
    echo "- Current Database: " . $result['current_database'] . "\n";
    echo "- Current User: " . $result['current_user'] . "\n";
    echo "- Current Time: " . $result['current_time'] . "\n\n";
    
    echo "Step 3: Testing a parameterized query...\n";
    
    // Example of prepared statement with parameters
    $testQuery = "SELECT ? as test_value, ? as test_string, GETDATE() as query_time";
    $stmt = $pdo->prepare($testQuery);
    $stmt->execute([123, 'Hello SQL Server']);
    $testResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ Parameterized query successful!\n";
    echo "- Test Value: " . $testResult['test_value'] . "\n";
    echo "- Test String: " . $testResult['test_string'] . "\n";
    echo "- Query Time: " . $testResult['query_time'] . "\n\n";
    
    echo "🎉 All tests passed! Your SQL Server PDO connection is working perfectly.\n";
    
} catch (PDOException $e) {
    echo "❌ Connection failed!\n\n";
    echo "Error Details:\n";
    echo "- Error Code: " . $e->getCode() . "\n";
    echo "- Error Message: " . $e->getMessage() . "\n";
    echo "- File: " . $e->getFile() . "\n";
    echo "- Line: " . $e->getLine() . "\n\n";
    
    echo "Common Solutions:\n";
    
    $errorMessage = $e->getMessage();
    
    if (strpos($errorMessage, 'could not find driver') !== false) {
        echo "1. Install the pdo_sqlsrv extension:\n";
        echo "   - Windows: pecl install pdo_sqlsrv\n";
        echo "   - Linux: sudo pecl install pdo_sqlsrv\n";
        echo "   - Add 'extension=pdo_sqlsrv' to php.ini\n";
        echo "   - Restart your web server\n\n";
    }
    
    if (strpos($errorMessage, 'Login failed') !== false) {
        echo "2. Check your credentials:\n";
        echo "   - Verify username: {$username}\n";
        echo "   - Verify password is correct\n";
        echo "   - Check SQL Server authentication mode (Mixed Mode)\n";
        echo "   - Test login with SQL Server Management Studio first\n\n";
    }
    
    if (strpos($errorMessage, 'server was not found') !== false || 
        strpos($errorMessage, 'network-related') !== false) {
        echo "3. Check SQL Server connectivity:\n";
        echo "   - Verify SQL Server is running\n";
        echo "   - Check SQL Server Configuration Manager:\n";
        echo "     * Enable TCP/IP protocol\n";
        echo "     * Set TCP port to {$port}\n";
        echo "     * Restart SQL Server service\n";
        echo "   - Test connectivity: telnet {$host} {$port}\n\n";
    }
    
    if (strpos($errorMessage, 'Cannot open database') !== false) {
        echo "4. Check database access:\n";
        echo "   - Verify database '{$database}' exists\n";
        echo "   - Check user has permission to access the database\n";
        echo "   - Try connecting to 'master' database first\n\n";
    }
    
    echo "For detailed troubleshooting, see TROUBLESHOOTING_GUIDE.md\n";
    
} catch (Exception $e) {
    echo "❌ Unexpected error: " . $e->getMessage() . "\n";
    
} finally {
    // Close connection
    $pdo = null;
    echo "\nConnection closed.\n";
}

echo "\n=== Example Complete ===\n";

// Display exact code template for copy-paste
echo "\n=== Copy-Paste Template ===\n";
echo "<?php\n";
echo "// SQL Server PDO Connection Template\n";
echo "\$dsn = 'sqlsrv:Server=localhost,1433;Database=Database';\n";
echo "\$username = 'Admin';\n";
echo "\$password = '122002';\n";
echo "\n";
echo "\$options = [\n";
echo "    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n";
echo "    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n";
echo "    PDO::ATTR_EMULATE_PREPARES => false,\n";
echo "];\n";
echo "\n";
echo "try {\n";
echo "    \$pdo = new PDO(\$dsn, \$username, \$password, \$options);\n";
echo "    echo 'Connected successfully!';\n";
echo "    \n";
echo "    // Your queries here\n";
echo "    \$stmt = \$pdo->query('SELECT @@VERSION');\n";
echo "    \$result = \$stmt->fetch();\n";
echo "    print_r(\$result);\n";
echo "    \n";
echo "} catch (PDOException \$e) {\n";
echo "    echo 'Connection failed: ' . \$e->getMessage();\n";
echo "}\n";
echo "?>\n";

echo "\n=== Required PHP Extensions ===\n";
echo "Make sure these extensions are installed and enabled:\n";
echo "- pdo (usually enabled by default)\n";
echo "- pdo_sqlsrv (Microsoft SQL Server Driver for PHP)\n";
echo "\nCheck with: php -m | grep -i sql\n";

echo "\n=== DSN String Variations ===\n";
echo "Basic: sqlsrv:Server=localhost,1433;Database=Database\n";
echo "With instance: sqlsrv:Server=localhost\\SQLEXPRESS;Database=Database\n";
echo "With encryption: sqlsrv:Server=localhost,1433;Database=Database;Encrypt=yes\n";
echo "Trust certificate: sqlsrv:Server=localhost,1433;Database=Database;TrustServerCertificate=true\n";

echo "\n=== End of Simple Example ===\n";