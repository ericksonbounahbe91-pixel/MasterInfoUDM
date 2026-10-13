# Gestion des étudiants — TP5 refactorisé + cartes étudiantes

Application PHP **procédurale** (PDO + Bootstrap 5) de gestion d'étudiants,
avec module complet de génération de cartes étudiantes au format CR80
(85,6 × 54 mm) en PDF et PNG.

## Équipe

| Membre | Compte GitHub |
|---|---|
| CHAHAD Banane Moustapha | *@bananefils00* |
| ALI Adoum Abakar | *(Ali-Adoum-Abakar)* |

## Encadrement

- **Enseignant :** *Prof : Arouna NDAM NJOYA*

## Prérequis

- XAMPP (Apache + PHP 8.0 ou supérieur + MySQL/MariaDB)
- Composer (pour Dompdf et Endroid QR Code)
- Extensions PHP : `pdo_mysql`, `mbstring`, `zip` (lot), `imagick` (PNG, facultatif)

## Installation

```bash
# 1. Placer le dossier dans htdocs
cd C:\xampp\htdocs
# le dossier s'appelle tp5

# 2. Installer les dépendances
cd C:\xampp\htdocs\tp5
composer install

# 3. Créer la table de journalisation des cartes
#    phpMyAdmin > base « universite » > onglet SQL > coller schema_migration.sql

# 4. Ouvrir l'application
#    http://localhost/tp5/
```

## Droits d'écriture

Le dossier `cartes/` doit être accessible en écriture par Apache.
Il est protégé par `.htaccess` (`Require all denied`) : les fichiers ne sont
téléchargeables que via `carte.php` et `cartes_batch.php`.

## Export PNG

1. **wkhtmltoimage** (recommandé) : ajouter `wkhtmltopdf\bin` au `PATH`.
2. **Imagick** : activer `extension=imagick` dans `php.ini` + installer Ghostscript.
3. À défaut, l'export PDF reste disponible.

## Sécurité

- Requêtes préparées PDO natives (`ATTR_EMULATE_PREPARES = false`)
- Jeton CSRF sur toutes les écritures (`verifier_csrf()`)
- Échappement HTML systématique via `h()`
- Content-Security-Policy stricte (CDN Bootstrap autorisé)
- QR codes signés en HMAC-SHA256 (`token_carte()` / `token_valide()`)
- Tri par liste blanche de colonnes, `LIMIT`/`OFFSET` liés en entier

## Arborescence

```
tp5/
├── .htaccess
├── config.php
├── functions.php
├── index.php
├── form.php
├── save.php
├── delete.php
├── carte.php
├── cartes_batch.php
├── verifier.php
├── composer.json
├── schema_migration.sql
├── api/search.php
├── includes/{header.php,footer.php}
├── templates/carte_template.php
├── assets/css/style.css
├── assets/js/app.js
├── assets/img/logo.png          (à fournir)
├── assets/img/photos/           (photos des étudiants, facultatif)
├── cartes/.htaccess
└── vendor/                      (créé par composer install)
```
