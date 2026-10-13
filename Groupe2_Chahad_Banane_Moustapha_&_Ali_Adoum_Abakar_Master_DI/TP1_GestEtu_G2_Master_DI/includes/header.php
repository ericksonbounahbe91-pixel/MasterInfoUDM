<?php
/**
 * =============================================================================
 *  includes/header.php — En-tête commun (doctype, navbar, toasts)
 * =============================================================================
 *  Variables facultatives attendues avant l'inclusion :
 *    $titre_page  : titre de l'onglet du navigateur
 *    $page_active : clé du lien à surligner dans la barre de navigation
 * =============================================================================
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$titre_page  = $titre_page  ?? 'Tableau de bord';
$page_active = $page_active ?? 'etudiants';
$flashs      = lire_flash();

/** Petit utilitaire local : marque le lien courant dans la navbar. */
$lien_actif = static fn(string $cle): string => $cle === $page_active ? ' active' : '';
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Application de gestion des étudiants — Université de Moundou">
<title><?= h($titre_page) ?> — Gestion des étudiants</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
<script>
/* Applique le thème mémorisé AVANT le premier rendu : évite tout clignotement. */
(function () {
  var theme = localStorage.getItem('tp5-theme') || 'light';
  document.documentElement.setAttribute('data-bs-theme', theme);
})();
</script>
</head>
<body>

<a class="lien-evitement" href="#contenu">Aller au contenu principal</a>

<nav class="navbar navbar-expand-lg app-navbar sticky-top" aria-label="Navigation principale">
  <div class="container-xxl">
    <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
      <span class="marque-logo" aria-hidden="true"><i class="bi bi-mortarboard-fill"></i></span>
      <span>
        <strong class="d-block lh-1">Gestion des étudiants</strong>
        <small class="text-secondary lh-1"><?= h(ETABLISSEMENT) ?></small>
      </span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
            aria-controls="menuPrincipal" aria-expanded="false" aria-label="Ouvrir le menu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuPrincipal">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
        <li class="nav-item">
          <a class="nav-link<?= $lien_actif('etudiants') ?>" href="index.php">
            <i class="bi bi-people"></i> Étudiants</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= $lien_actif('nouveau') ?>" href="form.php">
            <i class="bi bi-person-plus"></i> Nouvel étudiant</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= $lien_actif('verification') ?>" href="verifier.php">
            <i class="bi bi-patch-check"></i> Vérification</a>
        </li>
        <li class="nav-item ms-lg-2">
          <button class="btn btn-outline-secondary btn-sm" id="bascule-theme" type="button"
                  aria-pressed="false" title="Basculer le mode sombre">
            <i class="bi bi-moon-stars" aria-hidden="true"></i>
            <span class="visually-hidden">Basculer le mode sombre</span>
          </button>
        </li>
      </ul>
    </div>
  </div>
</nav>

<?php /* ---- Conteneur des toasts : les messages flash y sont injectés ---- */ ?>
<div class="toast-container position-fixed top-0 end-0 p-3" id="zone-toasts" aria-live="polite">
<?php foreach ($flashs as $flash): ?>
  <div class="toast align-items-center text-bg-<?= h($flash['type']) ?> border-0" role="alert"
       data-bs-autohide="true" data-bs-delay="6000">
    <div class="d-flex">
      <div class="toast-body">
        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
        <?= h($flash['message']) ?>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
              aria-label="Fermer"></button>
    </div>
  </div>
<?php endforeach; ?>
</div>

<main class="container-xxl py-4" id="contenu">
