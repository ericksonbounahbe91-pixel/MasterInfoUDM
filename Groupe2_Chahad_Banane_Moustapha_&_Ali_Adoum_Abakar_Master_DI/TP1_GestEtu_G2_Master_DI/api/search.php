<?php
/**
 * =============================================================================
 *  api/search.php — API JSON pour la recherche live (fetch)
 * =============================================================================
 *  Entrée  : GET ?q=terme        (au moins 2 caractères)
 *  Sortie  : {"resultats":[{"idEtu":"...","nomEtu":"...","prenomEtu":"..."}]}
 *  Sécurité : requête préparée, sortie encodée en JSON, aucun HTML renvoyé.
 * =============================================================================
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$terme = trim((string)($_GET['q'] ?? ''));

/* --- Validation de l'entrée ------------------------------------------------ */
if (mb_strlen($terme, 'UTF-8') < 2) {
    echo json_encode(['resultats' => [], 'message' => 'Saisissez au moins 2 caractères.'], JSON_UNESCAPED_UNICODE);
    exit;
}
if (mb_strlen($terme, 'UTF-8') > 60) {
    $terme = mb_substr($terme, 0, 60, 'UTF-8');
}

/* --- Recherche ------------------------------------------------------------- */
$requete = $pdo->prepare(
    'SELECT idEtu, nomEtu, prenomEtu, mentionEtu, cycleEtu
       FROM etudiant
      WHERE nomEtu LIKE :q1 OR prenomEtu LIKE :q2 OR idEtu LIKE :q3
      ORDER BY nomEtu
      LIMIT 8'
);
$requete->execute([
    ':q1' => '%' . $terme . '%',
    ':q2' => '%' . $terme . '%',
    ':q3' => '%' . $terme . '%',
]);

echo json_encode(
    ['resultats' => $requete->fetchAll(), 'terme' => $terme],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
