<?php
/** TP5 – READ : liste des étudiants (méthode GET, recherche facultative via ?q=). */
require 'config.php';

$q = trim($_GET['q'] ?? '');
$stmt = $pdo->prepare(
    'SELECT idEtu, nomEtu, prenomEtu, emailEtu, mentionEtu, cycleEtu
       FROM etudiant
      WHERE nomEtu LIKE :q OR prenomEtu LIKE :q
      ORDER BY nomEtu'
);
$stmt->execute([':q' => "%$q%"]);
$etudiants = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestion des étudiants</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">  
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</head>
<body class="bg-light">

  <header class="site-header">
    <div class="container1 header-inner">
      <a href="#" class="logo" style="font-size: 30px ;">Université <span >De Moundou</span></a>
      <nav aria-label="Navigation principale">
        <ul class="nav">
          <li><a href="#mpresident">Liste des étudiants</a></li>
          <li><a href="#admission">Enregistrement</a></li>
          <li><a href="#formations">Nos formations</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
      </nav>
    </div>
  </header>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 text-primary mb-0">Étudiants</h1>
    <a class="btn btn-primary" href="form.php">+ Nouvel étudiant</a>
  </div>

  <form class="row g-2 mb-3" method="get">
    <div class="col-8 col-md-4"><input class="form-control" name="q" placeholder="Rechercher un nom…" value="<?= h($q) ?>"></div>
    <div class="col-auto"><button class="btn btn-outline-secondary">Rechercher</button></div>
  </form>

  <?php if (isset($_GET['ok'])): ?><div class="alert alert-success">Opération réussie.</div><?php endif; ?>
  <?php if (isset($_GET['err'])): ?><div class="alert alert-danger"><?= h($_GET['err']) ?></div><?php endif; ?>

  <div class="table-responsive">
    <table class="table table-striped bg-white align-middle">
      <thead><tr><th>Matricule</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Mention</th><th>Cycle</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($etudiants as $e): ?>
        <tr>
          <td><?= h($e['idEtu']) ?></td><td><?= h($e['nomEtu']) ?></td><td><?= h($e['prenomEtu']) ?></td>
          <td><?= h($e['emailEtu']) ?></td><td><?= h($e['mentionEtu']) ?></td><td><?= h($e['cycleEtu']) ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="form.php?id=<?= urlencode($e['idEtu']) ?>">Modifier</a>
            <!-- Suppression par POST (jamais par GET : une simple URL ne doit pas modifier les données) -->
            <form method="post" action="delete.php" class="d-inline" onsubmit="return confirm('Supprimer cet étudiant ?');">
              <input type="hidden" name="idEtu" value="<?= h($e['idEtu']) ?>">
              <button class="btn btn-sm btn-outline-danger">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$etudiants): ?><tr><td colspan="7" class="text-center text-muted">Aucun résultat.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
    <footer class="cl-login09__foot">
      <span>Copyrigth 2026</span>
    </footer>
</body>
</html>
