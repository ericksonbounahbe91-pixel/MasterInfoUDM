<?php
declare(strict_types=1);
session_start();

/* ---------- Paramètres de connexion (à adapter si besoin) ---------- */
const DB_HOST = 'localhost';
const DB_NAME = 'gestion_commande';
const DB_USER = 'root';
const DB_PASS = '';

const SEUIL_STOCK = 10;                    // en dessous : produit « à surveiller »
const UPLOAD_DIR  = __DIR__ . '/../uploads/';
const UPLOAD_URL  = 'uploads/';

$debut = hrtime(true);                     // pour mesurer le temps de génération

/* ---------- Base de données ---------- */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,   // vraies requêtes préparées
            ]
        );
    }
    return $pdo;
}

/** Exécute une requête préparée et mémorise son temps d'exécution (analyse de performance). */
function q(string $sql, array $params = []): PDOStatement
{
    $t  = hrtime(true);
    $st = db()->prepare($sql);
    $st->execute($params);
    $GLOBALS['journal_sql'][] = [preg_replace('/\s+/', ' ', trim($sql)), (hrtime(true) - $t) / 1e6];
    return $st;
}

/* ---------- Affichage et sécurité ---------- */
function h($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function fcfa($n): string
{
    return number_format((float) $n, 0, ',', ' ') . ' FCFA';
}

function csrf_field(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return '<input type="hidden" name="csrf" value="' . h($_SESSION['csrf']) . '">';
}

function csrf_verifier(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Jeton de sécurité invalide. Rechargez la page puis recommencez.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [$type, $message];
}

function flash_afficher(): void
{
    foreach ($_SESSION['flash'] ?? [] as [$type, $message]) {
        echo '<p class="message ' . h($type) . '" role="status">' . h($message) . '</p>';
    }
    unset($_SESSION['flash']);
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Vignette : photo si elle existe, sinon initiale. */
function vignette(?string $photo, string $nom): string
{
    if ($photo && is_file(UPLOAD_DIR . basename($photo))) {
        return '<img class="vignette" src="' . h(UPLOAD_URL . basename($photo)) . '" alt="' . h($nom) . '" loading="lazy">';
    }
    $ini = mb_strtoupper(mb_substr(trim($nom), 0, 1));
    return '<span class="vignette vide" aria-hidden="true">' . h($ini) . '</span>';
}

/** Téléverse une photo (JPG/PNG/WebP, 2 Mo max). Renvoie le nom de fichier à stocker. */
function televerser_photo(string $champ, ?string $ancienne = null): ?string
{
    $f = $_FILES[$champ] ?? null;
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) {
        return $ancienne;
    }
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Le téléversement de la photo a échoué.');
    }
    if ($f['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('La photo dépasse 2 Mo.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if ($ext === null) {
        throw new RuntimeException('Format de photo refusé : utilisez JPG, PNG ou WebP.');
    }
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }
    $nom = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $nom)) {
        throw new RuntimeException("Impossible d'enregistrer la photo sur le serveur.");
    }
    return $nom;
}

/* ---------- Vérification de la connexion ---------- */
try {
    db();
} catch (PDOException $e) {
    http_response_code(500);
    exit('<!doctype html><meta charset="utf-8"><title>Connexion impossible</title>'
        . '<p style="font-family:sans-serif;max-width:40rem;margin:3rem auto;line-height:1.5">'
        . 'Connexion à la base impossible. Vérifiez que MySQL est démarré et que les paramètres '
        . 'de <code>inc/config.php</code> (hôte, nom de la base, identifiant, mot de passe) sont corrects.</p>');
}
