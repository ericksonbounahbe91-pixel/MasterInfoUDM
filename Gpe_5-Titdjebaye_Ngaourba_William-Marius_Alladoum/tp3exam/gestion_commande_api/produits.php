<?php
require 'config.php';
try {
 $sql = "SELECT idProd,designProd,qteStock,prixUProd,photoProd FROM Produit ORDER BY designProd";
 ok(db()->query($sql)->fetchAll());
} catch(Throwable $e) { fail('Impossible de charger les produits.',500); }
