@echo off
REM ====================================================
REM Production Deployment Script for Windows
REM Prevents 504 Gateway Time-out errors by optimizing
REM the application before first user access
REM ====================================================

echo ====================================================
echo    Production Deployment Starting
echo ====================================================
echo.

REM Check if we're in the right directory
if not exist "artisan" (
    echo ERROR: Must be run from Laravel root directory
    exit /b 1
)

echo Step 1: Installing/Updating Dependencies
echo ------------------------------------------------
call composer install --optimize-autoloader --no-dev
if %ERRORLEVEL% NEQ 0 (
    echo WARNING: Composer install had issues
)
echo.

echo Step 2: Setting Permissions
echo ------------------------------------------------
echo Creating cache directories if needed...
if not exist "storage\framework\cache\data" mkdir storage\framework\cache\data
if not exist "storage\framework\sessions" mkdir storage\framework\sessions
if not exist "storage\framework\views" mkdir storage\framework\views
if not exist "storage\logs" mkdir storage\logs
echo OK
echo.

echo Step 3: Environment Check
echo ------------------------------------------------
if not exist ".env" (
    echo WARNING: .env file not found!
    echo Please copy .env.example to .env and configure it
    pause
)
echo.

echo Step 4: Running Warmup Script
echo ------------------------------------------------
php warmup-system.php
echo.

echo Step 5: Building Frontend Assets
echo ------------------------------------------------
call npm install
call npm run build
if %ERRORLEVEL% NEQ 0 (
    echo WARNING: Asset build had issues
)
echo.

echo ====================================================
echo    Deployment Complete!
echo ====================================================
echo.
echo Your application is optimized and ready.
echo.
echo IMPORTANT: Restart your web server to apply all changes:
echo   - For nginx: nginx -s reload
echo   - For Apache: restart Apache service
echo   - For php-fpm: restart PHP-FPM service
echo.
echo After restart, the first page load should NOT timeout.
echo.

pause
