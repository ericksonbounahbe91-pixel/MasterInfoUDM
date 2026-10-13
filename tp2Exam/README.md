# TP Examen — Gestion d'une ligne de commande (Laravel)

## Installation
1. Créer le projet Laravel, puis copier les dossiers de cette archive dessus
   (`app/`, `database/`, `resources/`, `routes/`) en acceptant le remplacement de `routes/web.php` :

       composer create-project laravel/laravel tp-commande

2. Créer la base MySQL `tp_commande`, puis dans `.env` :

       DB_CONNECTION=mysql
       DB_HOST=127.0.0.1
       DB_PORT=3306
       DB_DATABASE=tp_commande
       DB_USERNAME=MasTMound2
       DB_PASSWORD=MoundMasT2@2026

3. Lancer :

       php artisan migrate
       php artisan storage:link
       php artisan db:seed --class=TpSeeder
       php artisan serve

4. Ouvrir http://127.0.0.1:8000

## Correspondance avec les consignes
1. Formulaire d'enregistrement  -> /commandes/create  (CommandeController@store, transaction + stock)
2. Listes                       -> /produits, /commandes, /clients
3. Modifier un produit          -> /produits/{id}/edit
4. Supprimer un produit d'une commande -> bouton « Supprimer » sur /commandes/{id}
5. Tableau de bord              -> /dashboard (KPI, 3 graphiques Chart.js, alertes stock, analyse de performance)
