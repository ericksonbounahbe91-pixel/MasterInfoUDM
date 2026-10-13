<?php
require_once __DIR__.'/../config.php';
$pageTitle=$pageTitle??'Gestion des commandes';
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=h($pageTitle)?> | Gestion Commandes</title><link rel="stylesheet" href="css/style.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script><script src="js/app.js" defer></script></head><body>
<header class="topbar"><a class="brand" href="dashboard.php">Gestion<span>Commandes</span></a>
<?php if(!empty($_SESSION['user'])): ?><button class="menu-toggle" aria-label="Ouvrir le menu">☰</button><nav class="nav">
<a href="dashboard.php">Tableau de bord</a><a href="produits.php">Produits</a><a href="clients.php">Clients</a><a href="commandes.php">Commandes</a>
<?php if($_SESSION['user']['role']==='admin'): ?><a href="utilisateurs.php">Utilisateurs</a><?php endif; ?>
<span class="user-chip"><?=h($_SESSION['user']['nom'])?> · <?=h($_SESSION['user']['role'])?></span><a class="logout" href="logout.php">Déconnexion</a></nav><?php endif; ?></header>
<main class="container">
<?php if(!empty($_SESSION['flash'])): $f=$_SESSION['flash']; unset($_SESSION['flash']); ?><div class="alert <?=h($f['type'])?>"><?=h($f['message'])?></div><?php endif; ?>
