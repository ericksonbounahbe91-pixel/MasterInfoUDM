<?php
declare(strict_types=1);
session_start();

/* ---------- Paramètres de connexion (à adapter si besoin) ---------- */
const DB_HOST = 'localhost';
const DB_NAME = 'tp_commande_2026';
const DB_USER = 'root';
const DB_PASS = '';

const SEUIL_STOCK = 10;                    // en dessous : offre à disponibilité limitée
const CATEGORIES  = ['Logiciels', 'Matériel', 'Services'];
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

/** Icônes SVG en ligne (style trait, héritent la couleur du texte). */
function icone($nom, $taille = 20)
{
    $chemins = [
        'tableau'  => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
        'commande' => '<path d="M6 2.5h12a1 1 0 0 1 1 1V21l-3-2-3 2-3-2-3 2V3.5a1 1 0 0 1 1-1z"/><path d="M9 8h6M9 12h6"/>',
        'produit'  => '<path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>',
        'client'   => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5a6.5 6.5 0 0 1 3.5 5.5"/>',
        'argent'   => '<rect x="2.5" y="6" width="19" height="12" rx="2"/><circle cx="12" cy="12" r="2.8"/><path d="M6 12h.01M18 12h.01"/>',
        'panier'   => '<path d="M3 4h2.5l2.2 10.2a1 1 0 0 0 1 .8h8.6a1 1 0 0 0 1-.8L20 8H6.2"/><circle cx="9.5" cy="19" r="1.4"/><circle cx="17" cy="19" r="1.4"/>',
        'unites'   => '<path d="M12 3l8 4-8 4-8-4 8-4z"/><path d="M4 12l8 4 8-4"/><path d="M4 17l8 4 8-4"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'retour'   => '<path d="M15 6l-6 6 6 6"/>',
        'check'    => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'modifier' => '<path d="M4 20h4L18.5 9.5a2.1 2.1 0 0 0-3-3L5 17v3z"/><path d="M13.5 8.5l2 2"/>',
        'ouvrir'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'retirer'  => '<path d="M5 7h14M10 7V4h4v3M7 7l1 13h8l1-13"/>',
    ];
    $c = isset($chemins[$nom]) ? $chemins[$nom] : '';
    return '<svg class="icone" width="' . (int) $taille . '" height="' . (int) $taille . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $c . '</svg>';
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
    $teinte = abs(crc32($nom)) % 360;
    return '<span class="vignette vide" style="background:hsl(' . $teinte . ',65%,92%);color:hsl(' . $teinte . ',45%,32%)" aria-hidden="true">' . h($ini) . '</span>';
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
