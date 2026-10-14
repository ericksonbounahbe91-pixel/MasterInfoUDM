<?php
/** Enregistrement d'une commande avec ses lignes. */
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Méthode non autorisée.'); }

$idCli    = (int)($_POST['idCli'] ?? 0);
$dateCmd  = trim($_POST['dateCmd'] ?? '');
$produits = $_POST['produits'] ?? [];
$quantites = $_POST['quantites'] ?? [];

if ($idCli <= 0 || $dateCmd === '' || empty($produits)) {
    flash('error', 'Veuillez remplir tous les champs et ajouter au moins un produit.');
    header('Location: commande_form.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Créer la commande
    $stmt = $pdo->prepare("INSERT INTO Commande (dateCmd, idCli) VALUES (?, ?)");
    $stmt->execute([$dateCmd, $idCli]);
    $idCmd = $pdo->lastInsertId();

    // Insérer les lignes
    $stmtLigne = $pdo->prepare("INSERT INTO ligneCommande (idCmd, idProd, qteCmd) VALUES (?, ?, ?)
                                 ON DUPLICATE KEY UPDATE qteCmd = qteCmd + VALUES(qteCmd)");
    for ($i = 0; $i < count($produits); $i++) {
        $idProd = (int)$produits[$i];
        $qte    = max(1, (int)($quantites[$i] ?? 1));
        if ($idProd > 0) {
            $stmtLigne->execute([$idCmd, $idProd, $qte]);
        }
    }

    $pdo->commit();
    flash('success', "Commande #{$idCmd} enregistrée avec succès.");
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log($e->getMessage());
    flash('error', 'Erreur lors de l\'enregistrement de la commande.');
}

header('Location: commandes.php');
