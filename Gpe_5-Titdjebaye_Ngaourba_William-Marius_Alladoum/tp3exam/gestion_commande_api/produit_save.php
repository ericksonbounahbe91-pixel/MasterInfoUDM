<?php
require 'config.php';
try {
 $d = body();
 $name = trim($d['designProd'] ?? '');
 $stock = filter_var($d['qteStock'] ?? null, FILTER_VALIDATE_INT);
 $price = filter_var($d['prixUProd'] ?? null, FILTER_VALIDATE_FLOAT);
 if ($name === '' || $stock === false || $stock < 0 || $price === false || $price < 0) {
   fail('Vérifiez le libellé, le stock et le prix.');
 }
 $pdo = db();
 if (!empty($d['idProd'])) {
   $st = $pdo->prepare('UPDATE Produit SET designProd=?,qteStock=?,prixUProd=?,photoProd=? WHERE idProd=?');
   $st->execute([$name,$stock,$price,$d['photoProd'] ?? null,(int)$d['idProd']]);
   ok(['message'=>'Produit modifié.']);
 } else {
   $st = $pdo->prepare('INSERT INTO Produit(designProd,qteStock,prixUProd,photoProd) VALUES(?,?,?,?)');
   $st->execute([$name,$stock,$price,$d['photoProd'] ?? null]);
   ok(['idProd'=>(int)$pdo->lastInsertId(),'message'=>'Produit ajouté.']);
 }
} catch(Throwable $e) { fail('Échec de l’enregistrement du produit.',500); }
