<?php

/**
 * Configuration Loader Class
 * 
 * This class loads configuration from environment variables and config files
 * with fallback support and validation.
 */

class ConfigLoader
{
    private static $config = null;
    private static $envLoaded = false;
    
    /**
     * Load environment variables from .env file
     * 
     * @param string $envFile Path to .env file
     * @return bool Success status
     */
    public static function loadEnv(string $envFile = '.env'): bool
    {
        if (self::$envLoaded) {
            return true;
        }
        
        $envPath = __DIR__ . '/' . $envFile;
        
        if (!file_exists($envPath)) {
            return false;
        }
        
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }
                
                // Set environment variable if not already set
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
        
        self::$envLoaded = true;
        return true;
    }
    
    /**
     * Get environment variable with fallback
     * 
     * @param string $key Environment variable key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function env(string $key, $default = null)
    {
        // Try $_ENV first
        if (isset($_ENV[$key])) {
            return self::parseValue($_ENV[$key]);
        }
        
        // Try getenv()
        $value = getenv($key);
        if ($value !== false) {
            return self::parseValue($value);
        }
        
        return $default;
    }
    
    /**
     * Parse environment value to appropriate type
     * 
     * @param string $value
     * @return mixed
     */
    private static function parseValue(string $value)
    {
        // Boolean values
        if (in_array(strtolower($value), ['true', 'false'])) {
            return strtolower($value) === 'true';
        }
        
        // Numeric values
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float)$value : (int)$value;
        }
        
        // Null value
        if (strtolower($value) === 'null') {
            return null;
        }
        
        return $value;
    }
    
    /**
     * Get database configuration
     * 
     * @return array
     */
    public static function getDatabaseConfig(): array
    {
        self::loadEnv();
        
        // Try to load settings from the database if available
        $settings = self::loadSettingsFromDatabase();
        
        return [
            'host' => $settings['db.host'] ?? self::env('DB_HOST', 'localhost'),
            'port' => (int)($settings['db.port'] ?? self::env('DB_PORT', 1433)),
            'database' => $settings['db.database'] ?? self::env('DB_DATABASE', 'Database'),
            'username' => $settings['db.username'] ?? self::env('DB_USERNAME', 'Admin'),
            'password' => $settings['db.password'] ?? self::env('DB_PASSWORD', '122002'),
            
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8,
                PDO::SQLSRV_ATTR_QUERY_TIMEOUT => (int)($settings['db.query_timeout'] ?? self::env('DB_QUERY_TIMEOUT', 30)),
                PDO::ATTR_TIMEOUT => (int)($settings['db.timeout'] ?? self::env('DB_TIMEOUT', 30)),
            ],
            
            'advanced_options' => [
                'encrypt' => $settings['db.encrypt'] ?? self::env('DB_ENCRYPT', 'no'),
                'trust_server_certificate' => ($settings['db.trust_server_certificate'] ?? self::env('DB_TRUST_SERVER_CERTIFICATE', true)) === 'true',
                'connection_pooling' => ($settings['db.connection_pooling'] ?? self::env('DB_CONNECTION_POOLING', true)) === 'true',
                'multiple_active_result_sets' => ($settings['db.multiple_active_result_sets'] ?? self::env('DB_MULTIPLE_ACTIVE_RESULT_SETS', false)) === 'true',
            ]
        ];
    }
    
    /**
     * Get application configuration
     * 
     * @return array
     */
    public static function getAppConfig(): array
    {
        self::loadEnv();
        
        return [
            'environment' => self::env('APP_ENV', 'development'),
            'debug' => self::env('APP_DEBUG', true),
            'memory_limit' => self::env('MEMORY_LIMIT', '512M'),
            'max_execution_time' => self::env('MAX_EXECUTION_TIME', 300),
        ];
    }
    
    /**
     * Get logging configuration
     * 
     * @return array
     */
    public static function getLoggingConfig(): array
    {
        self::loadEnv();
        
        return [
            'log_queries' => self::env('LOG_QUERIES', false),
            'log_errors' => self::env('LOG_ERRORS', true),
            'log_level' => self::env('LOG_LEVEL', 'debug'),
            'log_file' => __DIR__ . '/logs/database.log',
        ];
    }
    
    /**
     * Load settings from database
     * 
     * @return array
     */
    public static function loadSettingsFromDatabase(): array
    {
        $settings = [];
        
        try {
            // Create a temporary connection to get settings
            $host = self::env('DB_HOST', 'localhost');
            $port = self::env('DB_PORT', 1433);
            $database = self::env('DB_DATABASE', 'Database');
            $username = self::env('DB_USERNAME', 'Admin');
            $password = self::env('DB_PASSWORD', '122002');
            
            $dsn = "sqlsrv:Server=$host,$port;Database=$database";
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]);
            
            $stmt = $pdo->query('SELECT [key], [value] FROM settings');
            if ($stmt) {
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $settings[$row['key']] = $row['value'];
                }
            }
        } catch (\Exception $e) {
            // If there's an error, just return empty settings
            // This allows the system to fall back to environment variables
        }
        
        return $settings;
    }
    
    /**
     * Get complete configuration
     * 
     * @return array
     */
    public static function getConfig(): array
    {
        if (self::$config === null) {
            self::$config = [
                'database' => self::getDatabaseConfig(),
                'app' => self::getAppConfig(),
                'logging' => self::getLoggingConfig(),
            ];
        }
        
        return self::$config;
    }
    
    /**
     * Validate configuration
     * 
     * @return array Validation results
     */
    public static function validateConfig(): array
    {
        $config = self::getConfig();
        $errors = [];
        $warnings = [];
        
        // Validate database configuration
        if (empty($config['database']['host'])) {
            $errors[] = 'Database host is required';
        }
        
        if (empty($config['database']['database'])) {
            $errors[] = 'Database name is required';
        }
        
        if (empty($config['database']['username'])) {
            $errors[] = 'Database username is required';
        }
        
        if ($config['database']['port'] < 1 || $config['database']['port'] > 65535) {
            $errors[] = 'Database port must be between 1 and 65535';
        }
        
        // Validate security settings
        if ($config['app']['environment'] === 'production') {
            if ($config['app']['debug'] === true) {
                $warnings[] = 'Debug mode should be disabled in production';
            }
            
            if ($config['database']['advanced_options']['encrypt'] === 'no') {
                $warnings[] = 'Database encryption should be enabled in production';
            }
            
            if ($config['database']['advanced_options']['trust_server_certificate'] === true) {
                $warnings[] = 'Server certificate should be properly validated in production';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
    
    /**
     * Display configuration summary
     * 
     * @param bool $hidePassword Whether to hide password in output
     */
    public static function displayConfig(bool $hidePassword = true): void
    {
        $config = self::getConfig();
        
        echo "=== Configuration Summary ===\n";
        
        echo "\nDatabase Configuration:\n";
        echo "  Host: " . $config['database']['host'] . "\n";
        echo "  Port: " . $config['database']['port'] . "\n";
        echo "  Database: " . $config['database']['database'] . "\n";
        echo "  Username: " . $config['database']['username'] . "\n";
        echo "  Password: " . ($hidePassword ? str_repeat('*', strlen($config['database']['password'])) : $config['database']['password']) . "\n";
        
        echo "\nAdvanced Options:\n";
        foreach ($config['database']['advanced_options'] as $key => $value) {
            echo "  " . ucfirst(str_replace('_', ' ', $key)) . ": " . ($value === true ? 'true' : ($value === false ? 'false' : $value)) . "\n";
        }
        
        echo "\nApplication Configuration:\n";
        echo "  Environment: " . $config['app']['environment'] . "\n";
        echo "  Debug: " . ($config['app']['debug'] ? 'true' : 'false') . "\n";
        echo "  Memory Limit: " . $config['app']['memory_limit'] . "\n";
        echo "  Max Execution Time: " . $config['app']['max_execution_time'] . "s\n";
        
        echo "\nLogging Configuration:\n";
        echo "  Log Queries: " . ($config['logging']['log_queries'] ? 'true' : 'false') . "\n";
        echo "  Log Errors: " . ($config['logging']['log_errors'] ? 'true' : 'false') . "\n";
        echo "  Log Level: " . $config['logging']['log_level'] . "\n";
        
        // Validation
        $validation = self::validateConfig();
        
        if (!$validation['valid']) {
            echo "\n❌ Configuration Errors:\n";
            foreach ($validation['errors'] as $error) {
                echo "  - " . $error . "\n";
            }
        }
        
        if (!empty($validation['warnings'])) {
            echo "\n⚠️  Configuration Warnings:\n";
            foreach ($validation['warnings'] as $warning) {
                echo "  - " . $warning . "\n";
            }
        }
        
        if ($validation['valid'] && empty($validation['warnings'])) {
            echo "\n✅ Configuration is valid!\n";
        }
    }
}