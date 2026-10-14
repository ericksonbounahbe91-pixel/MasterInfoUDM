<?php
// POST commande_add.php {idCli} : crée une commande vide pour un client
require 'config.php';
try {
 $d = body();
 $idCli = filter_var($d['idCli'] ?? null, FILTER_VALIDATE_INT);
 if (!$idCli) fail('Client obligatoire.');
 $pdo = db();
 $s = $pdo->prepare('SELECT idCli FROM Client WHERE idCli=?');
 $s->execute([$idCli]);
 if (!$s->fetch()) fail('Client introuvable.',404);
 $pdo->prepare('INSERT INTO Commande(dateCmd,idCli) VALUES(NOW(),?)')->execute([$idCli]);
 ok(['idCmd'=>(int)$pdo->lastInsertId(),'message'=>'Commande créée.'],201);
} catch(Throwable $e) { fail('Impossible de créer la commande.',500); }
