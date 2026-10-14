<?php
require 'config.php';
try {
 $d = body(); $idCmd=(int)($d['idCmd'] ?? 0); $idProd=(int)($d['idProd'] ?? 0);
 if (!$idCmd || !$idProd) fail('Identifiants de commande et de produit obligatoires.');
 $s=db()->prepare('DELETE FROM ligneCommande WHERE idCmd=? AND idProd=?');
 $s->execute([$idCmd,$idProd]);
 if (!$s->rowCount()) fail('Ligne de commande introuvable.',404);
 ok(['message'=>'Produit supprimé de la commande.']);
} catch(Throwable $e) { fail('Impossible de supprimer cette ligne.',500); }
