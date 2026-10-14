<?php require_once __DIR__ . '/functions.php';
$icons = [
  'dashboard.php' => '<path d="M4 13h6V4H4v9zm0 7h6v-5H4v5zm10 0h6v-9h-6v9zm0-16v5h6V4h-6z"/>',
  'commandes.php' => '<path d="M7 4h10l3 4v12H4V8l3-4zm0 4h10M9 12h6M9 16h6"/>',
  'produits.php'  => '<path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3zm0 9l8-4.5M12 12v9M12 12L4 7.5"/>',
  'clients.php'   => '<path d="M16 19v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 17.5V19M10 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zm9 8v-1.5a3.5 3.5 0 0 0-2.5-3.35M15.5 4.2a3.5 3.5 0 0 1 0 6.6"/>',
];
$nav = ['dashboard.php' => 'Tableau de bord', 'commandes.php' => 'Commandes', 'produits.php' => 'Produits', 'clients.php' => 'Clients'];
$current = basename($_SERVER['SCRIPT_NAME']);
$groups = ['commande.php' => 'commandes.php', 'produit_form.php' => 'produits.php'];
$current = $groups[$current] ?? $current;
?><!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle ?? 'Commandes') ?> — Gestion des commandes</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<aside class="side">
  <a class="brand" href="dashboard.php"><span class="brand-mark" aria-hidden="true">R</span><span>Registre<br>des commandes</span></a>
  <nav aria-label="Navigation principale">
    <?php foreach ($nav as $file => $label): ?>
      <a href="<?= $file ?>" <?= $current === $file ? 'aria-current="page"' : '' ?>>
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $icons[$file] ?></svg>
        <?= h($label) ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <p class="side-foot">Base <b>gestion_commande</b><br>connectée</p>
</aside>
<div class="shell">
  <header class="topbar">
    <span class="crumb">Registre <i>/</i> <b><?= h($nav[$current] ?? $pageTitle ?? '') ?></b></span>
    <span class="today"><?= h(date('d/m/Y')) ?></span>
  </header>
  <main class="main">
<?php foreach ($_SESSION['flash'] ?? [] as [$type, $msg]): ?>
  <div class="flash flash-<?= h($type) ?>" role="status"><?= h($msg) ?></div>
<?php endforeach; unset($_SESSION['flash']); ?>
