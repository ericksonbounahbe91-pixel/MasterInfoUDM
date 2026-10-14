<?php
// Paramètres de connexion (utilisateur imposé par l'énoncé)
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_commande');
define('DB_USER', 'MasTMound2');
define('DB_PASS', 'MoundMasT2@2026');

define('CURRENCY', 'FCFA');      // devise affichée
define('STOCK_ALERT', 10);       // seuil d'alerte de stock
define('PER_PAGE', 15);          // lignes par page (liste des commandes)
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', 'uploads/');
