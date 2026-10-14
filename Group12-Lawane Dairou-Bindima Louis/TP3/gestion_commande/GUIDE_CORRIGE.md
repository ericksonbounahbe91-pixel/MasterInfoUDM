# Corrigé du TP — Gestion d’une ligne de commande avec Flutter

## 1. Compréhension du sujet

Le sujet demande une application autour des tables Client, Commande, ligneCommande, Produit, Region et Département. Les fonctionnalités exigées sont : formulaire d’ajout de produits à une commande, affichage des produits/commandes/clients, modification d’un produit, suppression d’un produit dans une commande et tableau de bord statistique. Le compte donné est `MasTMound2` / `MoundMasT2@2026`.

## 2. Pourquoi Flutter + API PHP + MySQL ?

Flutter sert à construire l’interface mobile. MySQL stocke les données et tourne dans XAMPP. Une API PHP sert d’intermédiaire : elle reçoit les requêtes HTTP de Flutter, valide les données et exécute des requêtes SQL préparées. Il ne faut pas mettre les identifiants MySQL dans l’application mobile.

## 3. Mise en place de la base

1. Ouvrir XAMPP et démarrer Apache et MySQL.
2. Ouvrir phpMyAdmin.
3. Importer `database/gestion_commande.sql`.
4. Vérifier que la base `gestion_commande` et les six tables sont présentes.

Le fichier SQL ajoute quelques données de test pour visualiser les écrans immédiatement.

## 4. Installation de l’API

1. Copier le dossier `api_php` vers `C:\\xampp\\htdocs\\gestion_commande_api`.
2. Dans `config.php`, vérifier `DB_HOST`, `DB_NAME`, `DB_USER` et `DB_PASS`.
3. Tester dans le navigateur : `http://localhost/gestion_commande_api/produits.php`.
4. La réponse attendue est un JSON avec `success: true` et une liste dans `data`.

## 5. Installation de Flutter

1. Créer un projet avec `flutter create gestion_commande_flutter` ou utiliser le dossier `flutter_app`.
2. Remplacer/ajouter les fichiers fournis dans `flutter_app`.
3. Ajouter la permission Internet dans `android/app/src/main/AndroidManifest.xml`.
4. Pour l’émulateur Android, conserver `http://10.0.2.2/gestion_commande_api` comme adresse API.
5. Dans un téléphone réel, remplacer `10.0.2.2` par l’adresse IPv4 locale du PC.
6. Exécuter :
   - `flutter pub get`
   - `flutter run`

## 6. Explication des fonctionnalités

### Connexion
`LoginPage` recueille le login et le mot de passe puis appelle `api.login()`. Si l’API confirme le compte, l’utilisateur arrive sur le tableau de bord. Le compte est codé dans l’API uniquement parce que le sujet fournit un compte mais aucune table Utilisateur.

### Produits
`ProduitsPage` charge le catalogue via `produits.php`. Le bouton Ajouter ouvre un formulaire. Le même formulaire sert à modifier un produit. `produit_save.php` utilise une requête préparée SQL (`INSERT` ou `UPDATE`).

### Clients
`ClientsPage` affiche les clients avec leur région. La jointure SQL relie `Client` à `Region`.

### Commandes et lignes
`CommandesPage` affiche les commandes, le client associé, la date et le montant calculé. Le bouton d’ajout ouvre une fenêtre pour choisir un produit et saisir la quantité. L’API vérifie le stock puis insère ou met à jour la ligne dans `ligneCommande`.

### Suppression d’un produit dans une commande
L’écran de détail d’une commande (`CommandeDetailPage`) liste les lignes via `lignes.php` et propose un bouton de suppression avec confirmation, qui appelle `api.deleteLigne(idCmd, idProd)` puis `ligne_delete.php`.

### Tableau de bord
`stats.php` calcule :
- nombre total de commandes ;
- nombre de clients ;
- nombre de produits ;
- quantité totale vendue ;
- chiffre d’affaires estimé ;
- cinq produits les plus vendus.

Le chiffre d’affaires est calculé par `SUM(qteCmd × prixUProd)`. Cette mesure suppose que le prix actuel du produit est représentatif du prix lors de la vente. Pour une application de production, ajouter un champ `prixVente` à `ligneCommande` afin de conserver le prix historique.

## 7. Analyse de performance et justification

- Index : les clés primaires et étrangères facilitent les jointures. La clé primaire composée `(idCmd, idProd)` empêche de dupliquer deux lignes pour le même produit dans la même commande.
- Requêtes préparées : réduisent les risques d’injection SQL sur les données reçues.
- Transaction sur l’ajout d’une ligne : vérification du stock et écriture sont exécutées dans une transaction ; le verrou `FOR UPDATE` limite les conflits d’achat concurrents.
- Agrégation côté SQL : les statistiques sont calculées par MySQL plutôt que de transférer toutes les lignes vers Flutter.
- Évolutions : pagination des listes, authentification avec mots de passe hachés et jetons, HTTPS, journalisation, validation stricte, gestion d’images, tests unitaires et mesures avec Flutter DevTools.

## 8. Tests de validation

1. Connexion avec le compte fourni ; tester un mauvais mot de passe.
2. Afficher le catalogue des produits.
3. Ajouter un produit avec désignation, stock et prix valides.
4. Modifier le prix ou le stock d’un produit existant.
5. Ouvrir les commandes et ajouter un produit avec quantité positive.
6. Tenter d’ajouter une quantité supérieure au stock : l’API doit refuser.
7. Vérifier que les totaux du tableau de bord évoluent.
8. Tester `ligne_delete.php` avec un couple `idCmd`/`idProd` existant.

## 9. Limites à signaler dans le rapport

- Le TP ne décrit pas de table Utilisateur : la connexion utilise donc le compte de démonstration dans l’API.
- Les photos sont prévues dans le schéma mais le formulaire n’implémente pas encore l’envoi de fichiers.
- Le projet est une base pédagogique locale, pas un système prêt à être exposé sur Internet.
