<?php
/** Enregistrement d'un produit (CREATE / UPDATE). */
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Méthode non autorisée.'); }

$design = trim($_POST['designProd'] ?? '');
$qte    = (int)($_POST['qteStock'] ?? 0);
$prix   = (float)($_POST['prixUProd'] ?? 0);
$mode   = $_POST['mode'] ?? 'create';
$id     = $_POST['idProd'] ?? null;

if ($design === '' || $prix < 0) {
    flash('error', 'Veuillez remplir tous les champs obligatoires.');
    header('Location: produit_form.php' . ($id ? "?id=$id" : ''));
    exit;
}

// Upload photo
$photo = null;
if (!empty($_FILES['photoProd']['name']) && $_FILES['photoProd']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['photoProd']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
        $photo = 'prod_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['photoProd']['tmp_name'], __DIR__ . '/uploads/' . $photo);
    }
}

try {
    if ($mode === 'update' && $id) {
        $sql = "UPDATE Produit SET designProd = ?, qteStock = ?, prixUProd = ?";
        $params = [$design, $qte, $prix];
        if ($photo) { $sql .= ", photoProd = ?"; $params[] = $photo; }
        $sql .= " WHERE idProd = ?";
        $params[] = $id;
        $pdo->prepare($sql)->execute($params);
        flash('success', 'Produit modifié avec succès.');
    } else {
        $pdo->prepare("INSERT INTO Produit (designProd, qteStock, prixUProd, photoProd) VALUES (?, ?, ?, ?)")
            ->execute([$design, $qte, $prix, $photo]);
        flash('success', 'Produit ajouté avec succès.');
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    flash('error', 'Erreur lors de l\'enregistrement.');
}

header('Location: produits.php');
