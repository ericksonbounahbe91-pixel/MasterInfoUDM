<?php
$courant = basename($_SERVER['SCRIPT_NAME']);
$menu = [
    'index.php'     => ['Tableau de bord', 'tableau',  ['index.php']],
    'commandes.php' => ['Commandes',       'commande', ['commandes.php', 'commande.php']],
    'produits.php'  => ['Offres',          'produit',  ['produits.php', 'produit.php']],
    'clients.php'   => ['Clients',         'client',   ['clients.php']],
];
?><!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h(isset($titre) ? $titre : 'Outils numériques – Commandes') ?> – Outils numériques – Commandes</title>
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%236A47F0'/%3E%3Cpath d='M9 12l7-4 7 4-7 4z M9 12v8l7 4 7-4v-8' fill='none' stroke='white' stroke-width='2' stroke-linejoin='round'/%3E%3C/svg%3E">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app">
  <aside class="barre-laterale">
    <a class="marque" href="index.php">
      <span class="logo"><?= icone('produit', 22) ?></span>
      <span><strong>Digital Store</strong><small>Commandes &amp; statistiques</small></span>
    </a>
    <p class="nav-titre">Navigation</p>
    <nav aria-label="Navigation principale">
<?php foreach ($menu as $href => list($libelle, $ico, $pages)): ?>
      <a href="<?= h($href) ?>"<?= in_array($courant, $pages, true) ? ' aria-current="page"' : '' ?>><?= icone($ico, 20) ?><span><?= h($libelle) ?></span></a>
<?php endforeach; ?>
    </nav>
    <div class="bas-barre">
      <strong>Outils numériques</strong>
      <span>Ventes · Licences · Services</span>
    </div>
  </aside>

  <div class="contenu">
    <div class="topbar">
      <span class="fil">Accueil <span aria-hidden="true">/</span> <strong><?= h(isset($titre) ? $titre : '') ?></strong></span>
      <span class="date"><?= h(date('d/m/Y')) ?></span>
    </div>
    <main>
<?php flash_afficher(); ?>
