<?php
/** Suppression d'un produit dans une commande (ligne de commande). */
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Méthode non autorisée.'); }

$idCmd  = (int)($_POST['idCmd'] ?? 0);
$idProd = (int)($_POST['idProd'] ?? 0);

if ($idCmd > 0 && $idProd > 0) {
    $pdo->prepare('DELETE FROM ligneCommande WHERE idCmd = ? AND idProd = ?')->execute([$idCmd, $idProd]);
    flash('success', 'Produit retiré de la commande.');
}

header('Location: commande_detail.php?id=' . $idCmd);
