#!/bin/bash

# Stop on errors
set -e

# Variables
REPO="your-private-repo-url"
BRANCH="main"
APP_PATH="/var/www/awan"
BACKUP_PATH="/var/www/backups/awan"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Create backup
echo "Creating backup..."
if [ -d "$APP_PATH" ]; then
    mkdir -p "$BACKUP_PATH"
    tar -czf "$BACKUP_PATH/backup_$TIMESTAMP.tar.gz" -C "$APP_PATH" .
fi

# Clone/pull repository
if [ -d "$APP_PATH" ]; then
    echo "Pulling latest changes..."
    cd "$APP_PATH"
    git pull origin $BRANCH
else
    echo "Cloning repository..."
    git clone -b $BRANCH $REPO $APP_PATH
    cd "$APP_PATH"
fi

# Install/update dependencies
echo "Installing composer dependencies..."
composer install --no-dev --optimize-autoloader

# Environment setup
echo "Setting up environment..."
cp .env.production .env
php artisan key:generate --force

# Clear caches
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Database migrations
echo "Running database migrations..."
php artisan migrate --force

# Optimize
echo "Optimizing application..."
php artisan optimize
php artisan storage:link

# Set permissions
echo "Setting permissions..."
chown -R www-data:www-data .
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache

# Install/build frontend assets
echo "Building frontend assets..."
npm install
npm run build

# Restart services
echo "Restarting services..."
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx

echo "Deployment completed successfully!"
