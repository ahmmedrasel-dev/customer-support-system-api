#!/bin/bash
set -e

echo "🚀 Starting Laravel API deployment..."

# Run migrations (ignore errors if DB not ready)
php artisan migrate --force || echo "⚠️ Migration failed, check database connection"

# Optional: Seed database
php artisan db:seed --force || echo "⚠️ Seeding skipped (no seeders found)"

# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 755 storage bootstrap/cache public
chown -R www-data:www-data storage bootstrap/cache public || true

echo "✅ Laravel API deployment completed successfully!"

# Start Apache
exec apache2-foreground
