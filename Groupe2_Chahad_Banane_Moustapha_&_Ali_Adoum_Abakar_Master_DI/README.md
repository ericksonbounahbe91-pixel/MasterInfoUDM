# Travaux pratiques — Master 1 Données & Intelligence (DI/IRS)

**Université de Moundou** · Faculté des sciences exactes et appliquées · Département des Sciences Technologiques · Année universitaire 2025-2026

| | |
|---|---|
| **Groupe** | Groupe 2 |
| **Étudiants** | CHAHAD Banane Moustapha · ALI Adoum Abakar |
| **Encadrant** | Prof. Arouna NDAM NJOYA |
| **Date** | Octobre 2026 |

Ce dossier regroupe les trois travaux pratiques du groupe : deux applications web (PHP, puis Laravel) et la conversion de la seconde en application mobile Android autonome (Flutter). Chaque TP a son propre dossier, son code et son rapport PDF.

---

## 1. Vue d'ensemble

| TP | Thème | Technologies | Base de données | Plateforme |
|---|---|---|---|---|
| **TP1** | Application web de gestion des étudiants, avec cartes étudiantes vérifiables par QR code | PHP 8 procédural, PDO, Bootstrap 5, Dompdf, Endroid QR Code | MySQL / MariaDB (`universite`) | Navigateur, XAMPP |
| **TP2** | Application web de gestion d'une ligne de commande (produits, clients, commandes, tableau de bord) | PHP 8.2, Laravel 12, Bootstrap 5.3, Chart.js 4 | MySQL (`gestion_commandes`) | Navigateur, XAMPP |
| **TP3** | Conversion de l'application du TP2 en application mobile, sans serveur | Flutter (Dart), sqflite, provider, go_router, fl_chart | SQLite locale sur le téléphone | Android |

Fil conducteur : le **TP1** pose les bases du développement web côté serveur (CRUD, sécurité, génération de documents). Le **TP2** passe à un framework MVC (Laravel) autour d'un schéma relationnel plus riche. Le **TP3** reprend le TP2 et le réécrit entièrement en Flutter, avec une base SQLite locale.

---

## 2. Organisation du dossier

> Les noms de dossiers ci-dessous sont une proposition : adaptez-les à votre arborescence réelle (le nom du code source de chaque TP est indiqué entre parenthèses).

```
TPs_Master1_DI/
├── README.md                          ce fichier
├── TP1_Gestion_Etudiants/             code du dossier « formulaire »
│   └── TP1_Groupe2_..._Master_DI.pdf  rapport du TP1
├── TP2_Gestion_Commandes_Web/         code du dossier « tp_exam_commande »
│   └── TP2_Groupe2_..._Master_DI.pdf  rapport du TP2
└── TP3_Gestion_Commandes_Mobile/      code du dossier « gestion_commande »
    └── TP3_Groupe2_..._Master_DI.pdf  rapport du TP3
```

---

## 3. TP1 — Gestion des étudiants (PHP, MySQL)

**Objectif.** Offrir à la scolarité un outil web pour tenir à jour la liste des étudiants et produire des cartes étudiantes dont l'authenticité peut être vérifiée par un tiers, sans exposer la base aux attaques courantes (injection SQL, XSS, CSRF).

**Fonctionnalités**
- CRUD complet sur les étudiants, avec validation côté serveur (e-mail, téléphone, date de naissance réelle et plausible, matricule immuable).
- Liste avec statistiques de synthèse, recherche instantanée (JSON, anti-rebond de 250 ms), filtres (mention, cycle, département), tri et pagination (10 par page).
- Cartes étudiantes au format CR80 (85,6 × 54 mm) : aperçu, téléchargement PDF ou PNG, génération par lot (archive ZIP), journal des cartes générées.
- Vérification publique d'une carte par **QR code signé HMAC-SHA256**, sans exposer d'e-mail ni de téléphone.
- Interface Bootstrap 5 responsive, mode sombre, accessibilité (lien d'évitement, libellés ARIA).

**Sécurité** : requêtes préparées natives, jeton CSRF, échappement systématique des sorties (`h()`), en-têtes de sécurité et CSP, suppression en POST uniquement avec transaction, fichiers générés protégés par `.htaccess`.

**Base de données** : `universite`, avec la table `etudiant` (créée lors d'un TP précédent) et la table `carte_etudiant` (ajoutée par `schema_migration.sql`).

**Prérequis** : XAMPP (Apache, PHP 8.0 ou plus, MySQL/MariaDB), Composer, extensions PHP `pdo_mysql`, `mbstring`, `zip` (`imagick` facultative pour le PNG).

**Installation et lancement**
1. Copier le dossier du projet dans `C:\xampp\htdocs\tp5`.
2. Dans un terminal : `cd C:\xampp\htdocs\tp5` puis `composer install`.
3. Démarrer Apache et MySQL depuis le panneau XAMPP.
4. Dans phpMyAdmin, ouvrir la base `universite`, onglet **SQL**, coller le contenu de `schema_migration.sql` et exécuter.
5. Vérifier que le dossier `cartes/` est accessible en écriture.
6. Ouvrir http://localhost/tp5/.

**Points à connaître** (détaillés dans la section 11 du rapport) : pas d'authentification ; la clé HMAC est écrite dans `config.php` et MySQL est utilisé avec `root` sans mot de passe (à remplacer avant tout usage réel) ; le style du gabarit de carte, le PDF de lot et l'échappement de la recherche instantanée figurent parmi les corrections identifiées ; aucun test automatisé (plan de 18 scénarios manuels).

📄 Rapport : `TP1_Groupe2_…pdf`

---

## 4. TP2 — Gestion d'une ligne de commande (Laravel)

**Objectif.** Réaliser une application web de gestion des clients, des produits et des commandes, à partir de six tables imposées par le sujet (Client, Commande, ligneCommande, Produit, Region, Département), avec un tableau de bord statistique et une analyse de performance.

**Fonctionnalités**
- Liste des produits, des clients et des commandes (paginées par 10) ; ajout et modification des clients et des produits ; création, modification et suppression des commandes.
- Page d'une commande : lignes, total, formulaire d'ajout d'un produit (liste limitée aux produits en stock et absents de la commande), modification de la quantité, retrait d'un produit.
- **Gestion du stock par transactions** : l'ajout diminue le stock (refusé s'il dépasse le stock disponible), la modification ajuste la différence, le retrait ou la suppression d'une commande remet en stock, le tout « tout ou rien » avec verrou de ligne (`lockForUpdate`).
- Département et région saisis librement dans le formulaire du client et créés au besoin (`firstOrCreate`, insensible à la casse).
- **Tableau de bord** : nombre de commandes, chiffre d'affaires, panier moyen, produits à stock faible (moins de 20), graphiques Chart.js (CA par mois, par département, top 5 des produits), meilleurs clients, mesure du nombre de requêtes SQL et de la durée de calcul.
- Montants en FCFA ; interface Bootstrap avec menu latéral adaptatif.
- Connexion et inscription : le sujet ne les exigeait pas, mais la version finale les propose (voir les captures du rapport et l'analyse faite au TP3 : `AuthController`, 5 tentatives de connexion par minute).

**Modèle de données** : `departement` → `region` → `client` → `commande` → `ligneCommande` ← `produit` (clé primaire composée sur `ligneCommande`), plus la table `users`. Actions de suppression : `CASCADE` des lignes avec leur commande, `RESTRICT` partout ailleurs.

**Prérequis** : XAMPP (ou WAMP), PHP 8.2 ou plus, Composer, MySQL.

**Installation et lancement**
1. Copier le projet (par exemple dans `C:\xampp\htdocs\DevWeb\TP1_exam\tp_exam_commande`).
2. Créer la base et un compte MySQL dédié dans phpMyAdmin (onglet **SQL**) :
   ```sql
   CREATE DATABASE gestion_commandes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'MasTMound2'@'localhost' IDENTIFIED BY '<mot_de_passe>';
   GRANT ALL PRIVILEGES ON gestion_commandes.* TO 'MasTMound2'@'localhost';
   FLUSH PRIVILEGES;
   ```
3. Renseigner la connexion dans le fichier `.env` du projet (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
4. Dans un terminal, dans le dossier du projet :
   ```
   composer install
   php artisan config:clear
   php artisan migrate
   php artisan serve
   ```
5. Ouvrir http://127.0.0.1:8000 (ou l'adresse du dossier `public` sous XAMPP).

**Points à connaître** : les données se saisissent depuis l'application (pas de seeders) ; les clients et produits ne peuvent pas être supprimés depuis l'interface ; téléversement des photos non implémenté ; pas de tests automatisés (9 scénarios manuels dans le rapport) ; piste d'optimisation : index sur `commande.dateCmd` et mise en cache des statistiques.

📄 Rapport : `TP2_Groupe2_…pdf`

---

## 5. TP3 — Conversion en application mobile Android (Flutter)

**Objectif.** Convertir l'application web du TP2 en **application Android autonome, sans serveur PHP ni base MySQL** : les données sont enregistrées sur le téléphone et l'application fonctionne **sans connexion Internet**. L'interface reprend l'identité visuelle Bootstrap (couleurs, tableaux, boutons, menu latéral).

**Fonctionnalités** : identiques à celles du TP2 : connexion et inscription (« Se souvenir de moi »), produits, clients, commandes, lignes de commande, tableau de bord (CA total, CA par mois, top produits, CA par département, meilleurs clients, stock faible).

**Architecture en cinq couches** : présentation (`views/`, `widgets/`, thème) → navigation (`go_router`) → état (`provider`) → logique métier et accès aux données (`repositories/`, `models/`) → persistance (SQLite via `sqflite`, `flutter_secure_storage`). Chaque brique Laravel a un équivalent Flutter :

| Laravel | Flutter |
|---|---|
| Migrations, seeders | `AppDatabase._createSchema`, `_seed` |
| Modèles Eloquent, contrôleurs | `models/`, `repositories/` (règles de stock) |
| `DB::transaction` | `db.transaction` (sqflite) |
| Vues Blade, Bootstrap | `views/`, `widgets/`, `core/theme.dart` |
| Middleware `auth` / `guest` | `redirect` de go_router |
| Session | `flutter_secure_storage` |
| Chart.js | `fl_chart` |

**Points techniques** : base SQLite `ligne_commandes.db` avec clés étrangères activées ; règles de stock identiques au web, dans des transactions ; mot de passe haché en SHA-256 avec sel aléatoire ; bouton retour Android géré ; interface adaptative (menu latéral fixe à partir de 992 dp, tiroir en dessous) ; montants au format « 12 500 FCFA ».

**Tests** : 7 tests unitaires des règles de stock (`test/commande_repository_test.dart`, base SQLite en mémoire avec `sqflite_common_ffi`), analyse statique `flutter analyze` et recette manuelle de 8 scénarios.

**Prérequis** : Flutter SDK, Android Studio (SDK Android), un émulateur ou un téléphone Android en débogage USB. Version minimale d'Android : API 23 (`minSdk = 23`, exigé par `flutter_secure_storage`).

**Installation et lancement**
```
cd gestion_commande
flutter pub get
flutter analyze
flutter test
flutter run
```

**Générer l'APK**
```
flutter build apk --release
# -> build/app/outputs/flutter-apk/app-release.apk
```
(`--split-per-abi` produit des APK plus légers, un par architecture ; `flutter build appbundle --release` prépare la publication sur Google Play, avec une clé de signature conservée hors du projet.)

**Points à connaître** : les données sont propres à chaque téléphone et perdues si l'application est désinstallée ; `onCreate` ne s'exécute qu'à la création de la base, donc après un changement de schéma ou des données de démonstration il faut désinstaller l'application ; le hachage SHA-256 salé convient à un usage pédagogique local, pas à la production ; listes non paginées ; la limitation des tentatives de connexion du web n'est pas reproduite.

📄 Rapport : `TP3_Groupe2_…pdf`

---

## 6. Prérequis généraux

| Outil | Utilisé par | Remarque |
|---|---|---|
| XAMPP (Apache, PHP 8.x, MySQL/MariaDB, phpMyAdmin) | TP1, TP2 | Démarrer Apache et MySQL avant de lancer les applications |
| Composer | TP1, TP2 | `composer install` dans chaque projet |
| Visual Studio Code | TP1, TP2 | Éditeur utilisé |
| Flutter SDK et Android Studio | TP3 | Vérifier l'installation avec `flutter doctor` |
| Git | Tous | Gestion de versions |

---

## 7. Dépannage rapide

| Problème | Solution |
|---|---|
| Page « Index of » au lieu de l'application (TP2) | Ouvrir l'adresse du dossier `public` |
| Pages vides, indicateurs à 0 (TP2) | La base ne contient pas encore de données : les saisir depuis l'application |
| `Access denied` pour l'utilisateur MySQL | Vérifier `DB_USERNAME` et `DB_PASSWORD` dans `.env`, puis `php artisan config:clear` |
| Erreur « La clé est trop longue » pendant `migrate` | Forcer le moteur InnoDB dans `config/database.php` (`'engine' => 'InnoDB'`) |
| Cartes PDF ou ZIP non générées (TP1) | Vérifier `composer install`, l'extension `zip` et les droits d'écriture sur `cartes/` |
| `flutter` n'est pas reconnu | Ajouter `flutter\bin` au PATH, puis rouvrir le terminal |
| La base Flutter ne reflète pas mes changements de schéma | Désinstaller l'application du téléphone ou de l'émulateur, puis relancer |

---

## 8. Ce qu'il ne faut pas publier sur GitHub

- les mots de passe et le fichier `.env` (TP2) ;
- la clé secrète HMAC du TP1 (`CLE_SECRETE` dans `config.php`) : la déplacer hors du code avant publication ;
- les dossiers générés : `vendor/`, `build/`, `.dart_tool/`, `cartes/` (fichiers générés du TP1) ;
- les clés de signature Android (`key.properties`, fichiers `.jks`).

---

## 9. Auteurs

**Groupe 2 — Master 1 DI/IRS, Université de Moundou**
- ALI Adoum Abakar
- CHAHAD Banane Moustapha

Encadrant : Prof. Arouna NDAM NJOYA — Octobre 2026
