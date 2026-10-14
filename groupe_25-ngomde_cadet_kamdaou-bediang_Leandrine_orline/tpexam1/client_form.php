<?php
/** Formulaire création / modification d'un client. */
require 'config.php';

$id = $_GET['id'] ?? null;
$cli = ['nomCli' => '', 'prenomCli' => '', 'email' => '', 'photo' => '', 'idRegion' => ''];
if ($id !== null) {
    $stmt = $pdo->prepare('SELECT * FROM Client WHERE idCli = ?');
    $stmt->execute([$id]);
    $cli = $stmt->fetch() ?: exit('Client introuvable.');
}
$regions = $pdo->query('SELECT r.idRegion, r.nomRegion, d.nomDep FROM Region r JOIN Departement d ON d.idDep = r.idDep ORDER BY d.nomDep, r.nomRegion')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $id ? 'Modifier' : 'Nouveau' ?> client – GestCommandes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'includes/navbar.php'; ?>
<div class="container" style="max-width:620px">
  <h2 class="h4 mb-3"><i class="bi bi-person-plus me-2"></i><?= $id ? 'Modifier le' : 'Nouveau' ?> client</h2>
  <form method="post" action="client_save.php" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm" id="formCli" novalidate>
    <input type="hidden" name="mode" value="<?= $id ? 'update' : 'create' ?>">
    <?php if ($id): ?><input type="hidden" name="idCli" value="<?= h($id) ?>"><?php endif; ?>

    <div class="row g-3 mb-3">
      <div class="col-6">
        <label class="form-label" for="nomCli">Nom <span class="text-danger">*</span></label>
        <input class="form-control" id="nomCli" name="nomCli" value="<?= h($cli['nomCli']) ?>" required>
      </div>
      <div class="col-6">
        <label class="form-label" for="prenomCli">Prénom <span class="text-danger">*</span></label>
        <input class="form-control" id="prenomCli" name="prenomCli" value="<?= h($cli['prenomCli']) ?>" required>
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
      <input type="email" class="form-control" id="email" name="email" value="<?= h($cli['email']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label" for="idRegion">Région <span class="text-danger">*</span></label>
      <select class="form-select" id="idRegion" name="idRegion" required>
        <option value="">-- Choisir --</option>
        <?php foreach ($regions as $r): ?>
          <option value="<?= $r['idRegion'] ?>" <?= ($cli['idRegion'] == $r['idRegion']) ? 'selected' : '' ?>>
            <?= h($r['nomRegion']) ?> (<?= h($r['nomDep']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label" for="photo">Photo</label>
      <?php if ($cli['photo']): ?>
        <div class="mb-2"><img src="uploads/<?= h($cli['photo']) ?>" alt="" class="rounded-circle" width="60"></div>
      <?php endif; ?>
      <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
      <a class="btn btn-outline-secondary" href="clients.php">Annuler</a>
    </div>
  </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('formCli').addEventListener('submit', function(e) {
  if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
  this.classList.add('was-validated');
});
</script>
</body>
</html>
