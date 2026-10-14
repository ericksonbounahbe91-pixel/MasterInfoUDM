# TP Examen — Gestion d'une ligne de commande (Laravel)

## Installation (dans votre projet Laravel existant, ex. C:\Users\HP\mon-projet)

1. Copiez les dossiers `app/`, `routes/`, `resources/` de cette archive dans le projet
   (acceptez de remplacer `routes/web.php`).
2. Mettez à jour le `.env` avec le contenu de `ENV_A_COPIER.txt`
   (adaptez `DB_DATABASE` au nom de la base fournie pour l'examen).
3. Importez la base : script de création des tables, puis (facultatif) `database/donnees_test.sql`.
4. Lancez :
   ```
   php artisan config:clear
   php artisan storage:link
   php artisan serve
   ```
5. Ouvrez http://127.0.0.1:8000 (aucun `npm` n'est nécessaire : Bootstrap et Chart.js sont chargés par CDN, une connexion Internet est donc requise).

## Fonctionnalités demandées

| Consigne | Où |
|---|---|
| Formulaire d'enregistrement des produits d'une commande | `/commandes/{id}` (formulaire « Ajouter un produit ») |
| Liste des produits / commandes / clients | `/produits`, `/commandes`, `/clients` |
| Modifier un produit | `/produits/{id}/edit` |
| Supprimer un produit dans une commande | bouton « Supprimer » sur `/commandes/{id}` |
| Tableau de bord + analyse de performance | `/dashboard` |

## Choix techniques à justifier à l'oral

- **Eloquent + relations** (`belongsTo`, `hasMany`, `belongsToMany`) : code lisible, pas de SQL répété.
  `ligneCommande`, de clé composée (idCmd, idProd), est gérée comme table pivot
  (`attach`, `updateExistingPivot`, `detach`).
- **Transactions + `lockForUpdate`** à l'ajout et à la suppression d'une ligne : le stock est
  décrémenté/rétabli de façon atomique, ce qui évite un stock négatif si deux commandes arrivent en même temps.
- **Validation côté serveur** (`validate`) : quantités > 0, stock suffisant, existence du produit/client,
  protection CSRF sur tous les formulaires.
- **Cumul des quantités** si le produit est déjà dans la commande (la clé primaire composée interdit les doublons).
- **Pas de N+1** : `with()` / `withCount()` pour les listes, totaux de commande calculés en une requête SQL (`SUM` + `GROUP BY`).
- **Pagination** (10 lignes) des listes.
- **Sessions/cache en `file`** : la base de l'examen n'a pas les tables système de Laravel.

## Tableau de bord (tâche ouverte)

Indicateurs choisis, avec leur utilité de gestion :

- CA total, panier moyen, nombre de clients/commandes/produits : vue d'ensemble.
- CA par mois : tendance et saisonnalité.
- CA par département (jointure Commande → Client → Region → Departement) : analyse géographique.
- Top 5 produits et top 5 clients : concentration des ventes.
- Valeur du stock et alertes de stock faible (≤ 5) : gestion des réapprovisionnements.

## Analyse de performance

Le tableau de bord mesure lui-même le temps de chaque requête (affiché en bas de page).

Méthode proposée :
1. Importer `donnees_test.sql`, ouvrir `/dashboard`, noter les temps (état de référence).
2. Exécuter `database/indexes.sql`, recharger plusieurs fois, comparer.
3. Utiliser `EXPLAIN` sur les requêtes lentes (type `ALL` = parcours complet de la table).
4. Pour mesurer un effet réel, générer beaucoup plus de lignes (par ex. 50 000 lignes de commande)
   : avec le jeu de test, tous les temps sont de l'ordre de la milliseconde et la différence est invisible.

Pistes d'amélioration : mettre le résultat du tableau de bord en cache (`Cache::remember`, 60 s),
ou pré-agréger les statistiques si les volumes deviennent importants.

Résultats mesurés : à compléter avec vos propres relevés.
