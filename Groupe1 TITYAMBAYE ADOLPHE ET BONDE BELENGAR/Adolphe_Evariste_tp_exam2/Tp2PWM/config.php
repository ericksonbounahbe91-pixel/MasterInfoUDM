<?php
/**
 * TP5 – Connexion PDO à MySQL (XAMPP : utilisateur root, mot de passe vide).
 * PDO + requêtes préparées = protection contre les injections SQL.
 * Placer le dossier tp5 dans C:\xampp\htdocs\ puis ouvrir http://localhost/tp5/
 * La base « universiteMbdTest » est celle créée par lecon2/tp4/schema.sql.
 */
declare(strict_types=1);

$dsn = 'mysql:host=localhost;dbname=universite;charset=utf8mb4';
try {
    $pdo = new PDO($dsn, 'root', '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // erreurs sous forme d'exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // tableaux associatifs par défaut
        PDO::ATTR_EMULATE_PREPARES   => false,                    // vraies requêtes préparées
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());                                  // détail dans les logs uniquement
    http_response_code(500);
    exit('Connexion à la base impossible.');
}

/** Échappement HTML pour tout affichage (protection contre le XSS). */
function h(string|int|float|null $valeur): string {
    return htmlspecialchars((string) ($valeur ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Liste des champs du formulaire étudiant (identique à la Leçon 2). */
const CHAMPS = ['idEtu', 'nomEtu', 'prenomEtu', 'dateNaiss', 'emaiEtu', 'numTelEtu',
                'regEtu', 'departEtu', 'arrondisEtu', 'mentionEtu', 'cycleEtu', 'parcoursEtu'];
