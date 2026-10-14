<?php
require_once __DIR__ . '/inc/config.php';

$id = (int) ($_GET['id'] ?? 0);
$produit = ['designProd' => '', 'qteStock' => 0, 'prixUProd' => 0, 'photoProd' => null];
if ($id > 0) {
    $trouve = q('SELECT designProd, qteStock, prixUProd, photoProd FROM Produit WHERE idProd = ?', [$id])->fetch();
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
    $stock       = filter_var($_POST['qteStock'] ?? '', FILTER_VALIDATE_INT);
    $prix        = filter_var(str_replace(',', '.', (string) ($_POST['prixUProd'] ?? '')), FILTER_VALIDATE_FLOAT);

    if ($designation === '' || mb_strlen($designation) > 150) {
        $erreurs[] = 'Saisissez une désignation (150 caractères maximum).';
    }
    if ($stock === false || $stock < 0) {
        $erreurs[] = 'Le stock doit être un nombre entier supérieur ou égal à 0.';
    }
    if ($prix === false || $prix < 0) {
        $erreurs[] = 'Le prix unitaire doit être un nombre supérieur ou égal à 0.';
    }
    $produit = ['designProd' => $designation, 'qteStock' => $stock, 'prixUProd' => $prix, 'photoProd' => $produit['photoProd']];

    if (!$erreurs) {
        try {
            $photo = televerser_photo('photoProd', $produit['photoProd']);
            if ($id > 0) {
                q('UPDATE Produit SET designProd = ?, qteStock = ?, prixUProd = ?, photoProd = ? WHERE idProd = ?',
                  [$designation, $stock, $prix, $photo, $id]);
                flash('ok', 'Produit modifié.');
            } else {
                q('INSERT INTO Produit (designProd, qteStock, prixUProd, photoProd) VALUES (?, ?, ?, ?)',
                  [$designation, $stock, $prix, $photo]);
                flash('ok', 'Produit ajouté.');
            }
            redirect('produits.php');
        } catch (RuntimeException $e) {
            $erreurs[] = $e->getMessage();
        }
    }
}

$titre = $id > 0 ? 'Modifier le produit' : 'Nouveau produit';
require __DIR__ . '/inc/header.php';
?>
<div class="titre-page"><h1><?= h($titre) ?></h1></div>

<?php foreach ($erreurs as $e): ?><p class="message erreur" role="alert"><?= h($e) ?></p><?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="formulaire">
  <?= csrf_field() ?>
  <div class="champ">
    <label for="designProd">Désignation</label>
    <input type="text" id="designProd" name="designProd" maxlength="150" required value="<?= h($produit['designProd']) ?>">
  </div>
  <div class="champ-deux">
    <div class="champ">
      <label for="qteStock">Quantité en stock</label>
      <input type="number" id="qteStock" name="qteStock" min="0" step="1" required value="<?= h($produit['qteStock']) ?>">
    </div>
    <div class="champ">
      <label for="prixUProd">Prix unitaire (FCFA)</label>
      <input type="number" id="prixUProd" name="prixUProd" min="0" step="any" required value="<?= h($produit['prixUProd']) ?>">
    </div>
  </div>
  <div class="champ">
    <label for="photoProd">Photo (JPG, PNG ou WebP, 2 Mo maximum)</label>
    <?php if ($produit['photoProd']): ?><div class="photo-actuelle"><?= vignette($produit['photoProd'], $produit['designProd']) ?> <span>Photo actuelle. Choisissez un fichier pour la remplacer.</span></div><?php endif; ?>
    <input type="file" id="photoProd" name="photoProd" accept="image/jpeg,image/png,image/webp">
  </div>
  <div class="barre-actions">
    <button type="submit"><?= $id > 0 ? 'Enregistrer les modifications' : 'Ajouter le produit' ?></button>
    <a class="bouton secondaire" href="produits.php">Annuler</a>
  </div>
</form>
<?php require __DIR__ . '/inc/footer.php'; ?>
