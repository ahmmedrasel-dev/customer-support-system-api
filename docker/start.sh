#!/bin/bash

# Start script for Render deployment
set -e

echo "🚀 Starting Laravel API deployment..."

# Copy production environment file
if [ ! -f .env ]; then
    echo "📋 Copying production environment configuration..."
    cp .env.production .env
fi

# Generate app key if not set
echo "🔑 Generating application key..."
php artisan key:generate --force --no-interaction

# Create database file if it doesn't exist
echo "🗄️ Setting up database..."
touch database/database.sqlite

# Run database migrations
echo "📊 Running database migrations..."
php artisan migrate --force --no-interaction

# Seed database with initial data (optional)
echo "🌱 Seeding database..."
php artisan db:seed --force --no-interaction || echo "⚠️ Seeding skipped (no seeders found)"

# Clear and cache configuration
echo "🧹 Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo "🔒 Setting file permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true

echo "✅ Laravel API deployment completed successfully!"

# Start Apache
exec apache2-foreground