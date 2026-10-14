<?php
/** Barre de navigation partagée. */
$currentPage = basename($_SERVER['PHP_SELF']);
function navActive(string $page): string {
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">
      <i class="bi bi-box-seam me-2"></i>GestCommandes
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link <?= navActive('index.php') ?>" href="index.php"><i class="bi bi-speedometer2 me-1"></i>Tableau de bord</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('produits.php') ?>" href="produits.php"><i class="bi bi-box me-1"></i>Produits</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('clients.php') ?>" href="clients.php"><i class="bi bi-people me-1"></i>Clients</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('commandes.php') ?>" href="commandes.php"><i class="bi bi-cart3 me-1"></i>Commandes</a></li>
      </ul>
      <a class="btn btn-outline-light btn-sm" href="commande_form.php"><i class="bi bi-plus-lg me-1"></i>Nouvelle commande</a>
    </div>
  </div>
</nav>
