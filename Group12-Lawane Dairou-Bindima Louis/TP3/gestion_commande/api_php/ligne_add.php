<?php
require 'config.php';
try {
 $d = body();
 $idCmd = filter_var($d['idCmd'] ?? null, FILTER_VALIDATE_INT);
 $idProd = filter_var($d['idProd'] ?? null, FILTER_VALIDATE_INT);
 $qte = filter_var($d['qteCmd'] ?? null, FILTER_VALIDATE_INT);
 if (!$idCmd || !$idProd || !$qte || $qte < 1) fail('Commande, produit et quantité positive obligatoires.');
 $pdo = db();
 $pdo->beginTransaction();
 $s = $pdo->prepare('SELECT qteStock FROM Produit WHERE idProd=? FOR UPDATE');
 $s->execute([$idProd]); $p = $s->fetch();
 if (!$p) { $pdo->rollBack(); fail('Produit introuvable.',404); }
 $s = $pdo->prepare('SELECT qteCmd FROM ligneCommande WHERE idCmd=? AND idProd=?');
 $s->execute([$idCmd,$idProd]); $existing = $s->fetch();
 $newQty = $qte + (int)($existing['qteCmd'] ?? 0);
 if ($newQty > (int)$p['qteStock']) { $pdo->rollBack(); fail('Stock insuffisant.'); }
 if ($existing) {
   $s = $pdo->prepare('UPDATE ligneCommande SET qteCmd=? WHERE idCmd=? AND idProd=?');
   $s->execute([$newQty,$idCmd,$idProd]);
 } else {
   $s = $pdo->prepare('INSERT INTO ligneCommande(idCmd,idProd,qteCmd) VALUES(?,?,?)');
   $s->execute([$idCmd,$idProd,$qte]);
 }
 $pdo->commit(); ok(['message'=>'Produit ajouté à la commande.']);
} catch(Throwable $e) {
 if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
 fail('Impossible d’ajouter ce produit à la commande.',500);
}
