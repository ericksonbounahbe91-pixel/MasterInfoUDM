<?php
declare(strict_types=1);
/**
 * TP5 – Connexion PDO à MySQL (XAMPP : utilisateur root, mot de passe vide).
 * PDO + requêtes préparées = protection contre les injections SQL.
 * Placer le dossier tp5 dans C:\xampp\htdocs\ puis ouvrir http://localhost/tp5/
 * La base « universiteMbdTest » est celle créée par lecon2/tp4/schema.sql.
 */

$dsn = 'mysql:host=localhost;dbname=universite;charset=utf8mb4';
try {
    $pdo = new PDO($dsn, 'root', '');
    // See the "errors" folder for details...
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log($e->getMessage());                             // détail dans les logs uniquement
    exit('Connexion à la base impossible.');
}

/** Échappement HTML pour tout affichage (protection contre le XSS). */
function h(?string $valeur): string {
    return htmlspecialchars($valeur ?? '', ENT_QUOTES, 'UTF-8');
}

/** Liste des champs du formulaire étudiant (identique à la Leçon 2). */
const CHAMPS = ['idEtu','nomEtu','prenomEtu','dateNaiss','emailEtu','numTelEtu',
                'regEtu','departEtu','arrondisEtu','mentionEtu','cycleEtu','ParcoursEtu'];
