<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_commandes');
define('DB_USER', 'root');
define('DB_PASS', ''); // Par défaut dans XAMPP. Modifiez si votre MySQL a un mot de passe.

try {
    $pdo = new PDO(
        'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Connexion à la base impossible. Vérifiez config.php et importez database.sql dans phpMyAdmin.');
}
function h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function flash(string $message, string $type='success'): void { $_SESSION['flash']=['message'=>$message,'type'=>$type]; }
function redirect(string $url): never { header('Location: '.$url); exit; }
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function check_csrf(): void {
    if ($_SERVER['REQUEST_METHOD']==='POST' && !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419); exit('Formulaire expiré. Revenez en arrière et réessayez.');
    }
}
function require_login(): void { if (empty($_SESSION['user'])) redirect('login.php'); }
function require_admin(): void { require_login(); if ($_SESSION['user']['role'] !== 'admin') { http_response_code(403); exit('Accès réservé à l’administrateur.'); } }
function current_user_id(): int { return (int)($_SESSION['user']['idUser'] ?? 0); }
