<?php
/** TP5 – DELETE : suppression par POST après confirmation côté navigateur. */
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}

$id = $_POST['idEtu'] ?? '';
if (!is_string($id) || trim($id) === '') {
    header('Location: index.php?err=' . urlencode('Matricule manquant.'));
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM etudiants WHERE idEtu = ?');
    $stmt->execute([trim($id)]);

    if ($stmt->rowCount() === 0) {
        header('Location: index.php?err=' . urlencode('Étudiant introuvable.'));
    } else {
        header('Location: index.php?ok=1');
    }
    exit;
} catch (PDOException $e) {
    error_log($e->getMessage());
    $msg = ($e->getCode() === '23000')
        ? 'Suppression impossible : cet étudiant est lié à d\'autres données.'
        : 'Erreur lors de la suppression.';
    header('Location: index.php?err=' . urlencode($msg));
    exit;
}