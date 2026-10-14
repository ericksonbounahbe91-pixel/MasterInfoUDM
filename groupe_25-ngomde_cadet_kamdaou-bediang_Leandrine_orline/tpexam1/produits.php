<?php
/** Liste des produits avec recherche. */
require 'config.php';

$q = trim($_GET['q'] ?? '');
$stmt = $pdo->prepare("SELECT * FROM Produit WHERE designProd LIKE :q ORDER BY designProd");
$stmt->execute([':q' => "%$q%"]);
$produits = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Produits – GestCommandes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'includes/navbar.php'; ?>
<div class="container">
  <?= showFlash() ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0"><i class="bi bi-box me-2"></i>Produits</h2>
    <a class="btn btn-primary" href="produit_form.php"><i class="bi bi-plus-lg me-1"></i>Nouveau produit</a>
  </div>

  <form class="row g-2 mb-3" method="get">
    <div class="col-8 col-md-4"><input class="form-control" name="q" placeholder="Rechercher un produit..." value="<?= h($q) ?>"></div>
    <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button></div>
  </form>

  <div class="table-responsive">
    <table class="table table-striped bg-white align-middle shadow-sm rounded">
      <thead class="table-primary">
        <tr><th>ID</th><th>Photo</th><th>Désignation</th><th class="text-center">Stock</th><th class="text-end">Prix unitaire</th><th></th></tr>
      </thead>
      <tbody>
      <?php foreach ($produits as $p): ?>
        <tr>
          <td><?= $p['idProd'] ?></td>
          <td>
            <?php if ($p['photoProd']): ?>
              <img src="uploads/<?= h($p['photoProd']) ?>" alt="" width="48" height="48" class="rounded" style="object-fit:cover">
            <?php else: ?>
              <span class="text-muted"><i class="bi bi-image" style="font-size:1.5rem"></i></span>
            <?php endif; ?>
          </td>
          <td class="fw-semibold"><?= h($p['designProd']) ?></td>
          <td class="text-center">
            <span class="badge <?= $p['qteStock'] <= 10 ? ($p['qteStock'] == 0 ? 'bg-danger' : 'bg-warning text-dark') : 'bg-success' ?>">
              <?= $p['qteStock'] ?>
            </span>
          </td>
          <td class="text-end"><?= number_format($p['prixUProd'], 0, ',', ' ') ?> F</td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="produit_form.php?id=<?= $p['idProd'] ?>"><i class="bi bi-pencil"></i></a>
            <form method="post" action="produit_delete.php" class="d-inline" onsubmit="return confirm('Supprimer ce produit ?');">
              <input type="hidden" name="idProd" value="<?= $p['idProd'] ?>">
              <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$produits): ?><tr><td colspan="6" class="text-center text-muted py-4">Aucun produit trouvé.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
