<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$produit = $id ? q1('SELECT * FROM Produit WHERE idProd = ?', [$id]) : null;
if ($id && !$produit) { flash('err', 'Produit introuvable.'); redirect('produits.php'); }

$errors = [];
$data = $produit ?: ['designProd' => '', 'qteStock' => 0, 'prixUProd' => '', 'photoProd' => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $data['designProd'] = trim($_POST['designProd'] ?? '');
    $data['qteStock']   = $_POST['qteStock'] ?? '';
    $data['prixUProd']  = $_POST['prixUProd'] ?? '';

    if ($data['designProd'] === '' || mb_strlen($data['designProd']) > 120) $errors[] = 'La désignation est obligatoire (120 caractères maximum).';
    if (filter_var($data['qteStock'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) $errors[] = 'Le stock doit être un entier positif ou nul.';
    if (!is_numeric($data['prixUProd']) || (float)$data['prixUProd'] < 0) $errors[] = 'Le prix unitaire doit être un nombre positif ou nul.';

    // Photo (optionnelle) : JPEG, PNG ou WebP, 2 Mo maximum, type vérifié sur le contenu réel
    $newPhoto = null;
    if (!empty($_FILES['photoProd']['name']) && $_FILES['photoProd']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['photoProd'];
        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = $f['error'] === UPLOAD_ERR_OK ? (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) : null;
        if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > 2 * 1024 * 1024) $errors[] = 'La photo n\'a pas pu être envoyée (2 Mo maximum).';
        elseif (!isset($ext[$mime])) $errors[] = 'La photo doit être au format JPEG, PNG ou WebP.';
        else $newPhoto = 'prod_' . bin2hex(random_bytes(8)) . '.' . $ext[$mime];
    }

    if (!$errors) {
        if ($newPhoto) {
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
            if (move_uploaded_file($_FILES['photoProd']['tmp_name'], UPLOAD_DIR . $newPhoto)) {
                if (!empty($produit['photoProd'])) @unlink(UPLOAD_DIR . basename($produit['photoProd']));
                $data['photoProd'] = $newPhoto;
            } else { $errors[] = 'Impossible d\'enregistrer la photo (droits du dossier uploads/).'; }
        }
    }
    if (!$errors) {
        if ($id) {
            run('UPDATE Produit SET designProd = ?, qteStock = ?, prixUProd = ?, photoProd = ? WHERE idProd = ?',
                [$data['designProd'], (int)$data['qteStock'], $data['prixUProd'], $data['photoProd'], $id]);
            flash('ok', 'Produit « ' . $data['designProd'] . ' » modifié.');
        } else {
            run('INSERT INTO Produit (designProd, qteStock, prixUProd, photoProd) VALUES (?, ?, ?, ?)',
                [$data['designProd'], (int)$data['qteStock'], $data['prixUProd'], $data['photoProd']]);
            flash('ok', 'Produit « ' . $data['designProd'] . ' » ajouté.');
        }
        redirect('produits.php');
    }
}

$pageTitle = $id ? 'Modifier un produit' : 'Ajouter un produit';
require __DIR__ . '/includes/header.php';
?>
<h1><?= h($pageTitle) ?></h1>
<p class="sub"><?= $id ? 'Les changements s\'appliquent à toutes les futures commandes. Les commandes passées gardent leurs quantités.' : 'Renseignez la fiche du nouveau produit.' ?></p>

<?php foreach ($errors as $e): ?><div class="flash flash-err" role="alert"><?= h($e) ?></div><?php endforeach; ?>

<form class="card" method="post" enctype="multipart/form-data" style="max-width:560px">
  <?= csrf_field() ?>
  <div class="field"><label for="designProd">Désignation</label>
    <input id="designProd" name="designProd" required maxlength="120" value="<?= h($data['designProd']) ?>"></div>
  <div class="field"><label for="prixUProd">Prix unitaire (<?= h(CURRENCY) ?>)</label>
    <input id="prixUProd" name="prixUProd" type="number" min="0" step="0.01" required value="<?= h($data['prixUProd']) ?>"></div>
  <div class="field"><label for="qteStock">Quantité en stock</label>
    <input id="qteStock" name="qteStock" type="number" min="0" step="1" required value="<?= h($data['qteStock']) ?>"></div>
  <div class="field"><label for="photoProd">Photo du produit</label>
    <?php if (!empty($data['photoProd'])): ?><div class="who" style="margin-bottom:.5rem"><?= thumb($data['photoProd']) ?><span class="hint">Photo actuelle — choisissez un fichier pour la remplacer.</span></div><?php endif; ?>
    <input id="photoProd" name="photoProd" type="file" accept="image/jpeg,image/png,image/webp">
    <span class="hint">JPEG, PNG ou WebP, 2 Mo maximum.</span></div>
  <button class="btn" type="submit">Enregistrer</button>
  <a class="btn btn-ghost" href="produits.php">Annuler</a>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
