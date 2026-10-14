<?php
require 'config.php';
try {
 $pdo=db();
 $summary=$pdo->query("SELECT
   (SELECT COUNT(*) FROM Commande) AS totalCommandes,
   (SELECT COUNT(*) FROM Client) AS totalClients,
   (SELECT COUNT(*) FROM Produit) AS totalProduits,
   (SELECT COALESCE(SUM(l.qteCmd*p.prixUProd),0)
      FROM ligneCommande l JOIN Produit p ON p.idProd=l.idProd) AS chiffreAffaires,
   (SELECT COALESCE(SUM(l.qteCmd),0) FROM ligneCommande l) AS quantiteVendue")->fetch();
 $top=$pdo->query("SELECT p.designProd,SUM(l.qteCmd) AS quantite
   FROM ligneCommande l JOIN Produit p ON p.idProd=l.idProd
   GROUP BY p.idProd,p.designProd ORDER BY quantite DESC LIMIT 5")->fetchAll();
 // Chiffre d'affaires des 6 derniers mois (du plus ancien au plus récent)
 $mois=$pdo->query("SELECT DATE_FORMAT(co.dateCmd,'%Y-%m') AS mois,
   SUM(l.qteCmd*p.prixUProd) AS montant
   FROM Commande co
   JOIN ligneCommande l ON l.idCmd=co.idCmd
   JOIN Produit p ON p.idProd=l.idProd
   GROUP BY DATE_FORMAT(co.dateCmd,'%Y-%m')
   ORDER BY mois DESC LIMIT 6")->fetchAll();
 $mois=array_reverse($mois);
 // Meilleurs clients par montant dépensé
 $clients=$pdo->query("SELECT CONCAT(c.prenomCli,' ',c.nomCli) AS client,
   SUM(l.qteCmd*p.prixUProd) AS montant
   FROM Client c
   JOIN Commande co ON co.idCli=c.idCli
   JOIN ligneCommande l ON l.idCmd=co.idCmd
   JOIN Produit p ON p.idProd=l.idProd
   GROUP BY c.idCli,c.prenomCli,c.nomCli
   ORDER BY montant DESC LIMIT 5")->fetchAll();
 // Produits à réapprovisionner (stock <= 10)
 $stock=$pdo->query("SELECT designProd,qteStock FROM Produit
   WHERE qteStock<=10 ORDER BY qteStock,designProd LIMIT 6")->fetchAll();
 ok(['summary'=>$summary,'topProduits'=>$top,'caParMois'=>$mois,
     'topClients'=>$clients,'stockFaible'=>$stock]);
} catch(Throwable $e) { fail('Impossible de calculer les statistiques.',500); }
