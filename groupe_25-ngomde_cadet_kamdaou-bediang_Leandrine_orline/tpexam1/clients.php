<?php
/** Liste des clients. */
require 'config.php';

$q = trim($_GET['q'] ?? '');
$stmt = $pdo->prepare("
    SELECT c.*, r.nomRegion, d.nomDep,
           COUNT(DISTINCT cmd.idCmd) AS nbCmd
    FROM Client c
    JOIN Region r ON r.idRegion = c.idRegion
    JOIN Departement d ON d.idDep = r.idDep
    LEFT JOIN Commande cmd ON cmd.idCli = c.idCli
    WHERE c.nomCli LIKE :q OR c.prenomCli LIKE :q
    GROUP BY c.idCli
    ORDER BY c.nomCli
");
$stmt->execute([':q' => "%$q%"]);
$clients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clients – GestCommandes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'includes/navbar.php'; ?>
<div class="container">
  <?= showFlash() ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0"><i class="bi bi-people me-2"></i>Clients</h2>
    <a class="btn btn-primary" href="client_form.php"><i class="bi bi-plus-lg me-1"></i>Nouveau client</a>
  </div>

  <form class="row g-2 mb-3" method="get">
    <div class="col-8 col-md-4"><input class="form-control" name="q" placeholder="Rechercher un client..." value="<?= h($q) ?>"></div>
    <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button></div>
  </form>

  <div class="table-responsive">
    <table class="table table-striped bg-white align-middle shadow-sm rounded">
      <thead class="table-primary">
        <tr><th>ID</th><th>Photo</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Région</th><th>Dept</th><th>Cmd</th><th></th></tr>
      </thead>
      <tbody>
      <?php foreach ($clients as $c): ?>
        <tr>
          <td><?= $c['idCli'] ?></td>
          <td>
            <?php if ($c['photo']): ?>
              <img src="uploads/<?= h($c['photo']) ?>" alt="" width="40" height="40" class="rounded-circle" style="object-fit:cover">
            <?php else: ?>
              <i class="bi bi-person-circle" style="font-size:1.5rem;color:#adb5bd"></i>
            <?php endif; ?>
          </td>
          <td class="fw-semibold"><?= h($c['nomCli']) ?></td>
          <td><?= h($c['prenomCli']) ?></td>
          <td><?= h($c['email']) ?></td>
          <td><?= h($c['nomRegion']) ?></td>
          <td><?= h($c['nomDep']) ?></td>
          <td><span class="badge bg-info"><?= $c['nbCmd'] ?></span></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="client_form.php?id=<?= $c['idCli'] ?>"><i class="bi bi-pencil"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$clients): ?><tr><td colspan="9" class="text-center text-muted py-4">Aucun client trouvé.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
