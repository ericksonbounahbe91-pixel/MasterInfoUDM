<?php
require_once __DIR__ . '/inc/config.php';

$id = (int) ($_GET['id'] ?? 0);
$produit = ['designProd' => '', 'categorieProd' => 'Logiciels', 'uniteProd' => 'unité', 'qteStock' => 0, 'prixUProd' => 0, 'photoProd' => null];
if ($id > 0) {
    $trouve = q('SELECT designProd, categorieProd, uniteProd, qteStock, prixUProd, photoProd FROM cmd_produit WHERE idProd = ?', [$id])->fetch();
    if (!$trouve) {
        flash('erreur', 'Produit introuvable.');
        redirect('produits.php');
    }
    $produit = $trouve;
}

$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    $designation = trim((string) ($_POST['designProd'] ?? ''));
    $cat         = (string) ($_POST['categorieProd'] ?? '');
    $unite       = trim((string) ($_POST['uniteProd'] ?? ''));
    $stock       = filter_var($_POST['qteStock'] ?? '', FILTER_VALIDATE_INT);
    $prix        = filter_var(str_replace(',', '.', (string) ($_POST['prixUProd'] ?? '')), FILTER_VALIDATE_FLOAT);

    if ($designation === '' || mb_strlen($designation) > 150) {
        $erreurs[] = 'Saisissez une désignation (150 caractères maximum).';
    }
    if (!in_array($cat, CATEGORIES, true)) {
        $erreurs[] = 'Choisissez une catégorie : Logiciels, Matériel ou Services.';
    }
    if ($unite === '' || mb_strlen($unite) > 30) {
        $erreurs[] = "Saisissez l'unité de facturation (mois, jour, trajet…).";
    }
    if ($stock === false || $stock < 0) {
        $erreurs[] = 'La disponibilité doit être un nombre entier supérieur ou égal à 0.';
    }
    if ($prix === false || $prix < 0) {
        $erreurs[] = 'Le prix unitaire doit être un nombre supérieur ou égal à 0.';
    }
    $produit = ['designProd' => $designation, 'categorieProd' => $cat, 'uniteProd' => $unite, 'qteStock' => $stock, 'prixUProd' => $prix, 'photoProd' => $produit['photoProd']];

    if (!$erreurs) {
        try {
            $photo = televerser_photo('photoProd', $produit['photoProd']);
            if ($id > 0) {
                q('UPDATE cmd_produit SET designProd = ?, categorieProd = ?, uniteProd = ?, qteStock = ?, prixUProd = ?, photoProd = ? WHERE idProd = ?',
                  [$designation, $cat, $unite, $stock, $prix, $photo, $id]);
                flash('ok', 'Offre modifiée.');
            } else {
                q('INSERT INTO cmd_produit (designProd, categorieProd, uniteProd, qteStock, prixUProd, photoProd) VALUES (?, ?, ?, ?, ?, ?)',
                  [$designation, $cat, $unite, $stock, $prix, $photo]);
                flash('ok', 'Offre ajoutée.');
            }
            redirect('produits.php');
        } catch (RuntimeException $e) {
            $erreurs[] = $e->getMessage();
        }
    }
}

$titre = $id > 0 ? 'Modifier l\'offre' : 'Nouvelle offre';
require __DIR__ . '/inc/header.php';
?>
<div class="titre-page"><div><h1><?= h($titre) ?></h1><p class="sous-titre">Renseignez la catégorie, la désignation, la disponibilité et le tarif.</p></div></div>

<?php foreach ($erreurs as $e): ?><p class="message erreur" role="alert"><?= h($e) ?></p><?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="formulaire">
  <?= csrf_field() ?>
  <div class="champ-deux">
    <div class="champ">
      <label for="categorieProd">Catégorie</label>
      <select id="categorieProd" name="categorieProd" required>
        <?php foreach (CATEGORIES as $c): ?><option value="<?= h($c) ?>"<?= $c === $produit['categorieProd'] ? ' selected' : '' ?>><?= h($c) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="champ">
      <label for="uniteProd">Unité de facturation</label>
      <input type="text" id="uniteProd" name="uniteProd" maxlength="30" required list="unites" value="<?= h($produit['uniteProd']) ?>">
      <datalist id="unites"><option value="licence"><option value="mois"><option value="an"><option value="unité"><option value="forfait"><option value="session"></datalist>
    </div>
  </div>
  <div class="champ">
    <label for="designProd">Désignation</label>
    <input type="text" id="designProd" name="designProd" maxlength="150" required value="<?= h($produit['designProd']) ?>">
  </div>
  <div class="champ-deux">
    <div class="champ">
      <label for="qteStock">Disponibilité (unités)</label>
      <input type="number" id="qteStock" name="qteStock" min="0" step="1" required value="<?= h($produit['qteStock']) ?>">
    </div>
    <div class="champ">
      <label for="prixUProd">Tarif par unité (FCFA)</label>
      <input type="number" id="prixUProd" name="prixUProd" min="0" step="any" required value="<?= h($produit['prixUProd']) ?>">
    </div>
  </div>
  <div class="champ">
    <label for="photoProd">Photo (JPG, PNG ou WebP, 2 Mo maximum)</label>
    <?php if ($produit['photoProd']): ?><div class="photo-actuelle"><?= vignette($produit['photoProd'], $produit['designProd']) ?> <span>Photo actuelle. Choisissez un fichier pour la remplacer.</span></div><?php endif; ?>
    <input type="file" id="photoProd" name="photoProd" accept="image/jpeg,image/png,image/webp">
  </div>
  <div class="barre-actions">
    <button type="submit"><?= icone('check', 18) ?><?= $id > 0 ? 'Enregistrer les modifications' : 'Ajouter le produit' ?></button>
    <a class="bouton secondaire" href="produits.php">Annuler</a>
  </div>
</form>
<?php require __DIR__ . '/inc/footer.php'; ?>
