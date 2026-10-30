# SYMFOERP

Socle applicatif ERP construit sur **Symfony 7.4 LTS**.

## Prérequis

- PHP 8.2 ou supérieur
- Composer 2
- Une base de données compatible Doctrine (MySQL/MariaDB ou PostgreSQL)

## Installation

```bash
composer install
cp .env .env.local
# Renseigner DATABASE_URL dans .env.local
php bin/console doctrine:database:create
php bin/console cache:clear
symfony server:start
```

Ouvrir ensuite http://localhost:8000.

## Socle inclus

- Symfony FrameworkBundle, Routing et Console
- Twig pour les vues
- Doctrine ORM et migrations
- Symfony Security (configuration de départ)
- Structure initiale pour les modules ERP

> Le dépôt contient le code source et la configuration. Les dépendances seront installées localement avec `composer install` ; le répertoire `vendor/` n'est pas versionné.
