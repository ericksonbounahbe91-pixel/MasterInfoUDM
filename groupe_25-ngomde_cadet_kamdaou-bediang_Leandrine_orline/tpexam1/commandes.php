<?php
/** Liste des commandes. */
require 'config.php';

$commandes = $pdo->query("
    SELECT c.idCmd, c.dateCmd, CONCAT(cl.prenomCli, ' ', cl.nomCli) AS client,
           COUNT(lc.idProd) AS nbLignes,
           COALESCE(SUM(lc.qteCmd), 0) AS totalQte,
           COALESCE(SUM(lc.qteCmd * p.prixUProd), 0) AS montant
    FROM Commande c
    JOIN Client cl ON cl.idCli = c.idCli
    LEFT JOIN ligneCommande lc ON lc.idCmd = c.idCmd
    LEFT JOIN Produit p ON p.idProd = lc.idProd
    GROUP BY c.idCmd
    ORDER BY c.dateCmd DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Commandes – GestCommandes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'includes/navbar.php'; ?>
<div class="container">
  <?= showFlash() ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0"><i class="bi bi-cart3 me-2"></i>Commandes</h2>
    <a class="btn btn-primary" href="commande_form.php"><i class="bi bi-plus-lg me-1"></i>Nouvelle commande</a>
  </div>

  <div class="table-responsive">
    <table class="table table-striped bg-white align-middle shadow-sm rounded">
      <thead class="table-primary">
        <tr><th>#</th><th>Date</th><th>Client</th><th class="text-center">Produits</th><th class="text-center">Qté totale</th><th class="text-end">Montant</th><th></th></tr>
      </thead>
      <tbody>
      <?php foreach ($commandes as $c): ?>
        <tr>
          <td class="fw-semibold"><?= $c['idCmd'] ?></td>
          <td><?= h($c['dateCmd']) ?></td>
          <td><?= h($c['client']) ?></td>
          <td class="text-center"><span class="badge bg-secondary"><?= $c['nbLignes'] ?></span></td>
          <td class="text-center"><?= $c['totalQte'] ?></td>
          <td class="text-end fw-semibold"><?= number_format($c['montant'], 0, ',', ' ') ?> F</td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-info" href="commande_detail.php?id=<?= $c['idCmd'] ?>"><i class="bi bi-eye"></i></a>
            <form method="post" action="commande_delete.php" class="d-inline" onsubmit="return confirm('Supprimer cette commande et toutes ses lignes ?');">
              <input type="hidden" name="idCmd" value="<?= $c['idCmd'] ?>">
              <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$commandes): ?><tr><td colspan="7" class="text-center text-muted py-4">Aucune commande.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
