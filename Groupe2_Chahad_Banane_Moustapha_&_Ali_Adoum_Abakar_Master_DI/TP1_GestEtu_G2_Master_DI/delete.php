<?php
/**
 * =============================================================================
 *  delete.php — DELETE : suppression d'un étudiant (POST + CSRF uniquement)
 * =============================================================================
 *  Une suppression ne doit JAMAIS être déclenchée par un simple lien GET :
 *  un moteur de préchargement ou une image distante pourrait la provoquer.
 * =============================================================================
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

/* --- Méthode HTTP ---------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}

/* --- Protection CSRF ------------------------------------------------------- */
verifier_csrf();

$id = trim((string)($_POST['idEtu'] ?? ''));
if ($id === '') {
    flash('danger', 'Aucun matricule n’a été transmis pour la suppression.');
    rediriger('index.php');
}

try {
    /* Récupération du nom pour un message explicite */
    $requete = $pdo->prepare('SELECT nomEtu, prenomEtu FROM etudiant WHERE idEtu = :id');
    $requete->execute([':id' => $id]);
    $etudiant = $requete->fetch();

    if (!$etudiant) {
        flash('warning', 'L’étudiant demandé n’existe déjà plus.');
        rediriger('index.php');
    }

    $pdo->beginTransaction();

    /* Les cartes générées pour cet étudiant sont supprimées en premier
       (la table carte_etudiant est créée par schema_migration.sql). */
    try {
        $pdo->prepare('DELETE FROM carte_etudiant WHERE idEtu = :id')->execute([':id' => $id]);
    } catch (PDOException $e) {
        error_log('[TP5] delete.php (cartes) : ' . $e->getMessage());
    }

    $pdo->prepare('DELETE FROM etudiant WHERE idEtu = :id')->execute([':id' => $id]);
    $pdo->commit();

    flash('success', 'L’étudiant ' . $etudiant['prenomEtu'] . ' ' . $etudiant['nomEtu']
                   . ' a été supprimé.');

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[TP5] delete.php : ' . $e->getMessage());
    $message = $e->getCode() === '23000'
        ? 'Suppression impossible : cet étudiant est référencé par d’autres enregistrements.'
        : 'Une erreur technique est survenue lors de la suppression.';
    flash('danger', $message);
}

rediriger('index.php');
