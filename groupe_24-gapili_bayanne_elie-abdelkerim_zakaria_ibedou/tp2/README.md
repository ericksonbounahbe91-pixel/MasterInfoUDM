# Gestion des commandes – Immobilier & Transport (PHP / MySQL)

## Version « outils numériques »
- Catalogue de 14 offres en 3 catégories : **Logiciels** (licences, cloud, application sur mesure), **Matériel** (PC, smartphone, routeur, SSD, caméra), **Services** (site web, maintenance, formation, hébergement).
- 10 nouveaux clients (startups, cybercafé, école, pharmacie, clinique…) et 16 commandes d'exemple.
- Nouveau thème violet/cyan sur fond dégradé ; tables en lignes-cartes avec en-tête collant.
- Base déjà installée : `sql/03_migration_numerique.sql`. Nouvelle installation : `01` puis `02`.

## Installation (XAMPP / WAMP / LAMP, PHP 7.1 ou plus)
1. Copier le dossier `gestion_commande` dans `htdocs` (XAMPP) ou `www` (WAMP).
2. Dans phpMyAdmin, importer `sql/01_schema_et_donnees.sql` (tables + données fictives de test).
   Si la base d'examen existe déjà, sautez cette étape.
3. Importer `sql/02_vue_et_index.sql` (vue `cmd_v_ligne_detail` + index). Aucune donnée n'est modifiée.
4. Vérifier `inc/config.php` : hôte, nom de la base (`tp_commande_2026` par défaut), identifiant et mot de passe fournis par le sujet.
5. Ouvrir `http://localhost/gestion_commande/`.

## Correspondance avec le sujet
| Demande | Page |
|---|---|
| Formulaire d'enregistrement des produits d'une commande donnée | `commande.php?id=N` (plusieurs lignes à la fois) |
| Liste des produits / commandes / clients | `produits.php`, `commandes.php`, `clients.php` |
| Modifier un produit | `produit.php?id=N` |
| Supprimer un produit dans une commande | bouton « Retirer » dans `commande.php` |
| Tableau de bord + analyse de performance | `index.php` |

## Choix techniques à défendre
- **PDO + requêtes préparées natives** : protège contre l'injection SQL.
- **Jeton CSRF** sur tous les formulaires ; **suppression par POST** (jamais par lien GET).
- **`htmlspecialchars` partout** (fonction `h()`) : protège contre le XSS.
- **Transactions InnoDB** : ajouter ou retirer un produit modifie la ligne ET le stock. Tout réussit ou tout est annulé.
- **`SELECT … FOR UPDATE`** : deux utilisateurs ne peuvent pas vendre en même temps le dernier stock. Les produits sont verrouillés dans un ordre constant (`ksort`) pour éviter les blocages.
- **Stock cohérent** : ajout = stock diminué ; suppression d'une ligne = stock rétabli ; stock insuffisant = tout est refusé avec un message clair.
- **Téléversement de photo** : type vérifié par le contenu du fichier (pas par l'extension), 2 Mo maximum, nom aléatoire.

## Tableau de bord : choix des indicateurs
- **Chiffre d'affaires, nombre de commandes, panier moyen, unités vendues, clients actifs** : l'état de santé des ventes en un coup d'œil.
- **CA par mois** : tendance et saisonnalité.
- **Top 5 produits + part du CA** : mesure la dépendance à quelques produits.
- **CA par département** (Client → Region → Departement) : exploite les tables géographiques du modèle.
- **Top 5 clients** : repère les clients à fidéliser.
- **Produits à réapprovisionner** (seuil dans `config.php`) : lien direct entre statistiques et action.
- **Filtre par période** : tous les calculs se font côté SQL, jamais en PHP sur des tableaux complets.
- Graphiques en SVG/CSS purs : aucune bibliothèque externe, donc fonctionne sans Internet (salle d'examen).

## Analyse de performance
- **Vue `cmd_v_ligne_detail`** : la jointure Commande–ligneCommande–Produit est écrite une seule fois ; les requêtes de statistiques restent courtes.
- **Index `idx_commande_date`** : le filtre par période n'a pas à parcourir toute la table `Commande`.
- **Index sur les clés étrangères** (`idProd`, `idCli`, `idRegion`, `idDep`) : jointures rapides.
- **Agrégation dans MySQL** (`SUM`, `COUNT`, `GROUP BY`) : seules quelques lignes résumées sont envoyées à PHP.
- **`LIMIT`** sur les classements.
- **Mesure intégrée** : le bas de chaque page affiche le temps de génération et le nombre de requêtes ; le tableau de bord détaille la durée de chaque requête et propose un `EXPLAIN` (colonne `key` = index utilisé).
- **Limites assumées** : le modèle ne stocke pas le prix au moment de la vente (le CA suit le prix actuel du produit) ; les listes ne sont pas paginées (suffisant pour un volume d'examen, à ajouter avec `LIMIT/OFFSET` sur de gros volumes).

## Interface
- Menu latéral fixe (barre du haut sur téléphone), icônes SVG intégrées, aucune bibliothèque externe.
- Palette : bleu profond (structure), bleu vif (actions), ambre (accent, 1er du classement), vert / rouge / orangé pour les états.
- Tout le style est dans `assets/style.css` (variables CSS en tête de fichier : on change toute la palette en modifiant quelques lignes).

## Noms de la base et des tables
- Base : `tp_commande_2026`.
- Tables : `cmd_departement`, `cmd_region`, `cmd_client`, `cmd_produit`, `cmd_commande`, `cmd_lignecommande` ; vue : `cmd_v_ligne_detail`.
- Les colonnes gardent les noms du sujet (`idCmd`, `qteCmd`, `prixUProd`, etc.).
