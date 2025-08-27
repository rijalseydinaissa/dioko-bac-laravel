#!/bin/bash

echo "🚀 Starting Laravel application..."

# Attendre que la base de données soit prête
echo "⏳ Waiting for database connection..."
sleep 15

# Vérifier la connexion à la base de données
echo "🔍 Testing database connection..."
php artisan migrate:status || {
    echo "❌ Database connection failed. Retrying in 10 seconds..."
    sleep 10
}

# Lancer les migrations
echo "📊 Running database migrations..."
php artisan migrate --force

# Vérifier si les migrations ont réussi
if [ $? -eq 0 ]; then
    echo "✅ Migrations completed successfully!"
else
    echo "❌ Migrations failed!"
fi

# Optionnel : Lancer les seeders si nécessaire
# echo "🌱 Running database seeders..."
# php artisan db:seed --force

# Clear and cache config for production
echo "⚡ Optimizing application for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🎯 Starting Laravel server on port 8000..."

# Démarrer le serveur Laravel
php artisan serve --host=0.0.0.0 --port=8000