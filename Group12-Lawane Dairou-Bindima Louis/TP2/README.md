# Gestion d'une ligne de commande — TP Examen Dev Web 2026

Application PHP 8.1+ / MySQL (ou MariaDB) sans framework : PDO, requêtes préparées, HTML/CSS/JS natifs.
Seul le graphique du tableau de bord charge Chart.js depuis un CDN (les tableaux restent lisibles hors ligne).

## Installation
1. Copier le dossier `gestion_commande` dans `htdocs` (XAMPP/WAMP) ou `/var/www/html`.
2. Importer, dans cet ordre : `mysql -u root -p < sql/schema.sql` puis `mysql -u root -p < sql/seed.sql`
   (ou via phpMyAdmin → Importer). Le schéma crée la base `gestion_commande` et l'utilisateur
   **MasTMound2** (mot de passe de l'énoncé) avec uniquement SELECT/INSERT/UPDATE/DELETE.
3. Rendre `uploads/` accessible en écriture au serveur web.
4. Ouvrir `http://localhost/gestion_commande/`. Les identifiants sont dans `config/config.php`.

Test rapide sans Apache : `php -S localhost:8000` dans le dossier du projet.

## Travail demandé → fichiers
| Demande | Fichier |
|---|---|
| Formulaire d'enregistrement des produits d'une commande donnée | `commande.php` (formulaire à lignes multiples) |
| Liste des produits / commandes / clients | `produits.php`, `commandes.php`, `clients.php` |
| Modifier un produit | `produit_form.php?id=…` |
| Supprimer un produit dans une commande | `commande.php` (bouton « Retirer ») |
| Tableau de bord + analyse de performance | `dashboard.php` |

## Choix de conception
- **Cohérence du stock** : l'ajout d'une ligne décrémente le stock, la suppression le remet. Tout se fait dans une
  transaction avec `SELECT … FOR UPDATE`, donc deux utilisateurs ne peuvent pas vendre le même dernier article.
  Un produit déjà présent dans la commande voit sa quantité augmenter (clé primaire `idCmd, idProd`).
- **Sécurité** : requêtes préparées (pas d'injection SQL), échappement systématique (`h()`), jeton CSRF sur tous les
  formulaires POST, suppression uniquement en POST avec confirmation, upload d'image contrôlé sur le contenu réel
  du fichier, scripts interdits dans `uploads/`, droits SQL minimaux.
- **Limite du modèle imposé** : `ligneCommande` ne stocke pas le prix au moment de la vente. Le chiffre d'affaires
  utilise donc le prix actuel du produit ; modifier un prix change l'historique. En production, on ajouterait
  `prixVente` dans `ligneCommande`.

## Tableau de bord : indicateurs retenus et pourquoi
| Indicateur | Question à laquelle il répond |
|---|---|
| CA, nombre de commandes, panier moyen, unités, clients actifs | Où en est l'activité ? Les clients achètent-ils plus ou plus souvent ? |
| CA par mois | Y a-t-il une tendance ou une saisonnalité ? |
| Top produits par CA (et non par quantité) | Un câble à 3 000 vendu 100 fois pèse moins qu'un ordinateur vendu 5 fois |
| CA par région et par département | Où concentrer la prospection et la logistique ? |
| Meilleurs clients | Qui fidéliser en priorité ? |
| Stocks bas + demande de la période | Quels produits risquent la rupture alors qu'ils se vendent ? |
Un filtre de période recalcule tous les indicateurs.

## Analyse de performance
Mesures intégrées : le panneau « Analyse de performance » du tableau de bord affiche le temps de chaque requête,
le temps total de la page et la mémoire. Les choix qui les rendent rapides :
- **Index** : `Commande(dateCmd)` pour le filtre de période (parcours de plage au lieu d'un balayage complet),
  `Commande(idCli)`, `ligneCommande(idProd)`, `Client(idRegion)`, `Region(idDep)` pour les jointures et clés étrangères,
  `Produit(qteStock)` pour les alertes. La clé primaire composite `(idCmd, idProd)` sert les jointures sur les commandes.
- **Agrégation côté SQL** : sommes et regroupements faits par MySQL, jamais en boucle PHP. Six requêtes pour tout le
  tableau de bord, aucune requête dans une boucle (pas de problème « N+1 »).
- **Vue `v_lignes`** : centralise la jointure Commande–Ligne–Produit et le calcul du montant, pour éviter de le dupliquer.
- **Pagination** de la liste des commandes (15 par page) : le temps de réponse ne croît pas avec le volume.
- **Vérifier soi-même** : `EXPLAIN SELECT … FROM v_lignes WHERE dateCmd BETWEEN '2026-01-01' AND '2026-03-31'`
  doit montrer `idx_commande_date` dans la colonne `key`.
- **Pistes si le volume explose** (millions de lignes) : table de synthèse mensuelle alimentée par un événement
  planifié, cache des résultats du tableau de bord quelques minutes, index couvrant `(dateCmd, idCli)`.

## Jeu de données
`sql/seed.sql` : 4 départements, 9 régions, 20 clients, 15 produits, 80 commandes, 207 lignes (octobre 2025 → octobre 2026).
Certains produits ont un stock bas ou nul pour illustrer les alertes.
