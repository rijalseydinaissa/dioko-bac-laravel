<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

### README.md
```markdown
# Payment Management API

Backend Laravel pour l'application de gestion des paiements réguliers.

## 🚀 Installation

### Prérequis
- PHP 8.1 ou supérieur
- Composer
- MySQL ou PostgreSQL
- Redis (optionnel)

### Étapes d'installation

1. **Cloner le repository**
```bash
git clone <repository-url>
cd payment-management-backend
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configuration de l'environnement**
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

4. **Configuration de la base de données**
Modifier les variables dans `.env` :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=payment_management
DB_USERNAME=root
DB_PASSWORD=
```

5. **Migrations et seeders**
```bash
php artisan migrate
php artisan db:seed
```

6. **Créer le lien symbolique pour le storage**
```bash
php artisan storage:link
```

7. **Lancer le serveur**
```bash
php artisan serve
```

L'API sera accessible sur `http://localhost:8000`

## 📚 Endpoints de l'API

### Authentification
- `POST /api/auth/register` - Inscription
- `POST /api/auth/login` - Connexion
- `POST /api/auth/logout` - Déconnexion
- `POST /api/auth/refresh` - Rafraîchir le token
- `GET /api/auth/me` - Profil utilisateur

### Dashboard
- `GET /api/dashboard` - Tableau de bord complet
- `GET /api/dashboard/monthly-stats` - Statistiques mensuelles
- `GET /api/dashboard/payment-type-stats` - Statistiques par type

### Paiements
- `GET /api/payments` - Liste des paiements avec filtres
- `POST /api/payments` - Créer un paiement
- `GET /api/payments/{id}` - Détails d'un paiement
- `PATCH /api/payments/{id}/cancel` - Annuler un paiement
- `PATCH /api/payments/{id}/retry` - Relancer un paiement échoué

### Fichiers
- `GET /api/files/payments/{id}/download` - Télécharger une pièce jointe
- `GET /api/files/payments/{id}/view` - Visualiser une pièce jointe

### Système
- `GET /api/health` - Vérification de l'état de l'API

## 🔧 Configuration

### Variables d'environnement principales

```env
# Application
APP_NAME="Payment Management API"
APP_URL=http://localhost:8000

# JWT Configuration
JWT_SECRET=your-jwt-secret-key
JWT_TTL=60

# Mock Payment API
MOCK_PAYMENT_API_URL=http://localhost:8001/api/payment
MOCK_PAYMENT_API_KEY=mock-api-key-123
```

## 🧪 Tests

Lancer les tests :
```bash
php artisan test
```

Avec couverture :
```bash
php artisan test --coverage
```

## 📦 Déploiement

### Sur AWS EC2

1. **Préparer l'instance EC2**
```bash
# Installer PHP, Composer, MySQL, Nginx
sudo apt update
sudo apt install php8.1-fpm php8.1-mysql php8.1-xml php8.1-curl php8.1-zip php8.1-mbstring
```

2. **Configuration Nginx**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/payment-management-backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

3. **Configuration de production**
```bash
# Optimisations Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Sur Render

1. **Créer un nouveau Web Service sur Render**
2. **Configuration**
   - Build Command: `composer install --no-dev --optimize-autoloader`
   - Start Command: `php artisan migrate --force && php artisan config:cache && php artisan serve --host=0.0.0.0 --port=$PORT`

### Variables d'environnement pour la production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Base de données (utiliser les credentials de production)
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# Sécurité
JWT_SECRET=your-super-secret-jwt-key
```

## 🔒 Sécurité

- Authentification JWT avec expiration
- Validation stricte des données d'entrée
- Protection CORS configurée
- Hachage sécurisé des mots de passe
- Vérification des permissions sur les ressources
- Limitation d'upload de fichiers (5MB max)

## 🏗️ Architecture

```
app/
├── Http/
│   ├── Controllers/     # Contrôleurs API
│   ├── Middleware/      # Middlewares personnalisés
│   ├── Requests/        # Validation des requêtes
│   └── Resources/       # Transformation des réponses JSON
├── Models/              # Modèles Eloquent
├── Services/            # Logique métier
└── Traits/              # Traits réutilisables

database/
├── migrations/          # Migrations de base de données
├── seeders/             # Données initiales
└── factories/           # Factories pour les tests

tests/
├── Feature/             # Tests d'intégration
└── Unit/                # Tests unitaires
```

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add some AmazingFeature'`)
4. Push sur la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📄 Licence

Ce projet est sous licence MIT.

## 📞 Support

Pour toute question ou support, contactez : admin@diokogroup.com
```