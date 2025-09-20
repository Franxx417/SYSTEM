<?php

/**
 * SQL Server PDO Connection Class
 * 
 * This class provides a robust PDO connection to SQL Server database
 * with comprehensive error handling, connection testing, and troubleshooting features.
 * 
 * Requirements:
 * - PHP 8.2+
 * - PDO extension
 * - pdo_sqlsrv extension (Microsoft SQL Server Driver for PHP)
 * 
 * @author Your Name
 * @version 1.0
 */

class SqlServerPDOConnection
{
    private $host;
    private $port;
    private $database;
    private $username;
    private $password;
    private $pdo;
    private $options;
    private $dsn;
    
    /**
     * Constructor
     * 
     * @param string $host Database host (default: localhost)
     * @param string $database Database name
     * @param string $username Database username
     * @param string $password Database password
     * @param int $port Database port (default: 1433)
     * @param array $options Additional PDO options
     */
    public function __construct(
        string $host = 'localhost',
        string $database = 'Database',
        string $username = 'Admin',
        string $password = '122002',
        int $port = 1433,
        array $options = []
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        
        // Default PDO options for SQL Server
        $this->options = array_merge([
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8,
            PDO::SQLSRV_ATTR_QUERY_TIMEOUT => 30,
            PDO::ATTR_TIMEOUT => 30,
        ], $options);
        
        $this->buildDSN();
    }
    
    /**
     * Build the DSN string for SQL Server
     */
    private function buildDSN(): void
    {
        // SQL Server DSN format: sqlsrv:Server=host,port;Database=database
        $this->dsn = sprintf(
            'sqlsrv:Server=%s,%d;Database=%s',
            $this->host,
            $this->port,
            $this->database
        );
    }
    
    /**
     * Establish connection to SQL Server
     * 
     * @return PDO
     * @throws PDOException
     */
    public function connect(): PDO
    {
        try {
            $this->pdo = new PDO(
                $this->dsn,
                $this->username,
                $this->password,
                $this->options
            );
            
            return $this->pdo;
        } catch (PDOException $e) {
            $this->handleConnectionError($e);
            throw $e;
        }
    }
    
    /**
     * Get the current PDO connection
     * 
     * @return PDO|null
     */
    public function getConnection(): ?PDO
    {
        return $this->pdo;
    }
    
    /**
     * Test the database connection
     * 
     * @return array Connection test results
     */
    public function testConnection(): array
    {
        $results = [
            'success' => false,
            'message' => '',
            'details' => [],
            'server_info' => null,
            'driver_info' => null
        ];
        
        try {
            // Check if required extensions are loaded
            $extensionCheck = $this->checkRequiredExtensions();
            $results['details']['extensions'] = $extensionCheck;
            
            if (!$extensionCheck['all_loaded']) {
                $results['message'] = 'Required PHP extensions are not loaded';
                return $results;
            }
            
            // Attempt connection
            $pdo = $this->connect();
            
            // Test with a simple query
            $stmt = $pdo->query("SELECT @@VERSION as version, @@SERVERNAME as server_name, DB_NAME() as database_name");
            $serverInfo = $stmt->fetch();
            
            $results['success'] = true;
            $results['message'] = 'Connection successful';
            $results['server_info'] = $serverInfo;
            $results['driver_info'] = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            $results['details']['dsn'] = $this->dsn;
            
        } catch (Exception $e) {
            $results['message'] = 'Connection failed: ' . $e->getMessage();
            $results['details']['error'] = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }
        
        return $results;
    }
    
    /**
     * Check if required PHP extensions are loaded
     * 
     * @return array Extension check results
     */
    public function checkRequiredExtensions(): array
    {
        $required = ['pdo', 'pdo_sqlsrv'];
        $results = [
            'all_loaded' => true,
            'extensions' => []
        ];
        
        foreach ($required as $extension) {
            $loaded = extension_loaded($extension);
            $results['extensions'][$extension] = $loaded;
            
            if (!$loaded) {
                $results['all_loaded'] = false;
            }
        }
        
        return $results;
    }
    
    /**
     * Execute a simple query to verify connection
     * 
     * @param string $query SQL query to execute
     * @return array Query results
     */
    public function executeTestQuery(string $query = "SELECT 1 as test"): array
    {
        $results = [
            'success' => false,
            'data' => null,
            'message' => '',
            'execution_time' => 0
        ];
        
        try {
            if (!$this->pdo) {
                $this->connect();
            }
            
            $startTime = microtime(true);
            $stmt = $this->pdo->query($query);
            $data = $stmt->fetchAll();
            $endTime = microtime(true);
            
            $results['success'] = true;
            $results['data'] = $data;
            $results['message'] = 'Query executed successfully';
            $results['execution_time'] = round(($endTime - $startTime) * 1000, 2); // in milliseconds
            
        } catch (Exception $e) {
            $results['message'] = 'Query failed: ' . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Handle connection errors with detailed troubleshooting information
     * 
     * @param PDOException $e
     */
    private function handleConnectionError(PDOException $e): void
    {
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();
        
        echo "\n=== SQL Server PDO Connection Error ===\n";
        echo "Error Code: {$errorCode}\n";
        echo "Error Message: {$errorMessage}\n";
        echo "DSN: {$this->dsn}\n";
        echo "Username: {$this->username}\n";
        
        // Provide specific troubleshooting based on error codes
        $this->provideTroubleshootingAdvice($errorCode, $errorMessage);
    }
    
    /**
     * Provide troubleshooting advice based on error codes
     * 
     * @param mixed $errorCode
     * @param string $errorMessage
     */
    private function provideTroubleshootingAdvice($errorCode, string $errorMessage): void
    {
        echo "\n=== Troubleshooting Advice ===\n";
        
        if (strpos($errorMessage, 'could not find driver') !== false) {
            echo "• Install Microsoft SQL Server Driver for PHP (pdo_sqlsrv)\n";
            echo "• Enable the extension in php.ini: extension=pdo_sqlsrv\n";
            echo "• Restart your web server after making changes\n";
        }
        
        if (strpos($errorMessage, 'Login failed') !== false) {
            echo "• Check username and password credentials\n";
            echo "• Verify the user has permission to access the database\n";
            echo "• Check if SQL Server authentication is enabled\n";
        }
        
        if (strpos($errorMessage, 'server was not found') !== false || 
            strpos($errorMessage, 'network-related') !== false) {
            echo "• Verify SQL Server is running and accessible\n";
            echo "• Check host and port configuration\n";
            echo "• Verify firewall settings allow connections on port {$this->port}\n";
            echo "• Check if TCP/IP protocol is enabled in SQL Server Configuration Manager\n";
        }
        
        if (strpos($errorMessage, 'Cannot open database') !== false) {
            echo "• Verify the database '{$this->database}' exists\n";
            echo "• Check if the user has permission to access this specific database\n";
        }
        
        echo "\n=== General Troubleshooting Steps ===\n";
        echo "1. Verify SQL Server is running: services.msc -> SQL Server\n";
        echo "2. Check SQL Server Configuration Manager:\n";
        echo "   - Enable TCP/IP protocol\n";
        echo "   - Set TCP Port to {$this->port}\n";
        echo "3. Test connection with SQL Server Management Studio first\n";
        echo "4. Check PHP extensions: php -m | grep -i sql\n";
        echo "5. Verify php.ini has: extension=pdo_sqlsrv\n";
    }
    
    /**
     * Close the database connection
     */
    public function close(): void
    {
        $this->pdo = null;
    }
    
    /**
     * Get connection information
     * 
     * @return array
     */
    public function getConnectionInfo(): array
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->database,
            'username' => $this->username,
            'dsn' => $this->dsn,
            'connected' => $this->pdo !== null
        ];
    }
    
    /**
     * Destructor - ensure connection is closed
     */
    public function __destruct()
    {
        $this->close();
    }
}