#!/bin/bash
set -e

echo "🚀 Starting Laravel application on Render..."

# Détection automatique de l'environnement Render
if [ -n "$RENDER_SERVICE_ID" ]; then
    echo "🔍 Render environment detected"
    
    # Configuration spécifique Render
    export DB_HOST="${DB_HOST:-dpg-d2nlchvdiees73bf1sqg-a}"
    export DB_SSLMODE="disable"  # Désactiver SSL pour connexion interne
    export PGSSLMODE="disable"
    
    echo "🔧 Using Render internal hostname: $DB_HOST"
    echo "🔓 SSL disabled for internal connection"
else
    echo "🏠 Local environment detected"
    export DB_SSLMODE="${DB_SSLMODE:-prefer}"
fi

# Debug des variables
echo "🔍 Database Configuration:"
echo "DB_HOST: ${DB_HOST}"
echo "DB_PORT: ${DB_PORT}"
echo "DB_DATABASE: ${DB_DATABASE}"
echo "DB_USERNAME: ${DB_USERNAME}"
echo "DB_SSLMODE: ${DB_SSLMODE}"

# Test de connectivité avec différentes approches
echo "🔌 Testing database connectivity..."

# Test 1: Connexion interne sans SSL
if timeout 10 pg_isready -h "$DB_HOST" -p "$DB_PORT" -d "$DB_DATABASE" -U "$DB_USERNAME" 2>/dev/null; then
    echo "✅ PostgreSQL internal connection ready!"
elif timeout 10 pg_isready -h "${DB_HOST}.virginia-postgres.render.com" -p "$DB_PORT" -d "$DB_DATABASE" -U "$DB_USERNAME" 2>/dev/null; then
    echo "✅ PostgreSQL external connection ready!"
    export DB_HOST="${DB_HOST}.virginia-postgres.render.com"
    export DB_SSLMODE="require"
else
    echo "⚠️ PostgreSQL not responding, but continuing..."
fi

# Clear caches Laravel
echo "🧹 Clearing Laravel caches..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

# Test de connexion Laravel avec gestion d'erreur
echo "🔍 Testing Laravel database connection..."
php artisan migrate:status 2>&1 || {
    echo "⚠️ Laravel DB connection failed, trying alternative configurations..."
    
    # Essayer sans SSL
    export DB_SSLMODE="disable"
    export PGSSLMODE="disable"
    php artisan config:clear
    php artisan migrate:status 2>&1 || {
        echo "⚠️ Still failing, trying with external host..."
        export DB_HOST="${DB_HOST}.virginia-postgres.render.com"
        export DB_SSLMODE="require"
        php artisan config:clear
        php artisan migrate:status 2>&1 || echo "⚠️ All connection methods failed"
    }
}

# Migrations conditionnelles
echo "📊 Running migrations..."
if php artisan migrate --force 2>&1; then
    echo "✅ Migrations completed successfully"
else
    echo "⚠️ Migrations failed, checking if tables exist..."
    php artisan migrate:status 2>&1 || echo "⚠️ Cannot determine migration status"
fi

# Optimisations
echo "⚡ Optimizing Laravel..."
php artisan config:cache
php artisan route:cache 2>/dev/null || echo "⚠️ Route cache failed"
php artisan view:cache 2>/dev/null || echo "⚠️ View cache failed"
php artisan storage:link 2>/dev/null || echo "ℹ️ Storage link already exists"

# Créer route de healthcheck si elle n'existe pas
echo "🏥 Setting up health check..."
if [ ! -f "routes/api.php" ] || ! grep -q "/health" routes/api.php; then
    echo "Route::get('/health', function () { return response()->json(['status' => 'ok', 'timestamp' => now()]); });" >> routes/api.php || true
fi

# Démarrage serveur
echo "🎯 Starting Laravel server..."
echo "🌐 Application available at: https://dioko-bac-laravel.onrender.com"
echo "🔍 Health check: https://dioko-bac-laravel.onrender.com/api/health"

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"