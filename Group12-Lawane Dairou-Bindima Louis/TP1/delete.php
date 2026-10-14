<?php
/** TP5 – DELETE : suppression par POST, avec confirmation navigateur et jeton CSRF. */
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Méthode non autorisée.'); }
csrf_verifier();

try {
    $stmt = $pdo->prepare('DELETE FROM etudiant WHERE idEtu = ?');
    $id = $_POST['idEtu'] ?? '';
    $stmt->execute([is_string($id) ? $id : '']);
    $stmt->rowCount() > 0 ? flash('success', 'Étudiant supprimé.') : flash('warning', 'Étudiant introuvable.');
} catch (PDOException $e) {
    error_log($e->getMessage());
    flash('danger', 'Erreur lors de la suppression.');
}
header('Location: index.php');
exit;
