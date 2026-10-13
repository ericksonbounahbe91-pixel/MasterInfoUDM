# TP2_Groupe2_Master_DI
# TP Examen : Gestion des commandes

Application web de gestion des **produits**, des **clients** et des **commandes**, avec un **tableau de bord** statistique.
Réalisée avec **Laravel 12**, **PHP 8.2+** et **MySQL**.

## Équipe

| Membre | Compte GitHub |
|---|---|
| CHAHAD Banane Moustapha | *@bananefils00* |
| ALI Adoum Abakar | *Ali-Adoum-Abakar* |

## Encadrement

- **Enseignant :** *Prof : Arouna NDAM NJOYA*

## Fonctionnalités

| Module | Actions disponibles |
|---|---|
| **Produits** | Liste, ajout, modification |
| **Clients** | Liste, ajout, modification (avec région et département) |
| **Commandes** | Liste, création, modification, détail, suppression |
| **Lignes de commande** | Ajout d'un produit à une commande, modification de la quantité, suppression d'un produit de la commande |
| **Tableau de bord** | Chiffre d'affaires total, chiffre d'affaires par mois, produits les plus vendus, chiffre d'affaires par département, meilleurs clients |

## Prérequis

- PHP **8.2 ou plus**, avec les extensions `mbstring`, `openssl`, `pdo_mysql`, `fileinfo`, `zip`
- [Composer](https://getcomposer.org)
- MySQL / MariaDB (WAMP ou XAMPP)
- [Git](https://git-scm.com)

## Installation

```bash
# 1. Récupérer le projet
git clone https://github.com/bananefils00/TP_G3_Master_DI.git
cd TP_G3_Master_DI

# 2. Installer les dépendances PHP
composer install

# 3. Créer le fichier de configuration et la clé de l'application
copy .env.example .env        # sous Linux/macOS : cp .env.example .env
php artisan key:generate
```

### Base de données

1. Démarrez Apache et MySQL (WAMP/XAMPP).
2. Dans phpMyAdmin (`http://localhost/phpmyadmin`), créez une base vide nommée **`ligne_commandes`**.
3. Ouvrez votre fichier **`.env`** (il reste sur votre ordinateur, il n'est jamais envoyé sur GitHub) et renseignez :

   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ligne_commandes
   DB_USERNAME=<votre utilisateur MySQL>
   DB_PASSWORD=<votre mot de passe>
   SESSION_DRIVER=file
   CACHE_STORE=file
   ```

   Par défaut, WAMP et XAMPP utilisent `root` sans mot de passe. Le nom d'utilisateur et le mot de passe de l'énoncé du TP sont à saisir ici, et uniquement ici.

### Créer les tables et lancer l'application

```bash
php artisan config:clear
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Ouvrez ensuite **http://127.0.0.1:8000**.

> Si MySQL refuse la création des index (erreur « clé trop longue »), ouvrez `config/database.php` et remplacez `'engine' => null` par `'engine' => 'InnoDB'` dans la section `mysql`, puis relancez `php artisan migrate:fresh --seed`.

## Structure de la base de données

```
departement(idDep, nomDep)
region(idRegion, nomRegion, #idDep)
client(idCli, ..., #idRegion)
produit(idProd, designProd, qteStock, prixUProd, ...)
commande(idCmd, dateCmd, #idCli)
ligne de commande(#idCmd, #idProd, qteCmd)
```

Relations : un département contient plusieurs régions ; une région regroupe plusieurs clients ; un client passe plusieurs commandes ; une commande contient plusieurs produits (relation plusieurs-à-plusieurs, avec la quantité commandée).

## Architecture

```
app/Http/Controllers/   ProduitController, ClientController, CommandeController, DashboardController
app/Models/             Client, Commande, Produit, Region, Departement
database/migrations/    création des tables et des index
database/seeders/       jeu de données de démonstration
resources/views/        pages Blade (layout, produits, clients, commandes, dashboard)
routes/web.php          routes de l'application
```

## Choix techniques et performance

- **Agrégations en SQL** (`SUM`, `GROUP BY`, jointures) pour le tableau de bord : la base calcule les statistiques au lieu de charger toutes les lignes en PHP.
- **Index** sur la date de commande (`dateCmd`) pour accélérer le regroupement par mois, et clés étrangères entre les tables.
- **Transactions SQL** (`DB::transaction`) pour les opérations sur les lignes de commande (ajout, modification, retrait, suppression) : les modifications sont enregistrées en entier ou pas du tout.
- **Validation** des formulaires côté serveur et protection **CSRF** sur chaque formulaire.

## Travail en équipe

1. Mettre à jour la branche principale avant de commencer : `git checkout main` puis `git pull`.
2. Créer une branche par tâche : `git checkout -b feature/nom-de-la-tache`.
3. Enregistrer et envoyer : `git add .`, `git commit -m "Message clair"`, `git push -u origin feature/nom-de-la-tache`.
4. Ouvrir une **Pull Request** sur GitHub, la faire relire par un autre membre, puis fusionner dans `main`.
5. Ne **jamais** envoyer le fichier `.env` ni de mot de passe sur le dépôt.
6. Après un `git pull`, lancer `composer install` si `composer.json` a changé, et `php artisan migrate` si de nouvelles migrations ont été ajoutées. Ne modifiez pas une migration déjà envoyée : créez-en une nouvelle.

## Dépannage

| Problème | Solution |
|---|---|
| `Could not open input file: artisan` | Se placer dans le dossier du projet (celui qui contient `artisan`) |
| `Access denied for user` | Corriger `DB_USERNAME` / `DB_PASSWORD` dans `.env`, puis `php artisan config:clear` |
| `Unknown database` | Créer la base `ligne_commandes` dans phpMyAdmin |
| `Table ... doesn't exist` | Lancer `php artisan migrate --seed` |
| `No application encryption key` | Lancer `php artisan key:generate` |
| Page sans style | Vérifier la connexion Internet si les bibliothèques sont chargées par CDN |
