<?php
/** Suppression d'un produit (POST uniquement). */
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Méthode non autorisée.'); }

$id = $_POST['idProd'] ?? '';
try {
    $pdo->prepare('DELETE FROM Produit WHERE idProd = ?')->execute([$id]);
    flash('success', 'Produit supprimé.');
} catch (PDOException $e) {
    error_log($e->getMessage());
    flash('error', 'Impossible de supprimer : ce produit est utilisé dans une commande.');
}
header('Location: produits.php');
