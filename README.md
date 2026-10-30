# SYMFOERP

Socle ERP basé sur **Symfony 7.4 LTS** et intégrant le cœur officiel **Dolibarr 24.0.2**.

## Architecture

- `/` : application Symfony (tableau de bord et services métier).
- `/dolibarr` : code source Dolibarr officiel, référencé comme sous-module Git sur le commit de la version 24.0.2.
- `compose.yaml` : environnement Docker de développement.
- Symfony et Dolibarr sont exécutés dans des conteneurs séparés, avec des bases MariaDB distinctes. Ils ne partagent ni kernel, ni sessions, ni schéma SQL.

## Démarrage avec Docker

Prérequis : Docker Engine et Docker Compose v2.

```bash
git clone https://github.com/amipm178/SYMFOERP.git
cd SYMFOERP
git submodule update --init --recursive
cp .env.docker.example .env.docker
```

Modifiez impérativement les mots de passe et secrets de `.env.docker`, puis lancez :

```bash
docker compose --env-file .env.docker up -d --build
docker compose --env-file .env.docker ps
docker compose --env-file .env.docker logs -f
```

Accès locaux :

- Symfony : http://localhost:8000
- Assistant d'installation Dolibarr : http://localhost:8080

Le conteneur Dolibarr initialise sa base dédiée au premier démarrage. Utilisez les identifiants administrateur définis par `DOLI_ADMIN_LOGIN` et `DOLI_ADMIN_PASSWORD` dans `.env.docker`. Changez-les avant le premier lancement.

Arrêt sans supprimer les données :

```bash
docker compose --env-file .env.docker down
```

Pour supprimer également les bases et les documents persistants (opération destructive) :

```bash
docker compose --env-file .env.docker down -v
```

## Données persistantes et isolation

Docker crée des volumes séparés pour :
- la base Symfony ;
- la base Dolibarr ;
- les documents téléversés dans Dolibarr ;
- les modules personnalisés Dolibarr ;
- le cache et les fichiers temporaires Symfony.

Les bases de données ne sont pas publiées sur les ports de l'hôte. Chaque application ne rejoint que son propre réseau Docker.

## Installation sans Docker

### Symfony

Prérequis : PHP 8.2+, Composer 2 et les extensions PHP nécessaires.

```bash
composer install
cp .env .env.local
# Renseigner DATABASE_URL et APP_SECRET dans .env.local
php bin/console cache:clear
symfony server:start
```

### Dolibarr

Pour une installation manuelle, configurer un serveur web PHP dont le document root pointe vers `dolibarr/htdocs`, puis suivre l'assistant Dolibarr sur le virtual host dédié. Utiliser une base de données indépendante de celle de Symfony.

## Licence

Dolibarr est distribué sous **GNU GPL-3.0-or-later**. Le code source et ses notices sont conservés dans le sous-module officiel. Les composants embarqués peuvent avoir leurs propres licences : consulter `dolibarr/COPYRIGHT` et `dolibarr/COPYING`.

Le code propre à SYMFOERP reste séparé du sous-module. Toute redistribution d'une œuvre combinant ou modifiant Dolibarr doit être examinée au regard de la GPL et des licences des composants inclus.

## État

La configuration Docker et les instructions de démarrage sont versionnées. Les conteneurs n'ont pas été démarrés depuis cet environnement : l'installation réelle, la migration de base, et les tests HTTP doivent être validés sur une machine disposant de Docker.
