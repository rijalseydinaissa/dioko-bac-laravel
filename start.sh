#!/bin/bash
set -e  # Exit on error

echo "🚀 Starting Laravel application..."

# Vérifier les variables d'environnement
echo "🔍 Environment check:"
echo "DB_HOST: $DB_HOST"
echo "DB_DATABASE: $DB_DATABASE"
echo "DB_SSLMODE: $DB_SSLMODE"

# Attente DB (30 tentatives max)
echo "⏳ Waiting for PostgreSQL to be ready..."
for i in {1..30}; do
    if pg_isready -h "$DB_HOST" -p "$DB_PORT" -d "$DB_DATABASE" -U "$DB_USERNAME"; then
        echo "✅ PostgreSQL is ready!"
        break
    fi
    echo "🔍 Attempt $i: Waiting for DB..."
    sleep 3
done

# Test connexion Laravel
echo "🔍 Testing Laravel DB connection..."
php artisan migrate:status || echo "⚠️ Laravel DB check failed, continuing..."

# Migrations
echo "📊 Running migrations..."
php artisan migrate --force || echo "⚠️ Migrations failed (maybe already migrated)"

# Optimisations Laravel
echo "⚡ Optimizing app..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan storage:link || true

echo "🎯 Starting Laravel server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
