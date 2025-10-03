#!/bin/bash

# Start script for Render deployment
set -e

echo "🚀 Starting Laravel API deployment..."

# Run database migrations (ignore errors if database not ready yet)
echo "📊 Running database migrations..."
php artisan migrate --force || echo "⚠️ Migration failed, check database connection"

# Optional: Seed database
echo "🌱 Seeding database..."
php artisan db:seed --force || echo "⚠️ Seeding skipped (no seeders found)"

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
