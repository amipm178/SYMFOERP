# SYMFOERP

Projet ERP dont l'objectif est de faire évoluer progressivement Dolibarr vers une architecture Symfony. **La priorité actuelle est une installation neuve et fonctionnelle de Dolibarr 24.0.2.** La reprise des données et la migration d'anciennes installations sont reportées.

## État actuel

- Symfony 7.4 : socle applicatif initial.
- Dolibarr 24.0.2 : code source officiel référencé dans le sous-module Git `dolibarr/`.
- Docker Compose : installation neuve de Dolibarr avec sa propre base MariaDB et volumes persistants.
- GitHub Actions : validation de Compose, construction et vérification de Symfony, et démarrage HTTP des deux applications.
- Aucune ancienne base n'est importée ou modifiée.

Pendant cette première phase, Dolibarr démarre avec son runtime officiel dans un conteneur dédié. Cela permet de valider une installation propre avant de porter progressivement ses fonctions dans Symfony. Le code Dolibarr n'est pas chargé directement comme un bundle Symfony.

## Prérequis

- Docker Engine
- Docker Compose v2
- Git

## Installation neuve

```bash
git clone https://github.com/amipm178/SYMFOERP.git
cd SYMFOERP
git submodule update --init --recursive
cp .env.docker.example .env.docker
```

Éditez `.env.docker` et remplacez impérativement tous les secrets d'exemple, notamment le mot de passe administrateur Dolibarr et les mots de passe MariaDB. Vous pouvez générer des valeurs aléatoires avec `openssl rand -hex 32`. Pour éviter d'exposer une instance de développement sur le réseau local, les ports HTTP sont liés à `127.0.0.1` : ils sont accessibles uniquement depuis la machine hôte. Puis lancez :

```bash
docker compose --env-file .env.docker config
docker compose --env-file .env.docker pull
docker compose --env-file .env.docker up -d --build
docker compose --env-file .env.docker ps
docker compose --env-file .env.docker logs -f dolibarr
```

Accès :

- Dolibarr 24.0.2 : http://localhost:8080
- Symfony : http://localhost:8000

Cette configuration est destinée au développement local, pas à une exposition directe sur Internet. Pour un déploiement public, prévoir un reverse proxy HTTPS, des secrets gérés hors du dépôt, des sauvegardes testées et une configuration Symfony en production.

L'image officielle Dolibarr est configurée pour initialiser automatiquement une nouvelle instance au premier démarrage. Les paramètres `DOLI_ADMIN_LOGIN` et `DOLI_ADMIN_PASSWORD` définissent le compte administrateur initial. Le chargement des données de démonstration est désactivé.

## Intégration continue

Le workflow [Validate application](.github/workflows/validate.yml) s'exécute sur chaque push vers `main`, sur les Pull Requests et manuellement. Il vérifie la configuration Compose, construit l'image Symfony, contrôle la syntaxe PHP, teste le tableau de bord Symfony en HTTP, puis démarre MariaDB et Dolibarr pour contrôler la réponse HTTP d'une instance neuve.

## Persistance

Les données sont conservées dans des volumes Docker :

- `dolibarr_database` : base de données neuve Dolibarr ;
- `dolibarr_documents` : documents et fichiers déposés ;
- `dolibarr_custom` : modules personnalisés ;
- `symfony_database` : base de développement Symfony ;
- `symfony_var` : cache et fichiers temporaires Symfony.

Arrêter les conteneurs sans supprimer les données :

```bash
docker compose --env-file .env.docker down
```

**Attention :** supprimer les volumes détruit les bases et les documents :

```bash
docker compose --env-file .env.docker down -v
```

## Installation locale Symfony sans Docker

```bash
composer install
cp .env .env.local
# Configurer DATABASE_URL et APP_SECRET dans .env.local
php bin/console cache:clear
symfony server:start
```

## Feuille de route

1. Démarrer et valider l'installation neuve Dolibarr 24.0.2.
2. Vérifier les modules natifs, les permissions, les documents et les tâches planifiées.
3. Faire évoluer progressivement l'interface et les services métier dans Symfony, module par module.
4. Organiser ultérieurement la reprise des données des anciennes versions, après sauvegardes et tests dédiés.

## Licence

Dolibarr est distribué sous **GNU GPL-3.0-or-later**. Le sous-module officiel conserve ses notices. Consulter `dolibarr/COPYRIGHT` et `dolibarr/COPYING` pour les licences des composants embarqués.
