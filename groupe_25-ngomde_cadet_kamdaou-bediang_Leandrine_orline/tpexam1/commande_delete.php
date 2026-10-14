<?php
/** Suppression d'une commande (POST uniquement). CASCADE supprime les lignes. */
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Méthode non autorisée.'); }

$id = (int)($_POST['idCmd'] ?? 0);
if ($id > 0) {
    $pdo->prepare('DELETE FROM Commande WHERE idCmd = ?')->execute([$id]);
    flash('success', 'Commande supprimée.');
}
header('Location: commandes.php');
