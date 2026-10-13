# Version mobile (Flutter) du TP Gestion des commandes

Le dossier `laravel/` ajoute une API JSON au site existant.
Le dossier `flutter/lib/` contient l'application mobile.

## A. Côté Laravel (API)
1. Dans D:\www\tp-laravel (PHP 8.3 actif) :

       php artisan install:api

   (répondre "yes" si on propose d'exécuter les migrations)
2. Copier le contenu de `laravel/` dans le projet (remplacer routes\api.php) :

       Copy-Item <chemin>\laravel\* D:\www\tp-laravel -Recurse -Force

3. Lancer le serveur, accessible depuis le téléphone :

       php artisan serve --host=0.0.0.0 --port=8000

4. Test : http://127.0.0.1:8000/api/produits doit afficher du JSON.

## B. Côté Flutter
1. Installer Flutter (docs.flutter.dev/get-started/install) puis `flutter doctor`.
2. Créer le projet et ajouter la dépendance :

       cd D:\www
       flutter create commandes_mobile
       cd commandes_mobile
       flutter pub add http

3. Copier `flutter/lib/` dans le projet (remplacer lib\main.dart).
4. Android : dans android\app\src\main\AndroidManifest.xml
   - avant <application> ajouter :  <uses-permission android:name="android.permission.INTERNET"/>
   - dans la balise <application ...> ajouter :  android:usesCleartextTraffic="true"
5. Régler `baseUrl` dans lib\config.dart (voir les commentaires du fichier).
6. Lancer : `flutter run`
