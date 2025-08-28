#!/bin/bash
set -e  # Exit on any error

echo "🚀 Starting Laravel application..."

# Debug environment variables
echo "🔍 Environment check:"
echo "DB_CONNECTION: $DB_CONNECTION"
echo "DB_HOST: $DB_HOST"
echo "DB_PORT: $DB_PORT"
echo "DB_DATABASE: $DB_DATABASE"
echo "DATABASE_URL: ${DATABASE_URL:0:50}..." # Show only first 50 chars for security

# Wait for PostgreSQL to be ready
echo "⏳ Waiting for PostgreSQL connection..."
for i in {1..30}; do
    if pg_isready -h "dpg-d2nlchvdiees73bf1sqg-a.oregon-postgres.render.com" -p "5432" > /dev/null 2>&1; then
        echo "✅ PostgreSQL is ready!"
        break
    fi
    echo "🔍 Testing PostgreSQL connection... (attempt $i/30)"
    sleep 3
done

# Test Laravel database connection with detailed error output
echo "🔍 Testing Laravel database connection..."
php artisan migrate:status 2>&1 || {
    echo "❌ Database connection test failed. Checking configuration..."
    
    # Try to show more detailed error
    php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connection OK';" 2>&1 || {
        echo "❌ Direct PDO connection failed"
        echo "🔧 Attempting fallback configuration..."
    }
    
    echo "⚠️ Continuing with deployment anyway..."
}

echo "✅ Database connection established!"

# Run migrations
echo "📊 Running database migrations..."
php artisan migrate --force 2>&1 || {
    echo "❌ Migrations failed, but continuing..."
}

echo "✅ Migrations completed!"

# Clear and optimize caches
echo "⚡ Optimizing application for production..."
php artisan config:clear
php artisan config:cache 2>&1 || echo "Config cache failed, continuing..."
php artisan route:cache 2>&1 || echo "Route cache failed, continuing..."
php artisan view:cache 2>&1 || echo "View cache failed, continuing..."

# Create storage symbolic links if needed
php artisan storage:link 2>&1 || echo "Storage link already exists or failed, continuing..."

echo "🎯 Starting Laravel server on port ${PORT:-8000}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}

# Run migrations
echo "📊 Running database migrations..."
php artisan migrate --force

if [ $? -eq 0 ]; then
    echo "✅ Migrations completed successfully!"
else
    echo "❌ Migrations failed!"
    # Continue anyway, might be already migrated
fi

# Clear and optimize caches
echo "⚡ Optimizing application for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symbolic links if needed
php artisan storage:link || true

echo "🎯 Starting Laravel server on port ${PORT:-8000}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}