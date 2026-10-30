# SYMFOERP

Socle ERP construit avec **Symfony 7.4 LTS** et intégrant le cœur officiel de **Dolibarr ERP/CRM** comme sous-module Git.

## Architecture

- `/` : application Symfony (tableau de bord et futurs services métier).
- `/dolibarr` : dépôt officiel Dolibarr, épinglé sur la version **24.0.2**.
- Dolibarr conserve son cycle d'installation, son noyau PHP, ses modules et son schéma de base de données. Il n'est pas chargé comme un bundle Symfony.

Cette séparation évite de mélanger les deux kernels, les sessions et les mécanismes d'installation. Une intégration plus profonde pourra ensuite passer par l'API REST Dolibarr ou un adaptateur dédié.

## Récupérer le code Dolibarr

Après clonage du projet :

```bash
git clone https://github.com/amipm178/SYMFOERP.git
cd SYMFOERP
git submodule update --init --recursive
```

Le sous-module est épinglé sur le commit officiel correspondant à Dolibarr 24.0.2. Pour vérifier :

```bash
git -C dolibarr describe --tags --always
```

## Installer Symfony

Prérequis : PHP 8.2+, Composer 2 et les extensions PHP nécessaires.

```bash
composer install
cp .env .env.local
```

Renseigner `DATABASE_URL` et `APP_SECRET` dans `.env.local`, puis :

```bash
php bin/console cache:clear
symfony server:start
```

## Installer Dolibarr

Dolibarr doit être servi par PHP avec un document root pointant vers `dolibarr/htdocs` (ou via un virtual host dédié). Il est recommandé de le publier sur un hôte ou un préfixe isolé, par exemple `erp.example.test`, plutôt que de faire exécuter ses scripts PHP à travers le kernel Symfony.

1. Configurer un serveur web PHP (Apache ou Nginx) dont le document root est `<chemin-du-projet>/dolibarr/htdocs`.
2. Créer le fichier de configuration vide `dolibarr/htdocs/conf/conf.php` et accorder temporairement au serveur web le droit d'écriture sur ce fichier et sur le répertoire de documents Dolibarr.
3. Ouvrir l'URL `/install/` sur le virtual host Dolibarr et suivre l'assistant.
4. Utiliser une base de données dédiée à Dolibarr (MariaDB/MySQL ou PostgreSQL), distincte de celle de Symfony.
5. À la fin de l'installation, retirer les droits d'écriture non nécessaires et appliquer les recommandations de sécurité de Dolibarr.

Ne jamais committer `conf.php`, les secrets, les documents téléversés ou les données de production.

## Licence

Dolibarr est distribué sous **GNU GPL-3.0-or-later**. Le code source et ses notices de licence sont conservés dans le sous-module officiel. Les dépendances embarquées peuvent avoir leurs propres licences : consulter `dolibarr/COPYRIGHT` et `dolibarr/COPYING`.

Le code propre à SYMFOERP reste séparé du sous-module ; toute redistribution d'une œuvre combinant ou modifiant Dolibarr doit être examinée au regard de la GPL et des licences des composants inclus.

## État

Le code source Dolibarr est référencé dans Git. L'installation web, la configuration de la base de données et les tests d'exécution doivent encore être réalisés dans l'environnement de déploiement.
