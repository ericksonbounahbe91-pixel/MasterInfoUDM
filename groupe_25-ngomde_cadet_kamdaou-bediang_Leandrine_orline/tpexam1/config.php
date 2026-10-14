<?php
/**
 * TP Examen 1 – Connexion PDO à MySQL.
 * Login : MasTMound2 | Mot de passe : MoundMasT2@2026
 */
declare(strict_types=1);
session_start();

$dsn = 'mysql:host=localhost;dbname=gestion_commandes;charset=utf8mb4';
try {
    $pdo = new PDO($dsn, 'MasTMound2', 'MoundMasT2@2026');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('<div style="text-align:center;margin-top:4rem;font-family:sans-serif">
          <h2>Connexion impossible</h2>
          <p>Vérifiez que MySQL est démarré et que la base <code>gestion_commandes</code> existe.</p></div>');
}

/** Échappement HTML (protection XSS). */
function h(?string $v): string {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

/** Message flash : stocke en session, affiché une seule fois. */
function flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function showFlash(): string {
    if (!isset($_SESSION['flash'])) return '';
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $t = $f['type'] === 'success' ? 'success' : 'danger';
    return '<div class="alert alert-' . $t . ' alert-dismissible fade show" role="alert">'
         . h($f['msg'])
         . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}
