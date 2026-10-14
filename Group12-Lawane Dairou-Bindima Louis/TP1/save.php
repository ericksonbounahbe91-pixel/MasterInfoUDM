<?php
/** TP5 – CREATE et UPDATE : traitement du formulaire (POST uniquement, CSRF, validation serveur). */
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Méthode non autorisée.'); }
csrf_verifier();

$mode = ($_POST['mode'] ?? '') === 'update' ? 'update' : 'create';

// Récupération (les valeurs non textuelles sont neutralisées)
$d = [];
foreach (CHAMPS as $c) {
    $v = $_POST[$c] ?? '';
    $d[$c] = is_string($v) ? trim($v) : '';
}

// Revalidation serveur : on ne fait jamais confiance au navigateur
$err = [];
foreach (CHAMPS as $c) {
    if ($d[$c] === '') { $err[] = 'Champ obligatoire : ' . LIBELLES[$c] . '.'; }
    elseif (mb_strlen($d[$c]) > 150) { $err[] = LIBELLES[$c] . ' : 150 caractères maximum.'; }
}
if ($d['idEtu'] !== '' && !preg_match('/^[A-Za-z0-9_-]{3,20}$/', $d['idEtu'])) {
    $err[] = 'Matricule : 3 à 20 caractères (lettres, chiffres, - ou _).';
}
foreach (['nomEtu', 'prenomEtu'] as $c) {
    if ($d[$c] !== '' && !preg_match("/^[\\p{L}\\s'’-]+$/u", $d[$c])) { $err[] = LIBELLES[$c] . ' : lettres uniquement.'; }
}
if ($d['dateNaiss'] !== '') {
    $dt = DateTime::createFromFormat('Y-m-d', $d['dateNaiss']);
    if (!$dt || $dt->format('Y-m-d') !== $d['dateNaiss']) { $err[] = 'Date de naissance invalide.'; }
    elseif ($dt > new DateTime('today') || (int)$dt->format('Y') < 1900) { $err[] = 'Date de naissance hors limites.'; }
}
if ($d['emailEtu'] !== '' && !filter_var($d['emailEtu'], FILTER_VALIDATE_EMAIL)) { $err[] = 'Email invalide.'; }
if ($d['numTelEtu'] !== '' && !preg_match('/^\+?[0-9 ]{8,15}$/', $d['numTelEtu'])) {
    $err[] = 'Téléphone invalide (8 à 15 chiffres, « + » autorisé au début).';
}
foreach ([['regEtu', REGIONS], ['mentionEtu', MENTIONS], ['cycleEtu', CYCLES]] as [$c, $liste]) {
    if ($d[$c] !== '' && !in_array($d[$c], $liste, true)) { $err[] = LIBELLES[$c] . ' : valeur non autorisée.'; }
}

$retour = 'form.php' . ($mode === 'update' ? '?id=' . urlencode($d['idEtu']) : '');
if ($err) {
    $_SESSION['old'] = $d; $_SESSION['errors'] = $err;   // le formulaire se réaffiche avec les erreurs
    header("Location: $retour"); exit;
}

try {
    if ($mode === 'update') {
        // Le matricule sert de critère WHERE et n'est pas modifié
        $set = implode(', ', array_map(fn($c) => "$c = :$c", array_diff(CHAMPS, ['idEtu'])));
        $pdo->prepare("UPDATE etudiant SET $set WHERE idEtu = :idEtu")->execute($d);
        flash('success', 'Étudiant modifié avec succès.');
    } else {
        $cols = implode(', ', CHAMPS);
        $params = implode(', ', array_map(fn($c) => ":$c", CHAMPS));
        $pdo->prepare("INSERT INTO etudiant ($cols) VALUES ($params)")->execute($d);
        flash('success', 'Étudiant ajouté avec succès.');
    }
    header('Location: index.php');
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['old'] = $d;
    $_SESSION['errors'] = [($e->getCode() === '23000') ? 'Matricule ou email déjà utilisé.' : "Erreur lors de l'enregistrement."];
    header("Location: $retour");
}
exit;
