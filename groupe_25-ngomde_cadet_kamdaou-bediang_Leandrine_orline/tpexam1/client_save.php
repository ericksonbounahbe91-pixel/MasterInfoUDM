<?php
/** Enregistrement d'un client (CREATE / UPDATE). */
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Méthode non autorisée.'); }

$nom    = trim($_POST['nomCli'] ?? '');
$prenom = trim($_POST['prenomCli'] ?? '');
$email  = trim($_POST['email'] ?? '');
$region = (int)($_POST['idRegion'] ?? 0);
$mode   = $_POST['mode'] ?? 'create';
$id     = $_POST['idCli'] ?? null;

if ($nom === '' || $prenom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $region <= 0) {
    flash('error', 'Veuillez remplir tous les champs obligatoires.');
    header('Location: client_form.php' . ($id ? "?id=$id" : ''));
    exit;
}

// Upload photo
$photo = null;
if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
        $photo = 'cli_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__ . '/uploads/' . $photo);
    }
}

try {
    if ($mode === 'update' && $id) {
        $sql = "UPDATE Client SET nomCli = ?, prenomCli = ?, email = ?, idRegion = ?";
        $params = [$nom, $prenom, $email, $region];
        if ($photo) { $sql .= ", photo = ?"; $params[] = $photo; }
        $sql .= " WHERE idCli = ?";
        $params[] = $id;
        $pdo->prepare($sql)->execute($params);
        flash('success', 'Client modifié avec succès.');
    } else {
        $pdo->prepare("INSERT INTO Client (nomCli, prenomCli, email, photo, idRegion) VALUES (?, ?, ?, ?, ?)")
            ->execute([$nom, $prenom, $email, $photo, $region]);
        flash('success', 'Client ajouté avec succès.');
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $msg = ($e->getCode() === '23000') ? 'Cet email est déjà utilisé.' : 'Erreur lors de l\'enregistrement.';
    flash('error', $msg);
}
header('Location: clients.php');
