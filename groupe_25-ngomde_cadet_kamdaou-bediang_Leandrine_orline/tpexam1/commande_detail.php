<?php
/** Détail d'une commande : lignes de commande avec possibilité de supprimer un produit. */
require 'config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: commandes.php'); exit; }

// Info commande
$cmd = $pdo->prepare("
    SELECT c.*, CONCAT(cl.prenomCli, ' ', cl.nomCli) AS client, cl.email
    FROM Commande c JOIN Client cl ON cl.idCli = c.idCli WHERE c.idCmd = ?
");
$cmd->execute([$id]);
$cmd = $cmd->fetch() ?: exit('Commande introuvable.');

// Lignes
$lignes = $pdo->prepare("
    SELECT lc.*, p.designProd, p.prixUProd, p.photoProd,
           (lc.qteCmd * p.prixUProd) AS sousTotal
    FROM ligneCommande lc
    JOIN Produit p ON p.idProd = lc.idProd
    WHERE lc.idCmd = ?
    ORDER BY p.designProd
");
$lignes->execute([$id]);
$lignes = $lignes->fetchAll();

$total = array_sum(array_column($lignes, 'sousTotal'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Commande #<?= $id ?> – GestCommandes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'includes/navbar.php'; ?>
<div class="container" style="max-width:850px">
  <?= showFlash() ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0"><i class="bi bi-receipt me-2"></i>Commande #<?= $id ?></h2>
    <a class="btn btn-outline-secondary" href="commandes.php"><i class="bi bi-arrow-left me-1"></i>Retour</a>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <div class="row">
        <div class="col-md-4"><strong>Client :</strong> <?= h($cmd['client']) ?></div>
        <div class="col-md-4"><strong>Email :</strong> <?= h($cmd['email']) ?></div>
        <div class="col-md-4"><strong>Date :</strong> <?= h($cmd['dateCmd']) ?></div>
      </div>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-striped bg-white align-middle shadow-sm rounded">
      <thead class="table-primary">
        <tr><th>Photo</th><th>Produit</th><th class="text-center">Qté</th><th class="text-end">Prix unit.</th><th class="text-end">Sous-total</th><th></th></tr>
      </thead>
      <tbody>
      <?php foreach ($lignes as $l): ?>
        <tr>
          <td>
            <?php if ($l['photoProd']): ?>
              <img src="uploads/<?= h($l['photoProd']) ?>" alt="" width="40" height="40" class="rounded" style="object-fit:cover">
            <?php else: ?>
              <i class="bi bi-image text-muted"></i>
            <?php endif; ?>
          </td>
          <td class="fw-semibold"><?= h($l['designProd']) ?></td>
          <td class="text-center"><?= $l['qteCmd'] ?></td>
          <td class="text-end"><?= number_format($l['prixUProd'], 0, ',', ' ') ?> F</td>
          <td class="text-end fw-semibold"><?= number_format($l['sousTotal'], 0, ',', ' ') ?> F</td>
          <td class="text-end">
            <form method="post" action="ligne_delete.php" class="d-inline" onsubmit="return confirm('Retirer ce produit de la commande ?');">
              <input type="hidden" name="idCmd" value="<?= $id ?>">
              <input type="hidden" name="idProd" value="<?= $l['idProd'] ?>">
              <button class="btn btn-sm btn-outline-danger" title="Supprimer cette ligne"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$lignes): ?><tr><td colspan="6" class="text-center text-muted py-4">Aucun produit dans cette commande.</td></tr><?php endif; ?>
      </tbody>
      <tfoot>
        <tr class="table-light">
          <td colspan="4" class="text-end fw-bold">Total :</td>
          <td class="text-end fw-bold fs-5 text-primary"><?= number_format($total, 0, ',', ' ') ?> F</td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
