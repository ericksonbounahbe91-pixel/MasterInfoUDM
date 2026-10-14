# Corrigé Flutter — Gestion d’une ligne de commande

Ce projet propose une solution pédagogique au TP « Gestion d’une ligne de commande » :
- authentification par identifiant/mot de passe ;
- consultation des clients, commandes et produits ;
- ajout d’un produit à une commande (ligneCommande) ;
- modification d’un produit ;
- suppression d’un produit d’une commande ;
- tableau de bord avec statistiques simples.

## Architecture

Flutter ne se connecte pas directement à MySQL. L’application Flutter appelle une API HTTP en PHP, hébergée localement avec XAMPP. L’API PHP utilise PDO pour communiquer avec MySQL.

- `flutter_app/` : application Flutter.
- `api_php/` : API REST pédagogique en PHP.
- `database/gestion_commande.sql` : structure et données de démonstration.

## Prérequis

1. Installer Flutter et Android Studio (ou VS Code).
2. Installer XAMPP et démarrer Apache + MySQL.
3. Importer `database/gestion_commande.sql` dans phpMyAdmin (⚠ le script supprime puis recrée la base : 16 clients, 20 produits, 16 commandes de démonstration).
4. Copier `api_php` dans `C:\xampp\htdocs\gestion_commande_api`.
5. Vérifier les paramètres MySQL dans `api_php/config.php`.
6. Dans `flutter_app/lib/api_service.dart`, l'adresse est choisie automatiquement (navigateur : `localhost`, mobile : `10.0.2.2`). Pour un téléphone réel, remplacez `_mobileHost` par l'IP du PC :
   - Android Emulator : `http://10.0.2.2/gestion_commande_api`
   - téléphone physique : `http://ADRESSE_IP_DU_PC/gestion_commande_api` (PC et téléphone sur le même Wi-Fi)
   - navigateur sur le même PC : `http://localhost/gestion_commande_api`
7. Depuis `flutter_app/`, exécuter `flutter pub get`, puis `flutter run`.

## Compte de démonstration

Login : `MasTMound2`  
Mot de passe : `MoundMasT2@2026`

Pour ce corrigé, l’authentification est une vérification de démonstration côté API. Pour un déploiement réel, stocker des mots de passe hachés (`password_hash`) dans une table utilisateur, utiliser HTTPS et ajouter des jetons de session.

## Endpoints

- `POST /login.php`
- `GET /clients.php`
- `GET /commandes.php`
- `GET /produits.php`
- `POST /produit_save.php` (ajout/modification)
- `POST /ligne_add.php`
- `POST /ligne_delete.php`
- `GET /lignes.php?idCmd=` (détail d'une commande)
- `POST /commande_add.php` (nouvelle commande pour un client)
- `GET /stats.php`

## Remarques pédagogiques

Le schéma du sujet indique les tables Client, Commande, ligneCommande, Produit, Region et Département. La table `Utilisateur` n’étant pas indiquée dans le sujet, le compte fourni est configuré dans `config.php` uniquement pour la démonstration.

## Nouveautés de cette version

- Interface refaite : connexion, menu latéral (écran large) ou barre de navigation (mobile), cartes, recherche, étiquettes de stock.
- Tableau de bord : chiffre d'affaires, histogramme mensuel, top produits, meilleurs clients, produits à réapprovisionner.
- Détail d'une commande avec **suppression d'un produit** et ajout (quantité + sous-total), création d'une nouvelle commande.
- Données de démonstration enrichies (clients, produits, commandes réparties sur 6 mois).
