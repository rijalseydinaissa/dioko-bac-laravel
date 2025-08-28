
Payment Management API

Backend Laravel pour l'application de gestion des paiements réguliers.
Déployé sur Render :https://dioko-bac-laravel.onrender.com
.

🚀 Installation Locale
Prérequis

PHP 8.1+

Composer

PostgreSQL 

Redis (optionnel)

Docker et Docker Compose (si utilisation)

Étapes

Cloner le repository

git clone : https://github.com/rijalseydinaissa/dioko-bac-laravel.git
cd payment-management-backend


Copier le fichier d'environnement

cp .env.example .env
php artisan key:generate
php artisan jwt:secret


Installer les dépendances

composer install


Configurer la base de données dans .env :

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=payment_management
DB_USERNAME=postgres
DB_PASSWORD=postgres


Exécuter les migrations et seeders

php artisan migrate
php artisan db:seed


Créer le lien symbolique pour le storage

php artisan storage:link


Lancer le serveur local

php artisan serve


L’API sera accessible sur http://localhost:8000.

🏃 Cloner et Tester Rapidement (pour un développeur)

Si vous venez de cloner le projet, voici la manière la plus rapide de le tester :

git clone <repository-url>
cd payment-management-backend
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
docker-compose up -d   # Si vous utilisez Docker
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve


L’API sera disponible sur http://localhost:8000.

📦 API Endpoints
Authentification

POST /api/auth/register – Inscription

POST /api/auth/login – Connexion

POST /api/auth/logout – Déconnexion

POST /api/auth/refresh – Rafraîchir le token

GET /api/auth/me – Profil utilisateur

Dashboard

GET /api/dashboard – Tableau de bord complet

GET /api/dashboard/monthly-stats – Statistiques mensuelles

GET /api/dashboard/payment-type-stats – Statistiques par type

Paiements

GET /api/payments – Liste des paiements

POST /api/payments – Créer un paiement

PATCH /api/payments/{id}/approve – Valider un paiement

PATCH /api/payments/{id}/retry – Relancer un paiement échoué

Fichiers

GET /api/files/payments/{id}/download – Télécharger un fichier

GET /api/files/payments/{id}/view – Visualiser un fichier

Système

GET /api/health – Vérification de l'état de l'API

🔧 Variables d'environnement principales
APP_NAME="Payment Management API"
APP_URL=http://localhost:8000
JWT_SECRET=your-jwt-secret-key
JWT_TTL=60

🐳 Docker
Lancer en local avec Docker Compose
docker-compose up -d


API disponible sur http://localhost:8000

Base de données PostgreSQL sur le port configuré

Commandes utiles
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan storage:link

🌐 Déploiement sur Render

Créer un Web Service sur Render

Build Command :

composer install --no-dev --optimize-autoloader


Start Command :

php artisan migrate --force && php artisan config:cache && php artisan serve --host=0.0.0.0 --port=$PORT


Ajouter les variables d’environnement sur Render (.env) pour la production

🔒 Sécurité

Authentification JWT avec expiration

Validation stricte des données d'entrée

Protection CORS configurée

Hachage sécurisé des mots de passe

Permissions sur les ressources

Limitation de l’upload des fichiers (5MB max)

🏗️ Architecture
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Services/
└── Traits/

database/
├── migrations/
├── seeders/
└── factories/

tests/
├── Feature/
└── Unit/

🧪 Tests

Lancer les tests :

php artisan test


Avec couverture :

php artisan test --coverage

🤝 Contribution

Fork le projet

Créer une branche feature (git checkout -b feature/AmazingFeature)

Commit et push (git commit -m 'Add some AmazingFeature')

Ouvrir une Pull Request

📄 Licence

MIT