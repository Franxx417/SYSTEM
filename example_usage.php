<?php

/**
 * Comprehensive SQL Server PDO Usage Examples
 * 
 * This file demonstrates various ways to use the SqlServerPDOConnection class
 * including CRUD operations, prepared statements, transactions, and error handling.
 * 
 * Usage: php example_usage.php
 */

// Include required files
require_once 'SqlServerPDOConnection.php';

// Load configuration
$config = require 'config.php';

echo "=== SQL Server PDO Usage Examples ===\n\n";

try {
    // Initialize connection
    $connection = new SqlServerPDOConnection(
        $config['database']['host'],
        $config['database']['database'],
        $config['database']['username'],
        $config['database']['password'],
        $config['database']['port'],
        $config['database']['options']
    );
    
    // Establish connection
    $pdo = $connection->connect();
    echo "✅ Connected to SQL Server successfully!\n\n";
    
    // Example 1: Create a sample table
    echo "Example 1: Creating Sample Table\n";
    echo "================================\n";
    
    $createTableSQL = "
        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='users' AND xtype='U')
        CREATE TABLE users (
            id INT IDENTITY(1,1) PRIMARY KEY,
            name NVARCHAR(100) NOT NULL,
            email NVARCHAR(255) UNIQUE NOT NULL,
            age INT,
            created_at DATETIME2 DEFAULT GETDATE(),
            updated_at DATETIME2 DEFAULT GETDATE()
        )
    ";
    
    $pdo->exec($createTableSQL);
    echo "✅ Table 'users' created or already exists.\n\n";
    
    // Example 2: Insert data using prepared statements
    echo "Example 2: Inserting Data with Prepared Statements\n";
    echo "==================================================\n";
    
    $insertSQL = "INSERT INTO users (name, email, age) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($insertSQL);
    
    $sampleUsers = [
        ['John Doe', 'john.doe@example.com', 30],
        ['Jane Smith', 'jane.smith@example.com', 25],
        ['Bob Johnson', 'bob.johnson@example.com', 35],
        ['Alice Brown', 'alice.brown@example.com', 28]
    ];
    
    foreach ($sampleUsers as $user) {
        try {
            $stmt->execute($user);
            echo "✅ Inserted user: {$user[0]} ({$user[1]})\n";
        } catch (PDOException $e) {
            // Handle duplicate key or other errors
            if (strpos($e->getMessage(), 'Violation of UNIQUE KEY constraint') !== false) {
                echo "⚠️  User {$user[1]} already exists, skipping...\n";
            } else {
                echo "❌ Error inserting {$user[0]}: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "\n";
    
    // Example 3: Select data with different fetch modes
    echo "Example 3: Selecting Data with Different Fetch Modes\n";
    echo "====================================================\n";
    
    // Fetch all as associative array
    $selectSQL = "SELECT id, name, email, age, created_at FROM users ORDER BY id";
    $stmt = $pdo->query($selectSQL);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "All users (FETCH_ASSOC):\n";
    foreach ($users as $user) {
        echo "  ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Age: {$user['age']}\n";
    }
    echo "\n";
    
    // Fetch single user
    $singleUserSQL = "SELECT * FROM users WHERE id = ?";
    $stmt = $pdo->prepare($singleUserSQL);
    $stmt->execute([1]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "Single user (ID=1):\n";
        echo "  Name: {$user['name']}\n";
        echo "  Email: {$user['email']}\n";
        echo "  Created: {$user['created_at']}\n";
    }
    echo "\n";
    
    // Example 4: Update data
    echo "Example 4: Updating Data\n";
    echo "========================\n";
    
    $updateSQL = "UPDATE users SET age = ?, updated_at = GETDATE() WHERE email = ?";
    $stmt = $pdo->prepare($updateSQL);
    
    $result = $stmt->execute([32, 'john.doe@example.com']);
    $rowsAffected = $stmt->rowCount();
    
    if ($result && $rowsAffected > 0) {
        echo "✅ Updated {$rowsAffected} user(s) successfully.\n";
    } else {
        echo "⚠️  No users were updated.\n";
    }
    echo "\n";
    
    // Example 5: Using transactions
    echo "Example 5: Using Transactions\n";
    echo "=============================\n";
    
    try {
        $pdo->beginTransaction();
        
        // Insert a new user
        $stmt = $pdo->prepare("INSERT INTO users (name, email, age) VALUES (?, ?, ?)");
        $stmt->execute(['Transaction User', 'transaction@example.com', 40]);
        $newUserId = $pdo->lastInsertId();
        
        // Update another user
        $stmt = $pdo->prepare("UPDATE users SET age = age + 1 WHERE id = ?");
        $stmt->execute([1]);
        
        // Commit the transaction
        $pdo->commit();
        echo "✅ Transaction completed successfully. New user ID: {$newUserId}\n";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "❌ Transaction failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Example 6: Using named parameters
    echo "Example 6: Using Named Parameters\n";
    echo "=================================\n";
    
    $namedSQL = "SELECT * FROM users WHERE age > :min_age AND age < :max_age ORDER BY age";
    $stmt = $pdo->prepare($namedSQL);
    $stmt->execute([
        ':min_age' => 25,
        ':max_age' => 35
    ]);
    
    $filteredUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Users aged between 25 and 35:\n";
    foreach ($filteredUsers as $user) {
        echo "  {$user['name']} (Age: {$user['age']})\n";
    }
    echo "\n";
    
    // Example 7: Aggregate functions and grouping
    echo "Example 7: Aggregate Functions\n";
    echo "==============================\n";
    
    $aggregateSQL = "
        SELECT 
            COUNT(*) as total_users,
            AVG(CAST(age as FLOAT)) as average_age,
            MIN(age) as youngest,
            MAX(age) as oldest
        FROM users
    ";
    
    $stmt = $pdo->query($aggregateSQL);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "User Statistics:\n";
    echo "  Total Users: {$stats['total_users']}\n";
    echo "  Average Age: " . round($stats['average_age'], 2) . "\n";
    echo "  Youngest: {$stats['youngest']}\n";
    echo "  Oldest: {$stats['oldest']}\n";
    echo "\n";
    
    // Example 8: Working with dates
    echo "Example 8: Working with Dates\n";
    echo "=============================\n";
    
    $dateSQL = "
        SELECT 
            name,
            created_at,
            DATEDIFF(day, created_at, GETDATE()) as days_since_created,
            FORMAT(created_at, 'yyyy-MM-dd HH:mm:ss') as formatted_date
        FROM users 
        ORDER BY created_at DESC
    ";
    
    $stmt = $pdo->query($dateSQL);
    $dateResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Users with date information:\n";
    foreach ($dateResults as $user) {
        echo "  {$user['name']}: Created {$user['days_since_created']} days ago ({$user['formatted_date']})\n";
    }
    echo "\n";
    
    // Example 9: Error handling with try-catch
    echo "Example 9: Error Handling\n";
    echo "=========================\n";
    
    try {
        // This will cause an error (invalid column name)
        $errorSQL = "SELECT invalid_column FROM users";
        $stmt = $pdo->query($errorSQL);
    } catch (PDOException $e) {
        echo "❌ Caught PDO Exception:\n";
        echo "  Error Code: " . $e->getCode() . "\n";
        echo "  Error Message: " . $e->getMessage() . "\n";
        echo "  ✅ Error handled gracefully.\n";
    }
    echo "\n";
    
    // Example 10: Stored procedure call (if available)
    echo "Example 10: System Information Queries\n";
    echo "======================================\n";
    
    $systemQueries = [
        'Database Name' => "SELECT DB_NAME() as database_name",
        'Server Version' => "SELECT @@VERSION as version",
        'Current User' => "SELECT SYSTEM_USER as current_user",
        'Server Name' => "SELECT @@SERVERNAME as server_name",
        'Current Time' => "SELECT GETDATE() as current_time"
    ];
    
    foreach ($systemQueries as $description => $query) {
        try {
            $stmt = $pdo->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $value = reset($result); // Get first value
            echo "  {$description}: {$value}\n";
        } catch (PDOException $e) {
            echo "  {$description}: Error - " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
    
    // Example 11: Cleanup (optional - remove test data)
    echo "Example 11: Cleanup Options\n";
    echo "===========================\n";
    echo "To clean up test data, uncomment the following lines:\n";
    echo "// \$pdo->exec(\"DROP TABLE IF EXISTS users\");\n";
    echo "// echo \"✅ Test table dropped.\\n\";\n";
    
    // Uncomment to actually clean up:
    // $pdo->exec("DROP TABLE IF EXISTS users");
    // echo "✅ Test table dropped.\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    
} catch (Exception $e) {
    echo "❌ General Error: " . $e->getMessage() . "\n";
    
} finally {
    // Close connection
    if (isset($connection)) {
        $connection->close();
        echo "\n✅ Database connection closed.\n";
    }
}

echo "\n=== Examples Complete ===\n";

// Display performance tips
echo "\n=== Performance Tips ===\n";
echo "1. Use prepared statements for repeated queries\n";
echo "2. Use transactions for multiple related operations\n";
echo "3. Fetch only the columns you need\n";
echo "4. Use LIMIT/TOP for large result sets\n";
echo "5. Create appropriate indexes on frequently queried columns\n";
echo "6. Use connection pooling in production environments\n";
echo "7. Monitor query execution times and optimize slow queries\n";

echo "\n=== Security Best Practices ===\n";
echo "1. Always use prepared statements for user input\n";
echo "2. Validate and sanitize input data\n";
echo "3. Use least privilege principle for database users\n";
echo "4. Enable SSL/TLS encryption for connections\n";
echo "5. Regularly update SQL Server and PHP drivers\n";
echo "6. Log and monitor database access\n";
echo "7. Use environment variables for sensitive configuration\n";

echo "\n=== End of Examples ===\n";