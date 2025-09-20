# SQL Server PDO Troubleshooting Guide

This comprehensive guide helps you diagnose and resolve common issues when connecting to SQL Server using PDO in PHP.

## Table of Contents
1. [Quick Diagnostic Checklist](#quick-diagnostic-checklist)
2. [Common Error Messages](#common-error-messages)
3. [Extension Issues](#extension-issues)
4. [Connection Issues](#connection-issues)
5. [Authentication Issues](#authentication-issues)
6. [Database Issues](#database-issues)
7. [Performance Issues](#performance-issues)
8. [SSL/TLS Issues](#ssltls-issues)
9. [Debugging Tools](#debugging-tools)
10. [Environment-Specific Issues](#environment-specific-issues)

## Quick Diagnostic Checklist

Before diving into specific issues, run through this checklist:

### ✅ Prerequisites Check
- [ ] PHP 8.2+ installed
- [ ] SQL Server 2012+ running and accessible
- [ ] Microsoft ODBC Driver 18 for SQL Server installed
- [ ] pdo_sqlsrv extension installed and enabled
- [ ] Database "Database" exists
- [ ] User "Admin" has proper permissions

### ✅ Quick Tests
```bash
# Check PHP extensions
php -m | grep -i sql

# Test basic connectivity
telnet localhost 1433

# Run connection test
php test_connection.php
```

## Common Error Messages

### 1. "could not find driver"

**Error Message:**
```
PDOException: could not find driver
```

**Cause:** The pdo_sqlsrv extension is not installed or not enabled.

**Solutions:**
```bash
# Check if extension is loaded
php -m | grep pdo_sqlsrv

# Install extension (Windows)
pecl install pdo_sqlsrv

# Install extension (Linux)
sudo pecl install pdo_sqlsrv

# Add to php.ini
extension=pdo_sqlsrv

# Restart web server
sudo systemctl restart apache2  # or nginx/php-fpm
```

### 2. "Login failed for user"

**Error Message:**
```
SQLSTATE[28000]: [Microsoft][ODBC Driver 18 for SQL Server][SQL Server]Login failed for user 'Admin'.
```

**Causes & Solutions:**

**A. Wrong credentials:**
```php
// Verify credentials in config.php
'username' => 'Admin',
'password' => '122002',
```

**B. SQL Server authentication mode:**
```sql
-- Enable Mixed Mode authentication
-- In SQL Server Management Studio:
-- Right-click server -> Properties -> Security -> SQL Server and Windows Authentication mode
```

**C. User doesn't exist:**
```sql
-- Create the user if it doesn't exist
CREATE LOGIN Admin WITH PASSWORD = '122002';
USE Database;
CREATE USER Admin FOR LOGIN Admin;
ALTER ROLE db_owner ADD MEMBER Admin;
```

### 3. "A network-related or instance-specific error occurred"

**Error Message:**
```
SQLSTATE[08001]: [Microsoft][ODBC Driver 18 for SQL Server]TCP Provider: No connection could be made because the target machine actively refused it.
```

**Solutions:**

**A. SQL Server not running:**
```bash
# Windows
net start MSSQLSERVER

# Check service status
services.msc -> SQL Server (MSSQLSERVER)
```

**B. TCP/IP not enabled:**
1. Open SQL Server Configuration Manager
2. SQL Server Network Configuration -> Protocols for MSSQLSERVER
3. Enable TCP/IP
4. Right-click TCP/IP -> Properties -> IP Addresses
5. Set TCP Port to 1433 for all IP addresses
6. Restart SQL Server service

**C. Firewall blocking:**
```bash
# Windows Firewall
netsh advfirewall firewall add rule name="SQL Server" dir=in action=allow protocol=TCP localport=1433

# Linux iptables
sudo iptables -A INPUT -p tcp --dport 1433 -j ACCEPT
```

### 4. "Cannot open database"

**Error Message:**
```
SQLSTATE[42000]: [Microsoft][ODBC Driver 18 for SQL Server][SQL Server]Cannot open database "Database" requested by the login.
```

**Solutions:**

**A. Database doesn't exist:**
```sql
-- Create the database
CREATE DATABASE [Database];
```

**B. User lacks permissions:**
```sql
-- Grant access to the database
USE [Database];
CREATE USER Admin FOR LOGIN Admin;
ALTER ROLE db_datareader ADD MEMBER Admin;
ALTER ROLE db_datawriter ADD MEMBER Admin;
```

### 5. SSL/Certificate Errors

**Error Message:**
```
SQLSTATE[08001]: [Microsoft][ODBC Driver 18 for SQL Server]SSL Provider: The certificate chain was issued by an authority that is not trusted.
```

**Solutions:**

**A. Trust server certificate:**
```php
$options = [
    PDO::SQLSRV_ATTR_TRUST_SERVER_CERTIFICATE => true,
];
```

**B. Disable encryption (not recommended for production):**
```php
$dsn = 'sqlsrv:Server=localhost,1433;Database=Database;Encrypt=no';
```

## Extension Issues

### Installing pdo_sqlsrv on Different Systems

#### Windows
```bash
# Method 1: PECL
pecl install sqlsrv
pecl install pdo_sqlsrv

# Method 2: Manual download
# Download from: https://pecl.php.net/package/pdo_sqlsrv
# Extract DLL to PHP extension directory
# Add to php.ini: extension=pdo_sqlsrv
```

#### Ubuntu/Debian
```bash
# Install Microsoft repository
curl https://packages.microsoft.com/keys/microsoft.asc | sudo apt-key add -
curl https://packages.microsoft.com/config/ubuntu/20.04/prod.list | sudo tee /etc/apt/sources.list.d/mssql-release.list

# Install ODBC driver
sudo apt-get update
sudo ACCEPT_EULA=Y apt-get install -y msodbcsql18 unixodbc-dev

# Install PHP extensions
sudo pecl install sqlsrv pdo_sqlsrv

# Add to php.ini
echo "extension=sqlsrv" >> /etc/php/8.2/cli/php.ini
echo "extension=pdo_sqlsrv" >> /etc/php/8.2/cli/php.ini
```

#### CentOS/RHEL
```bash
# Install Microsoft repository
sudo curl https://packages.microsoft.com/config/rhel/8/prod.repo > /etc/yum.repos.d/mssql-release.repo

# Install ODBC driver
sudo ACCEPT_EULA=Y yum install -y msodbcsql18 unixODBC-devel

# Install PHP extensions
sudo pecl install sqlsrv pdo_sqlsrv
```

### Verifying Extension Installation

```php
<?php
// Check if extensions are loaded
if (extension_loaded('pdo_sqlsrv')) {
    echo "✅ pdo_sqlsrv is loaded\n";
    
    // Check available PDO drivers
    $drivers = PDO::getAvailableDrivers();
    if (in_array('sqlsrv', $drivers)) {
        echo "✅ sqlsrv PDO driver is available\n";
    } else {
        echo "❌ sqlsrv PDO driver is NOT available\n";
    }
} else {
    echo "❌ pdo_sqlsrv is NOT loaded\n";
}

// Display all loaded extensions
print_r(get_loaded_extensions());
?>
```

## Connection Issues

### DSN String Troubleshooting

#### Basic DSN Format
```php
// Standard format
$dsn = 'sqlsrv:Server=localhost,1433;Database=Database';

// With instance name
$dsn = 'sqlsrv:Server=localhost\SQLEXPRESS;Database=Database';

// With named instance and port
$dsn = 'sqlsrv:Server=localhost\INSTANCE,1433;Database=Database';
```

#### Advanced DSN Options
```php
$dsn = 'sqlsrv:Server=localhost,1433;Database=Database;'
     . 'Encrypt=yes;TrustServerCertificate=true;'
     . 'ConnectionPooling=true;MultipleActiveResultSets=false';
```

### Connection Timeout Issues

```php
$options = [
    PDO::ATTR_TIMEOUT => 30,                    // Connection timeout
    PDO::SQLSRV_ATTR_QUERY_TIMEOUT => 60,      // Query timeout
    PDO::SQLSRV_ATTR_LOGIN_TIMEOUT => 30,      // Login timeout
];
```

### Testing Connectivity

#### Using telnet
```bash
# Test if SQL Server port is open
telnet localhost 1433

# If successful, you'll see a blank screen
# If failed, you'll get "Connection refused" or similar
```

#### Using PowerShell (Windows)
```powershell
# Test TCP connection
Test-NetConnection -ComputerName localhost -Port 1433

# Check SQL Server services
Get-Service -Name "*SQL*"
```

#### Using netstat
```bash
# Check if SQL Server is listening on port 1433
netstat -an | grep 1433
# or on Windows:
netstat -an | findstr 1433
```

## Authentication Issues

### SQL Server Authentication Modes

#### Check Current Authentication Mode
```sql
SELECT CASE SERVERPROPERTY('IsIntegratedSecurityOnly')
    WHEN 1 THEN 'Windows Authentication Only'
    WHEN 0 THEN 'Mixed Mode (SQL Server and Windows Authentication)'
END AS AuthenticationMode;
```

#### Enable Mixed Mode Authentication
1. Open SQL Server Management Studio
2. Right-click server instance → Properties
3. Security tab → SQL Server and Windows Authentication mode
4. Restart SQL Server service

### User Management

#### Create SQL Server Login
```sql
-- Create login at server level
CREATE LOGIN Admin WITH PASSWORD = '122002';

-- Enable the login if disabled
ALTER LOGIN Admin ENABLE;

-- Check login status
SELECT name, is_disabled FROM sys.sql_logins WHERE name = 'Admin';
```

#### Create Database User
```sql
-- Switch to your database
USE [Database];

-- Create user for the login
CREATE USER Admin FOR LOGIN Admin;

-- Grant permissions
ALTER ROLE db_owner ADD MEMBER Admin;
-- or more restrictive:
-- ALTER ROLE db_datareader ADD MEMBER Admin;
-- ALTER ROLE db_datawriter ADD MEMBER Admin;
```

#### Check User Permissions
```sql
-- Check user's role memberships
SELECT 
    dp.name AS principal_name,
    dp.type_desc AS principal_type,
    r.name AS role_name
FROM sys.database_role_members rm
JOIN sys.database_principals dp ON rm.member_principal_id = dp.principal_id
JOIN sys.database_principals r ON rm.role_principal_id = r.principal_id
WHERE dp.name = 'Admin';
```

## Database Issues

### Database Existence and Status

#### Check if Database Exists
```sql
SELECT name FROM sys.databases WHERE name = 'Database';
```

#### Check Database Status
```sql
SELECT 
    name,
    state_desc,
    is_read_only,
    is_in_standby
FROM sys.databases 
WHERE name = 'Database';
```

#### Create Database if Missing
```sql
CREATE DATABASE [Database]
ON (
    NAME = 'Database_Data',
    FILENAME = 'C:\Program Files\Microsoft SQL Server\MSSQL15.MSSQLSERVER\MSSQL\DATA\Database.mdf',
    SIZE = 100MB,
    MAXSIZE = 1GB,
    FILEGROWTH = 10MB
)
LOG ON (
    NAME = 'Database_Log',
    FILENAME = 'C:\Program Files\Microsoft SQL Server\MSSQL15.MSSQLSERVER\MSSQL\DATA\Database.ldf',
    SIZE = 10MB,
    MAXSIZE = 100MB,
    FILEGROWTH = 1MB
);
```

## Performance Issues

### Connection Pooling

```php
// Enable connection pooling in DSN
$dsn = 'sqlsrv:Server=localhost,1433;Database=Database;ConnectionPooling=1';

// Or in connection options
$options = [
    PDO::SQLSRV_ATTR_CONNECTION_POOLING => true,
];
```

### Query Optimization

```php
// Use prepared statements for repeated queries
$stmt = $pdo->prepare("SELECT * FROM users WHERE age > ?");
$stmt->execute([25]);

// Limit result sets
$stmt = $pdo->prepare("SELECT TOP 100 * FROM large_table");

// Use appropriate fetch modes
$stmt->setFetchMode(PDO::FETCH_ASSOC); // More memory efficient than FETCH_BOTH
```

### Memory Management

```php
// Free statement resources
$stmt = null;

// Close cursor for large result sets
$stmt->closeCursor();

// Increase PHP memory limit if needed
ini_set('memory_limit', '512M');
```

## SSL/TLS Issues

### Certificate Problems

#### Trust Server Certificate (Development Only)
```php
$options = [
    PDO::SQLSRV_ATTR_TRUST_SERVER_CERTIFICATE => true,
];
```

#### Disable Encryption (Not Recommended)
```php
$dsn = 'sqlsrv:Server=localhost,1433;Database=Database;Encrypt=no';
```

#### Proper SSL Configuration (Production)
```php
$dsn = 'sqlsrv:Server=localhost,1433;Database=Database;Encrypt=yes;TrustServerCertificate=false';
```

## Debugging Tools

### Enable SQL Server Error Logging

1. SQL Server Management Studio
2. Management → SQL Server Logs
3. Configure error logging level

### PHP Error Logging

```php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log PDO errors
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];

// Custom error handler
try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    // Log to file
    file_put_contents('pdo_errors.log', date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n", FILE_APPEND);
}
```

### SQL Server Profiler

Use SQL Server Profiler or Extended Events to monitor:
- Login attempts
- Failed connections
- Query execution
- Performance metrics

### Network Monitoring

```bash
# Monitor network traffic (Linux)
sudo tcpdump -i any port 1433

# Windows netstat monitoring
netstat -an | findstr 1433
```

## Environment-Specific Issues

### Docker Containers

```dockerfile
# Dockerfile for PHP with SQL Server support
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    gnupg \
    unixodbc-dev

# Install Microsoft ODBC Driver
RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - \
    && curl https://packages.microsoft.com/config/debian/11/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18

# Install PHP extensions
RUN pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv
```

### Cloud Environments

#### Azure SQL Database
```php
$dsn = 'sqlsrv:Server=your-server.database.windows.net,1433;Database=Database;Encrypt=yes;TrustServerCertificate=false';
$username = 'Admin@your-server'; // Note the @server-name suffix
```

#### AWS RDS SQL Server
```php
$dsn = 'sqlsrv:Server=your-instance.region.rds.amazonaws.com,1433;Database=Database;Encrypt=yes';
```

### Load Balancer Issues

When using load balancers, ensure:
- Sticky sessions for connection pooling
- Health checks don't interfere with connections
- Timeout settings are appropriate

## Advanced Troubleshooting

### Registry Issues (Windows)

Check registry entries for ODBC drivers:
```
HKEY_LOCAL_MACHINE\SOFTWARE\ODBC\ODBCINST.INI\ODBC Driver 18 for SQL Server
```

### Environment Variables

```bash
# Set environment variables for debugging
export ODBCSYSINI=/etc
export ODBCINI=/etc/odbc.ini
```

### Trace Files

Enable ODBC tracing:
1. ODBC Data Source Administrator
2. Tracing tab
3. Enable tracing
4. Analyze trace files

## Getting Help

### Log Collection

When seeking help, collect:
```bash
# PHP information
php -v
php -m | grep sql
php --ini

# System information
uname -a  # Linux
systeminfo  # Windows

# SQL Server information
sqlcmd -S localhost -Q "SELECT @@VERSION"

# Error logs
tail -f /var/log/apache2/error.log  # Linux
# Check Event Viewer on Windows
```

### Useful Resources

- [Microsoft PHP Driver Documentation](https://docs.microsoft.com/en-us/sql/connect/php/)
- [PHP PDO Documentation](https://www.php.net/manual/en/book.pdo.php)
- [SQL Server Configuration Manager Guide](https://docs.microsoft.com/en-us/sql/relational-databases/sql-server-configuration-manager)
- [Stack Overflow SQL Server + PHP Tags](https://stackoverflow.com/questions/tagged/sql-server+php)

Remember: Always test connections in a development environment first, and never expose sensitive credentials in error messages or logs in production environments.