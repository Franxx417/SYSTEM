# SQL Server PDO Driver Installation Guide

This guide provides step-by-step instructions for installing and configuring the Microsoft SQL Server Driver for PHP (pdo_sqlsrv) on different operating systems.

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Windows Installation](#windows-installation)
3. [Linux Installation](#linux-installation)
4. [macOS Installation](#macos-installation)
5. [PHP Configuration](#php-configuration)
6. [Verification](#verification)
7. [Common Issues](#common-issues)

## Prerequisites

- PHP 8.2 or higher
- SQL Server 2012 or later (or Azure SQL Database)
- Administrator/root access to install drivers

## Windows Installation

### Method 1: Using PECL (Recommended)

1. **Install PECL** (if not already installed):
   ```bash
   # Download and install PECL
   php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
   php composer-setup.php
   ```

2. **Install SQL Server drivers**:
   ```bash
   pecl install sqlsrv
   pecl install pdo_sqlsrv
   ```

### Method 2: Manual Installation

1. **Download Microsoft Drivers for PHP for SQL Server**:
   - Visit: https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server
   - Download the appropriate version for your PHP version

2. **Extract and copy DLL files**:
   ```bash
   # Extract the downloaded file
   # Copy the appropriate DLL files to your PHP extension directory
   # For PHP 8.2 x64 Non Thread Safe:
   copy php_sqlsrv_82_nts_x64.dll C:\php\ext\
   copy php_pdo_sqlsrv_82_nts_x64.dll C:\php\ext\
   ```

3. **Install Microsoft ODBC Driver**:
   - Download from: https://docs.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server
   - Install the latest version (ODBC Driver 18 for SQL Server)

## Linux Installation

### Ubuntu/Debian

1. **Install Microsoft repository**:
   ```bash
   curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add -
   curl https://packages.microsoft.com/config/ubuntu/20.04/prod.list > /etc/apt/sources.list.d/mssql-release.list
   apt-get update
   ```

2. **Install ODBC Driver**:
   ```bash
   ACCEPT_EULA=Y apt-get install -y msodbcsql18
   apt-get install -y unixodbc-dev
   ```

3. **Install PHP extensions**:
   ```bash
   pecl install sqlsrv
   pecl install pdo_sqlsrv
   ```

### CentOS/RHEL/Fedora

1. **Install Microsoft repository**:
   ```bash
   curl https://packages.microsoft.com/config/rhel/8/prod.repo > /etc/yum.repos.d/mssql-release.repo
   yum update
   ```

2. **Install ODBC Driver**:
   ```bash
   ACCEPT_EULA=Y yum install -y msodbcsql18
   yum install -y unixODBC-devel
   ```

3. **Install PHP extensions**:
   ```bash
   pecl install sqlsrv
   pecl install pdo_sqlsrv
   ```

## macOS Installation

### Using Homebrew

1. **Install prerequisites**:
   ```bash
   brew tap microsoft/mssql-release https://github.com/Microsoft/homebrew-mssql-release
   brew update
   ```

2. **Install ODBC Driver**:
   ```bash
   HOMEBREW_NO_ENV_FILTERING=1 ACCEPT_EULA=Y brew install msodbcsql18 mssql-tools18
   ```

3. **Install PHP extensions**:
   ```bash
   pecl install sqlsrv
   pecl install pdo_sqlsrv
   ```

## PHP Configuration

### 1. Update php.ini

Add the following lines to your `php.ini` file:

```ini
; Microsoft SQL Server extensions
extension=sqlsrv
extension=pdo_sqlsrv

; Optional: Increase memory limit for large datasets
memory_limit = 512M

; Optional: Set timezone
date.timezone = "Asia/Manila"
```

### 2. Find your php.ini location

```bash
php --ini
```

### 3. Restart web server

After modifying php.ini, restart your web server:

**Apache:**
```bash
# Windows
net stop apache2.4 && net start apache2.4

# Linux
sudo systemctl restart apache2
# or
sudo service apache2 restart
```

**Nginx:**
```bash
# Linux
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

**IIS (Windows):**
```bash
iisreset
```

## Verification

### 1. Check PHP Extensions

Create a PHP file to verify the extensions are loaded:

```php
<?php
// Check if extensions are loaded
if (extension_loaded('pdo_sqlsrv')) {
    echo "✓ pdo_sqlsrv extension is loaded\n";
} else {
    echo "✗ pdo_sqlsrv extension is NOT loaded\n";
}

if (extension_loaded('sqlsrv')) {
    echo "✓ sqlsrv extension is loaded\n";
} else {
    echo "✗ sqlsrv extension is NOT loaded\n";
}

// Show all loaded extensions
echo "\nAll loaded extensions:\n";
print_r(get_loaded_extensions());

// Show PDO drivers
echo "\nAvailable PDO drivers:\n";
print_r(PDO::getAvailableDrivers());
?>
```

### 2. Command Line Verification

```bash
# Check if extensions are loaded
php -m | grep -i sql

# Expected output:
# pdo_sqlsrv
# sqlsrv
```

### 3. Test Connection

Use the provided test script to verify your connection works.

## Common Issues

### Issue 1: "could not find driver" Error

**Solution:**
- Verify extensions are installed: `php -m | grep sql`
- Check php.ini has the extension lines
- Restart web server after php.ini changes
- Ensure you're editing the correct php.ini file

### Issue 2: "Login failed for user" Error

**Solution:**
- Verify SQL Server authentication mode (Mixed Mode)
- Check username and password
- Ensure user has proper database permissions
- Test connection with SQL Server Management Studio first

### Issue 3: "A network-related or instance-specific error occurred"

**Solution:**
- Verify SQL Server is running
- Check SQL Server Configuration Manager:
  - Enable TCP/IP protocol
  - Set TCP port to 1433
  - Restart SQL Server service
- Check firewall settings
- Verify server name/IP address

### Issue 4: "Cannot open database" Error

**Solution:**
- Verify database exists
- Check user permissions for the specific database
- Ensure database is online and accessible

### Issue 5: Extension Loading Issues on Linux

**Solution:**
```bash
# Check PHP extension directory
php -i | grep extension_dir

# Verify files exist
ls -la /usr/lib/php/*/
ls -la /usr/lib/php/*/pdo_sqlsrv.so
ls -la /usr/lib/php/*/sqlsrv.so

# Check PHP error log
tail -f /var/log/php_errors.log
```

### Issue 6: SSL/TLS Connection Issues

**Solution:**
Add to your connection options:
```php
$options = [
    PDO::SQLSRV_ATTR_TRUST_SERVER_CERTIFICATE => true,
    PDO::SQLSRV_ATTR_ENCRYPT => true,
];
```

## Additional Resources

- [Microsoft PHP Driver Documentation](https://docs.microsoft.com/en-us/sql/connect/php/)
- [PHP PDO Documentation](https://www.php.net/manual/en/book.pdo.php)
- [SQL Server Configuration Manager Guide](https://docs.microsoft.com/en-us/sql/relational-databases/sql-server-configuration-manager)

## Support

If you encounter issues not covered in this guide:

1. Check PHP error logs
2. Enable SQL Server error logging
3. Test connection with other tools (SSMS, sqlcmd)
4. Verify network connectivity
5. Check SQL Server and PHP versions compatibility

For additional help, consult the official Microsoft documentation or PHP community forums.