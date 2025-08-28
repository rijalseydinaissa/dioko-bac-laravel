#!/bin/bash
set -e

echo "🚀 Starting Laravel application..."

# Debug des variables d'environnement
echo "🔍 Environment DEBUG:"
echo "DB_HOST: ${DB_HOST}"
echo "DB_DATABASE: ${DB_DATABASE}"
echo "DB_USERNAME: ${DB_USERNAME}"
echo "DB_SSLMODE: ${DB_SSLMODE}"
echo "APP_ENV: ${APP_ENV}"
echo "PORT: ${PORT:-8000}"

# Test de résolution DNS
echo "🌐 Testing DNS resolution..."
nslookup "$DB_HOST" || echo "⚠️ DNS resolution issue"

# Test de connectivité réseau
echo "🔌 Testing network connectivity..."
timeout 10 nc -zv "$DB_HOST" "$DB_PORT" || echo "❌ Cannot reach $DB_HOST:$DB_PORT"

# Test connexion PostgreSQL avec SSL
echo "🔐 Testing PostgreSQL SSL connection..."
for i in {1..20}; do
    if timeout 15 pg_isready -h "$DB_HOST" -p "$DB_PORT" -d "$DB_DATABASE" -U "$DB_USERNAME"; then
        echo "✅ PostgreSQL is ready!"
        break
    fi
    
    if [ $i -eq 20 ]; then
        echo "❌ PostgreSQL not ready after 20 attempts"
        
        # Test de connexion directe avec debug
        echo "🔍 Attempting direct SSL connection test..."
        timeout 15 psql "postgresql://$DB_USERNAME:$DB_PASSWORD@$DB_HOST:$DB_PORT/$DB_DATABASE?sslmode=require" -c "SELECT version();" || {
            echo "❌ Direct connection failed"
            echo "🔍 Trying with different SSL modes..."
            
            # Test avec différents modes SSL
            for sslmode in require prefer allow disable; do
                echo "Testing SSL mode: $sslmode"
                timeout 10 psql "postgresql://$DB_USERNAME:$DB_PASSWORD@$DB_HOST:$DB_PORT/$DB_DATABASE?sslmode=$sslmode" -c "SELECT 1;" 2>&1 && {
                    echo "✅ Connection successful with sslmode=$sslmode"
                    break
                } || echo "❌ Failed with sslmode=$sslmode"
            done
        }
        
        echo "⚠️ Continuing despite connection issues..."
        break
    fi
    
    echo "🔍 Attempt $i/20: Waiting for PostgreSQL..."
    sleep 3
done

# Clear Laravel cache avant test
echo "🧹 Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Test connexion Laravel avec debug détaillé
echo "🔍 Testing Laravel database connection..."
php -r "
try {
    \$pdo = new PDO(
        'pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE;sslmode=require',
        '$DB_USERNAME',
        '$DB_PASSWORD',
        [
            PDO::ATTR_TIMEOUT => 30,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
    echo '✅ Direct PDO connection successful!' . PHP_EOL;
    \$stmt = \$pdo->query('SELECT version()');
    echo '✅ PostgreSQL version: ' . \$stmt->fetchColumn() . PHP_EOL;
} catch (Exception \$e) {
    echo '❌ PDO connection failed: ' . \$e->getMessage() . PHP_EOL;
}
" || echo "⚠️ PHP PDO test failed"

# Test Laravel DB
timeout 20 php artisan migrate:status || {
    echo "⚠️ Laravel migrate:status failed"
    echo "🔧 Attempting to fix Laravel configuration..."
    php artisan config:cache
}

# Migrations
echo "📊 Running migrations..."
timeout 60 php artisan migrate --force || {
    echo "⚠️ Migrations failed"
    echo "🔍 Checking current migration status..."
    timeout 15 php artisan migrate:status || echo "⚠️ Cannot check migration status"
}

# Optimisations finales
echo "⚡ Final optimizations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache || echo "⚠️ View cache failed"
php artisan storage:link 2>/dev/null || echo "⚠️ Storage link exists"

# Démarrage serveur
echo "🎯 Starting Laravel server on port ${PORT:-8000}..."
echo "🌐 Application will be available at: https://dioko-bac-laravel.onrender.com"

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"