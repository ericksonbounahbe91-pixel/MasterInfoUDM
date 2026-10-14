<?php
/** TP5 – CREATE et UPDATE : traitement du formulaire (méthode POST uniquement). */
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}

/** Redirige vers la liste avec un message d'erreur. */
function erreur(string $message): never {
    header('Location: index.php?err=' . urlencode($message));
    exit;
}

// Revalidation serveur : on ne fait jamais confiance au navigateur
$d = [];
foreach (CHAMPS as $c) {
    $valeur = $_POST[$c] ?? '';
    $d[$c] = is_string($valeur) ? trim($valeur) : '';
    if ($d[$c] === '') {
        erreur("Champ manquant : $c");
    }
}

if (!filter_var($d['emaiEtu'], FILTER_VALIDATE_EMAIL)) {
    erreur('Email invalide.');
}

$date = DateTime::createFromFormat('Y-m-d', $d['dateNaiss']);
if (!$date || $date->format('Y-m-d') !== $d['dateNaiss']) {
    erreur('Date de naissance invalide.');
}

try {
    if (($_POST['mode'] ?? '') === 'update') {
        // Le matricule sert de critère WHERE et n'est pas modifié
        $set = implode(', ', array_map(fn($c) => "$c = :$c", array_diff(CHAMPS, ['idEtu'])));
        $pdo->prepare("UPDATE etudiants SET $set WHERE idEtu = :idEtu")->execute($d);
    } else {
        $cols   = implode(', ', CHAMPS);
        $params = implode(', ', array_map(fn($c) => ":$c", CHAMPS));
        $pdo->prepare("INSERT INTO etudiants ($cols) VALUES ($params)")->execute($d);
    }
    header('Location: index.php?ok=1');
    exit;
} catch (PDOException $e) {
    error_log($e->getMessage());
    $msg = ($e->getCode() === '23000')
        ? 'Matricule ou email déjà utilisé.'
        : "Erreur lors de l'enregistrement.";
    erreur($msg);
}