<?php
/** Formulaire création / modification d'un produit. */
require 'config.php';

$id = $_GET['id'] ?? null;
$prod = ['designProd' => '', 'qteStock' => '', 'prixUProd' => '', 'photoProd' => ''];
if ($id !== null) {
    $stmt = $pdo->prepare('SELECT * FROM Produit WHERE idProd = ?');
    $stmt->execute([$id]);
    $prod = $stmt->fetch() ?: exit('Produit introuvable.');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $id ? 'Modifier' : 'Nouveau' ?> produit – GestCommandes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'includes/navbar.php'; ?>
<div class="container" style="max-width:620px">
  <h2 class="h4 mb-3"><i class="bi bi-box me-2"></i><?= $id ? 'Modifier le' : 'Nouveau' ?> produit</h2>
  <form method="post" action="produit_save.php" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm" id="formProd" novalidate>
    <input type="hidden" name="mode" value="<?= $id ? 'update' : 'create' ?>">
    <?php if ($id): ?><input type="hidden" name="idProd" value="<?= h($id) ?>"><?php endif; ?>

    <div class="mb-3">
      <label class="form-label" for="designProd">Désignation <span class="text-danger">*</span></label>
      <input class="form-control" id="designProd" name="designProd" value="<?= h($prod['designProd']) ?>" required>
      <div class="invalid-feedback">La désignation est obligatoire.</div>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-6">
        <label class="form-label" for="qteStock">Quantité en stock <span class="text-danger">*</span></label>
        <input type="number" min="0" class="form-control" id="qteStock" name="qteStock" value="<?= h((string)$prod['qteStock']) ?>" required>
        <div class="invalid-feedback">Quantité invalide.</div>
      </div>
      <div class="col-6">
        <label class="form-label" for="prixUProd">Prix unitaire (FCFA) <span class="text-danger">*</span></label>
        <input type="number" min="0" step="1" class="form-control" id="prixUProd" name="prixUProd" value="<?= h((string)$prod['prixUProd']) ?>" required>
        <div class="invalid-feedback">Prix invalide.</div>
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label" for="photoProd">Photo du produit</label>
      <?php if ($prod['photoProd']): ?>
        <div class="mb-2"><img src="uploads/<?= h($prod['photoProd']) ?>" alt="" class="rounded" width="80"></div>
      <?php endif; ?>
      <input type="file" class="form-control" id="photoProd" name="photoProd" accept="image/*">
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
      <a class="btn btn-outline-secondary" href="produits.php">Annuler</a>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('formProd').addEventListener('submit', function(e) {
  if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
  this.classList.add('was-validated');
});
</script>
</body>
</html>
