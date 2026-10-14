<?php
// GET lignes.php?idCmd=3 : détail (lignes) d'une commande
require 'config.php';
try {
 $idCmd = filter_var($_GET['idCmd'] ?? null, FILTER_VALIDATE_INT);
 if (!$idCmd) fail('Numéro de commande obligatoire.');
 $st = db()->prepare("SELECT l.idProd,p.designProd,l.qteCmd,p.prixUProd,
        l.qteCmd*p.prixUProd AS sousTotal
        FROM ligneCommande l JOIN Produit p ON p.idProd=l.idProd
        WHERE l.idCmd=? ORDER BY p.designProd");
 $st->execute([$idCmd]);
 ok($st->fetchAll());
} catch(Throwable $e) { fail('Impossible de charger les lignes de la commande.',500); }
