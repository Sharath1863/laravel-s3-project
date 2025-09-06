#!/bin/bash

# Configuration
APPS=("app1" "app2")
DEPLOY_PATH="/var/www"
GIT_BRANCH="main"

# Function to deploy a single application
deploy_app() {
    local app=$1
    echo "Deploying $app..."
    
    cd "$DEPLOY_PATH/$app"
    
    # Pull latest changes
    git pull origin $GIT_BRANCH
    
    # Install/update dependencies
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
    
    # Clear caches
    php artisan optimize:clear
    
    # Run migrations
    php artisan migrate --force
    
    # Cache configuration and routes
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Set permissions
    chown -R www-data:www-data .
    chmod -R 755 storage bootstrap/cache
    
    echo "$app deployed successfully!"
}

# Deploy each application
for app in "${APPS[@]}"; do
    deploy_app "$app"
done

# Reload PHP-FPM and Nginx
systemctl reload php8.2-fpm || systemctl restart php8.2-fpm
systemctl reload nginx

echo "All applications deployed successfully!"
