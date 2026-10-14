<?php
require 'config.php';
try {
 $sql = "SELECT c.idCli,c.nomCli,c.prenomCli,c.email,r.nomRegion,
         (SELECT COUNT(*) FROM Commande co WHERE co.idCli=c.idCli) AS nbCommandes
         FROM Client c LEFT JOIN Region r ON r.idRegion=c.idRegion
         ORDER BY c.nomCli,c.prenomCli";
 ok(db()->query($sql)->fetchAll());
} catch(Throwable $e) { fail('Impossible de charger les clients.',500); }
