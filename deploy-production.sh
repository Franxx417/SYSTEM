#!/bin/bash
####################################################
# Production Deployment Script for Linux/Unix
# Prevents 504 Gateway Time-out errors by optimizing
# the application before first user access
####################################################

set -e  # Exit on error

echo "===================================================="
echo "   Production Deployment Starting"
echo "===================================================="
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ ERROR: Must be run from Laravel root directory"
    exit 1
fi

echo "Step 1: Installing/Updating Dependencies"
echo "------------------------------------------------"
composer install --optimize-autoloader --no-dev
echo ""

echo "Step 2: Setting Permissions"
echo "------------------------------------------------"
echo "Creating cache directories if needed..."
mkdir -p storage/framework/{cache/data,sessions,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache

echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache
if [ -n "$SUDO_USER" ]; then
    chown -R www-data:www-data storage bootstrap/cache
    echo "✅ Ownership set to www-data"
fi
echo "✅ Permissions set"
echo ""

echo "Step 3: Environment Check"
echo "------------------------------------------------"
if [ ! -f ".env" ]; then
    echo "⚠️  WARNING: .env file not found!"
    echo "Please copy .env.example to .env and configure it"
    read -p "Press Enter to continue..."
fi
echo ""

echo "Step 4: Running Warmup Script"
echo "------------------------------------------------"
php warmup-system.php
echo ""

echo "Step 5: Building Frontend Assets"
echo "------------------------------------------------"
npm install
npm run build
echo ""

echo "===================================================="
echo "   Deployment Complete!"
echo "===================================================="
echo ""
echo "✅ Your application is optimized and ready."
echo ""
echo "IMPORTANT: Restart your services to apply all changes:"
echo "  sudo systemctl reload nginx"
echo "  sudo systemctl restart php8.2-fpm"
echo ""
echo "After restart, the first page load should NOT timeout."
echo ""
