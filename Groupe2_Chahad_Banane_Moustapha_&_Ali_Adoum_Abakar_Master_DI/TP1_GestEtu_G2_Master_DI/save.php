<?php
/**
 * =============================================================================
 *  save.php — CREATE et UPDATE avec validation serveur renforcée
 * =============================================================================
 *  Règles appliquées (on ne fait jamais confiance au navigateur) :
 *    1. Méthode POST uniquement ;
 *    2. Jeton CSRF valide ;
 *    3. Présence de tous les champs, longueurs maximales respectées ;
 *    4. Format de l'email (FILTER_VALIDATE_EMAIL) ;
 *    5. Format du téléphone (expression régulière) ;
 *    6. Date de naissance cohérente (format réel, non future, âge plausible) ;
 *    7. En mode modification : existence de la fiche et immutabilité du matricule.
 *  Les erreurs sont cumulées puis restituées sous forme de messages flash.
 * =============================================================================
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

/* --- 1. Méthode HTTP ------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}

/* --- 2. Protection CSRF ---------------------------------------------------- */
verifier_csrf();

$mode = ($_POST['mode'] ?? '') === 'update' ? 'update' : 'create';

/* --- 3. Collecte et nettoyage --------------------------------------------- */
$donnees = [];
foreach (CHAMPS as $champ) {
    $donnees[$champ] = trim((string)($_POST[$champ] ?? ''));
}

$erreurs = [];

/* --- 4. Présence et longueur ---------------------------------------------- */
foreach (CHAMPS as $champ) {
    if ($donnees[$champ] === '') {
        $erreurs[] = 'Le champ « ' . LIBELLES[$champ] . ' » est obligatoire.';
        continue;
    }
    $maximum = LONGUEURS_MAX[$champ] ?? 60;
    if (mb_strlen($donnees[$champ], 'UTF-8') > $maximum) {
        $erreurs[] = 'Le champ « ' . LIBELLES[$champ] . ' » ne doit pas dépasser '
                   . $maximum . ' caractères.';
    }
}

/* --- 5. Format de l'email -------------------------------------------------- */
if ($donnees['emailEtu'] !== '' && !filter_var($donnees['emailEtu'], FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = 'L’adresse électronique saisie n’est pas valide.';
}

/* --- 6. Format du téléphone ------------------------------------------------ */
if ($donnees['numTelEtu'] !== '' && !preg_match(REGEX_TEL, $donnees['numTelEtu'])) {
    $erreurs[] = 'Le numéro de téléphone doit comporter de 8 à 15 chiffres '
               . '(l’indicatif « + » est accepté en tête).';
}

/* --- 7. Cohérence de la date de naissance --------------------------------- */
if ($donnees['dateNaiss'] !== '') {
    $date = DateTimeImmutable::createFromFormat('Y-m-d', $donnees['dateNaiss']);
    $valide = $date && $date->format('Y-m-d') === $donnees['dateNaiss'];
    if (!$valide) {
        $erreurs[] = 'La date de naissance est invalide (format attendu : AAAA-MM-JJ).';
    } else {
        $aujourdhui = new DateTimeImmutable('today');
        $age = $date->diff($aujourdhui)->y;
        if ($date > $aujourdhui) {
            $erreurs[] = 'La date de naissance ne peut pas être postérieure à la date du jour.';
        } elseif ($age < 15 || $age > 80) {
            $erreurs[] = 'L’âge calculé (' . $age . ' ans) est incohérent pour un étudiant.';
        }
    }
}

/* --- 8. Contrôles propres à la modification -------------------------------- */
if ($mode === 'update') {
    $verification = $pdo->prepare('SELECT COUNT(*) FROM etudiant WHERE idEtu = :id');
    $verification->execute([':id' => $donnees['idEtu']]);
    if ((int)$verification->fetchColumn() === 0) {
        $erreurs[] = 'Aucun étudiant ne porte le matricule « ' . $donnees['idEtu'] . ' ».';
    }
}

/* --- 9. Restitution en cas d'erreur --------------------------------------- */
if ($erreurs) {
    memoriser_saisie($donnees);
    foreach ($erreurs as $erreur) {
        flash('danger', $erreur);
    }
    $retour = $mode === 'update' ? 'form.php?id=' . urlencode($donnees['idEtu']) : 'form.php';
    rediriger($retour);
}

/* --- 10. Enregistrement ---------------------------------------------------- */
try {
    if ($mode === 'update') {
        /* Le matricule sert de critère WHERE : il n'est jamais modifié. */
        $champsModifies = array_values(array_diff(CHAMPS, ['idEtu']));
        $affectations   = implode(', ', array_map(
            static fn(string $c): string => $c . ' = :' . $c,
            $champsModifies
        ));
        $requete = $pdo->prepare('UPDATE etudiant SET ' . $affectations . ' WHERE idEtu = :idEtu');
        $requete->execute($donnees);
        flash('success', 'La fiche de ' . $donnees['prenomEtu'] . ' ' . $donnees['nomEtu']
                       . ' a été mise à jour.');
    } else {
        $colonnes  = implode(', ', CHAMPS);
        $marqueurs = implode(', ', array_map(static fn(string $c): string => ':' . $c, CHAMPS));
        $requete   = $pdo->prepare('INSERT INTO etudiant (' . $colonnes . ') VALUES (' . $marqueurs . ')');
        $requete->execute($donnees);
        flash('success', 'L’étudiant ' . $donnees['prenomEtu'] . ' ' . $donnees['nomEtu']
                       . ' a été enregistré.');
    }
    rediriger('index.php');

} catch (PDOException $e) {
    /* 23000 = violation de contrainte (doublon de matricule ou d'email) */
    error_log('[TP5] save.php : ' . $e->getMessage());
    memoriser_saisie($donnees);
    $message = $e->getCode() === '23000'
        ? 'Ce matricule ou cette adresse électronique est déjà utilisé(e) par un autre étudiant.'
        : 'Une erreur technique est survenue lors de l’enregistrement.';
    flash('danger', $message);
    $retour = $mode === 'update' ? 'form.php?id=' . urlencode($donnees['idEtu']) : 'form.php';
    rediriger($retour);
}
