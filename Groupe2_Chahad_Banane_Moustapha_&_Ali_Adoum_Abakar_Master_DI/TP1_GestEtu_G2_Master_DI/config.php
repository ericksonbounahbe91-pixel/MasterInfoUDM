<?php
/**
 * =============================================================================
 *  config.php — Configuration centrale de l'application
 *  Projet : « Gestion des étudiants » (TP5 refactorisé) + cartes étudiantes
 *  Environnement : XAMPP — Apache / PHP 8+ / MySQL (MariaDB)
 * =============================================================================
 *
 *  Rôle de ce fichier :
 *    1. Démarrer une session sécurisée (messages flash + jeton CSRF).
 *    2. Envoyer les en-têtes de sécurité, dont la Content-Security-Policy.
 *    3. Établir la connexion PDO à MySQL (requêtes préparées NATIVES).
 *    4. Déclarer les constantes globales (champs, secret HMAC, chemins).
 *    5. Fournir la fonction h() d'échappement HTML (protection XSS).
 *
 *  Ce fichier est inclus par TOUTES les pages, y compris api/search.php.
 * =============================================================================
 */
declare(strict_types=1);

/* -----------------------------------------------------------------------------
 * 1. Constantes globales
 * --------------------------------------------------------------------------- */

/** Version de l'application (affichée dans le pied de page). */
const APP_VERSION = '2.0.0';

/** Établissement affiché dans l'interface et imprimé sur les cartes. */
const ETABLISSEMENT = 'Université de Moundou';

/** Année académique portée sur les cartes étudiantes. */
const ANNEE_ACADEMIQUE = '2025-2026';

/**
 * Clé secrète servant à signer les QR codes (HMAC-SHA256).
 * EN PRODUCTION : sortir cette valeur du code source (fichier .env non versionné).
 */
const CLE_SECRETE = 'TP5-UdM-2026-cle-secrete-a-remplacer-en-production';

/** Dossier de stockage des cartes générées (PDF / PNG). Protégé par .htaccess. */
const DOSSIER_CARTES = __DIR__ . '/cartes';

/** Nombre d'étudiants affichés par page (pagination). */
const PAR_PAGE = 10;

/**
 * Liste des champs du formulaire étudiant (identique à la Leçon 2).
 * ATTENTION : « ParcoursEtu » conserve son P majuscule (nom réel de la colonne).
 */
const CHAMPS = ['idEtu','nomEtu','prenomEtu','dateNaiss','emailEtu','numTelEtu',
                'regEtu','departEtu','arrondisEtu','mentionEtu','cycleEtu','ParcoursEtu'];

/* -----------------------------------------------------------------------------
 * 2. Fuseau horaire et session sécurisée
 * --------------------------------------------------------------------------- */
date_default_timezone_set('Africa/Ndjamena');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,                        // inaccessible en JavaScript
        'samesite' => 'Lax',                       // limite le CSRF inter-sites
        'secure'   => (($_SERVER['HTTPS'] ?? '') === 'on'),
    ]);
    session_name('TP5SESSID');
    session_start();
}

/* -----------------------------------------------------------------------------
 * 3. En-têtes de sécurité (Content-Security-Policy)
 * --------------------------------------------------------------------------- */
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    /* La CSP autorise uniquement : le site lui-même, le CDN Bootstrap,
       les images en data: (QR code, logo) et le service de QR de secours. */
    header(
        "Content-Security-Policy: default-src 'self'; "
        . "img-src 'self' data: https://api.qrserver.com; "
        . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
        . "script-src 'self' https://cdn.jsdelivr.net; "
        . "font-src 'self' data: https://cdn.jsdelivr.net; "
        . "connect-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'"
    );
}

/* -----------------------------------------------------------------------------
 * 4. Connexion PDO à MySQL
 * --------------------------------------------------------------------------- */
$dsn = 'mysql:host=localhost;dbname=universite;charset=utf8mb4';
try {
    $pdo = new PDO($dsn, 'root', '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        /* Requêtes préparées NATIVES : indispensable pour binder LIMIT/OFFSET en entier. */
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log('[TP5] Connexion PDO : ' . $e->getMessage());   // détail dans les logs uniquement
    http_response_code(503);
    exit('Connexion à la base de données impossible. Veuillez vérifier que MySQL est démarré.');
}

/* -----------------------------------------------------------------------------
 * 5. Échappement HTML
 * --------------------------------------------------------------------------- */

/** Échappe une valeur pour tout affichage HTML (protection contre le XSS). */
function h(?string $valeur): string
{
    return htmlspecialchars($valeur ?? '', ENT_QUOTES, 'UTF-8');
}
