<?php
$courant = basename($_SERVER['SCRIPT_NAME']);
$menu = [
    'index.php'     => ['Tableau de bord', ['index.php']],
    'commandes.php' => ['Commandes',       ['commandes.php', 'commande.php']],
    'produits.php'  => ['Produits',        ['produits.php', 'produit.php']],
    'clients.php'   => ['Clients',         ['clients.php']],
];
?><!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($titre ?? 'Gestion des commandes') ?> – Gestion des commandes</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="entete">
  <a class="marque" href="index.php">Gestion des commandes</a>
  <nav aria-label="Navigation principale">
<?php foreach ($menu as $href => [$libelle, $pages]): ?>
    <a href="<?= h($href) ?>"<?= in_array($courant, $pages, true) ? ' aria-current="page"' : '' ?>><?= h($libelle) ?></a>
<?php endforeach; ?>
  </nav>
</header>
<main>
<?php flash_afficher(); ?>
