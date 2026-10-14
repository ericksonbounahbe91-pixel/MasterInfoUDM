<?php
require 'config.php';
try {
 $sql = "SELECT co.idCmd,co.dateCmd,co.idCli,
         CONCAT(c.prenomCli,' ',c.nomCli) AS client,
         COALESCE(SUM(l.qteCmd*p.prixUProd),0) AS montant,
         COUNT(l.idProd) AS nbProduits
         FROM Commande co JOIN Client c ON c.idCli=co.idCli
         LEFT JOIN ligneCommande l ON l.idCmd=co.idCmd
         LEFT JOIN Produit p ON p.idProd=l.idProd
         GROUP BY co.idCmd,co.dateCmd,co.idCli,c.prenomCli,c.nomCli
         ORDER BY co.dateCmd DESC";
 ok(db()->query($sql)->fetchAll());
} catch(Throwable $e) { fail('Impossible de charger les commandes.',500); }
