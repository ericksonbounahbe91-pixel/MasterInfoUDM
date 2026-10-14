<?php
/** Formulaire d'enregistrement des produits d'une commande. */
require 'config.php';

$clients  = $pdo->query("SELECT idCli, CONCAT(prenomCli, ' ', nomCli) AS nom FROM Client ORDER BY nomCli")->fetchAll();
$produits = $pdo->query("SELECT idProd, designProd, prixUProd, qteStock FROM Produit WHERE qteStock > 0 ORDER BY designProd")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nouvelle commande – GestCommandes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .ligne-produit { transition: all .2s; }
    .ligne-produit:hover { background: #f0f4ff; }
    #totalCommande { font-size: 1.3rem; }
  </style>
</head>
<body class="bg-light">
<?php include 'includes/navbar.php'; ?>
<div class="container" style="max-width:820px">
  <h2 class="h4 mb-3"><i class="bi bi-cart-plus me-2"></i>Nouvelle commande</h2>

  <form method="post" action="commande_save.php" class="bg-white p-4 rounded shadow-sm" id="formCmd">
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label class="form-label fw-semibold" for="idCli">Client <span class="text-danger">*</span></label>
        <select class="form-select" id="idCli" name="idCli" required>
          <option value="">-- Choisir un client --</option>
          <?php foreach ($clients as $c): ?>
            <option value="<?= $c['idCli'] ?>"><?= h($c['nom']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold" for="dateCmd">Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="dateCmd" name="dateCmd" value="<?= date('Y-m-d') ?>" required>
      </div>
    </div>

    <h5 class="mb-3"><i class="bi bi-list-check me-2"></i>Lignes de commande</h5>
    <div id="lignes">
      <div class="row g-2 mb-2 align-items-end ligne-produit p-2 rounded">
        <div class="col-md-5">
          <label class="form-label small">Produit</label>
          <select class="form-select produit-select" name="produits[]" required>
            <option value="">-- Produit --</option>
            <?php foreach ($produits as $p): ?>
              <option value="<?= $p['idProd'] ?>" data-prix="<?= $p['prixUProd'] ?>" data-stock="<?= $p['qteStock'] ?>">
                <?= h($p['designProd']) ?> (<?= number_format($p['prixUProd'], 0, ',', ' ') ?> F | stock: <?= $p['qteStock'] ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small">Quantité</label>
          <input type="number" class="form-control qte-input" name="quantites[]" min="1" value="1" required>
        </div>
        <div class="col-md-3">
          <label class="form-label small">Sous-total</label>
          <input type="text" class="form-control sous-total" readonly value="0 F">
        </div>
        <div class="col-md-2">
          <button type="button" class="btn btn-outline-danger btn-sm suppr-ligne" title="Supprimer"><i class="bi bi-x-lg"></i></button>
        </div>
      </div>
    </div>

    <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="ajouterLigne">
      <i class="bi bi-plus-lg me-1"></i>Ajouter un produit
    </button>

    <hr>
    <div class="d-flex justify-content-between align-items-center">
      <div id="totalCommande" class="fw-bold text-primary">Total : 0 F</div>
      <div class="d-flex gap-2">
        <button class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>Valider la commande</button>
        <a class="btn btn-outline-secondary" href="commandes.php">Annuler</a>
      </div>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const lignesContainer = document.getElementById('lignes');
const templateLigne = lignesContainer.querySelector('.ligne-produit').cloneNode(true);

document.getElementById('ajouterLigne').addEventListener('click', () => {
  const clone = templateLigne.cloneNode(true);
  clone.querySelector('.produit-select').value = '';
  clone.querySelector('.qte-input').value = 1;
  clone.querySelector('.sous-total').value = '0 F';
  lignesContainer.appendChild(clone);
  attachEvents(clone);
});

function calcul() {
  let total = 0;
  lignesContainer.querySelectorAll('.ligne-produit').forEach(row => {
    const sel = row.querySelector('.produit-select');
    const opt = sel.options[sel.selectedIndex];
    const prix = parseFloat(opt?.dataset?.prix || 0);
    const qte = parseInt(row.querySelector('.qte-input').value) || 0;
    const st = prix * qte;
    row.querySelector('.sous-total').value = st.toLocaleString('fr') + ' F';
    total += st;
  });
  document.getElementById('totalCommande').textContent = 'Total : ' + total.toLocaleString('fr') + ' F';
}

function attachEvents(row) {
  row.querySelector('.produit-select').addEventListener('change', calcul);
  row.querySelector('.qte-input').addEventListener('input', calcul);
  row.querySelector('.suppr-ligne').addEventListener('click', () => {
    if (lignesContainer.querySelectorAll('.ligne-produit').length > 1) {
      row.remove();
      calcul();
    }
  });
}

lignesContainer.querySelectorAll('.ligne-produit').forEach(attachEvents);
</script>
</body>
</html>
