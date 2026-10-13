
<?php
/**
 * TP5 - CREATE et UPDATE
 * Compatible avec PHP 7.3 et PDO.
 */

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}

// Vérifier que les champs existent dans config.php
if (!defined('CHAMPS')) {
    exit('Erreur : la constante CHAMPS est absente de config.php.');
}

// Revalidation des données reçues
$d = [];

foreach (CHAMPS as $c) {
    $d[$c] = trim(isset($_POST[$c]) ? $_POST[$c] : '');

    if ($d[$c] === '') {
        header('Location: index.php?err=' . urlencode("Champ manquant : " . $c));
        exit;
    }
}

// Vérifier le format de l'email
if (!filter_var($d['emailEtu'], FILTER_VALIDATE_EMAIL)) {
    header('Location: index.php?err=' . urlencode('Email invalide.'));
    exit;
}

try {
    if (isset($_POST['mode']) && $_POST['mode'] === 'update') {

        // Préparer les champs à modifier, sans toucher à idEtu
        $champsModification = array_diff(CHAMPS, ['idEtu']);
        $partiesSet = [];

        foreach ($champsModification as $c) {
            $partiesSet[] = $c . ' = :' . $c;
        }

        $set = implode(', ', $partiesSet);

        $sql = "UPDATE etudiants SET " . $set . " WHERE idEtu = :idEtu";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($d);

    } else {

        // Préparer l'insertion
        $cols = implode(', ', CHAMPS);
        $listeParams = [];

        foreach (CHAMPS as $c) {
            $listeParams[] = ':' . $c;
        }

        $params = implode(', ', $listeParams);

        $sql = "INSERT INTO etudiants (" . $cols . ")
                VALUES (" . $params . ")";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($d);
    }

    header('Location: index.php?ok=1');
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());

    if ($e->getCode() === '23000') {
        $msg = 'Matricule ou email déjà utilisé.';
    } else {
        $msg = "Erreur lors de l'enregistrement.";
    }

    header('Location: index.php?err=' . urlencode($msg));
    exit;
}
