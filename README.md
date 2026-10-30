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

**Sécurité Symfony :** les routes applicatives exigent désormais une session authentifiée (`ROLE_USER`). La connexion utilise le hachage de mots de passe Symfony, un jeton CSRF et une entité utilisateur distincte des comptes Dolibarr. La création du premier administrateur se fait exclusivement en ligne de commande ; aucune inscription publique n'est exposée. Le port 8000 reste réservé au développement local : une mise en production nécessite encore HTTPS, gestion des secrets, politique de sauvegarde, limitation des tentatives de connexion et revue de sécurité.

L'image officielle Dolibarr est configurée pour initialiser automatiquement une nouvelle instance au premier démarrage. Les paramètres `DOLI_ADMIN_LOGIN` et `DOLI_ADMIN_PASSWORD` définissent le compte administrateur initial. Le chargement des données de démonstration est désactivé.

## Intégration continue

Le workflow [Validate application](.github/workflows/validate.yml) s'exécute sur chaque push de branche, sur les Pull Requests vers `main` et manuellement. Il vérifie la configuration Compose, construit l'image Symfony, contrôle la syntaxe PHP et les invariants métier, applique les migrations, crée un administrateur temporaire, vérifie la connexion avec session et CSRF ainsi que la protection des routes, puis démarre MariaDB et Dolibarr pour contrôler la réponse HTTP d'une instance neuve.

## Premier modèle métier Symfony

Le modèle initial des tiers (clients, prospects et fournisseurs) et sa migration sont en place. La migration concerne uniquement la base Symfony ; elle n’importe ni ne modifie les données Dolibarr.

Après le démarrage Docker, initialisez le schéma Symfony avec :

```bash
docker compose --env-file .env.docker exec symfony php bin/console doctrine:migrations:migrate --no-interaction
```

Créez ensuite le premier administrateur. Le mot de passe est transmis uniquement à l'environnement du processus de commande, jamais comme argument visible dans l'historique shell :

```bash
read -r -p "E-mail administrateur : " SYMFOERP_ADMIN_EMAIL
read -r -s -p "Mot de passe (12 caractères minimum) : " SYMFOERP_ADMIN_PASSWORD
echo
docker compose --env-file .env.docker exec \\
  -e SYMFOERP_ADMIN_EMAIL="$SYMFOERP_ADMIN_EMAIL" \\
  -e SYMFOERP_ADMIN_PASSWORD="$SYMFOERP_ADMIN_PASSWORD" \\
  symfony php bin/console app:user:create
unset SYMFOERP_ADMIN_EMAIL SYMFOERP_ADMIN_PASSWORD
```

Connectez-vous ensuite sur http://localhost:8000/login. Les comptes et rôles Symfony sont stockés dans `app_user`, dans la base Symfony ; ils ne sont pas synchronisés avec les utilisateurs Dolibarr.

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
