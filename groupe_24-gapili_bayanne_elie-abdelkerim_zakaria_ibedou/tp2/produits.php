<?php
require_once __DIR__ . '/inc/config.php';
$titre = 'Produits';

$recherche = trim((string) ($_GET['q'] ?? ''));
$categorie = in_array($_GET['cat'] ?? '', CATEGORIES, true) ? $_GET['cat'] : '';
$sql = 'SELECT idProd, designProd, categorieProd, uniteProd, qteStock, prixUProd, photoProd FROM cmd_produit WHERE 1=1';
$params = [];
if ($recherche !== '') {
    $sql .= ' AND designProd LIKE ?';
    $params[] = '%' . $recherche . '%';
}
if ($categorie !== '') {
    $sql .= ' AND categorieProd = ?';
    $params[] = $categorie;
}
$produits = q($sql . ' ORDER BY categorieProd, designProd', $params)->fetchAll();

require __DIR__ . '/inc/header.php';
?>
<div class="titre-page">
  <div><h1>Offres et services</h1><p class="sous-titre"><?= count($produits) ?> offre(s) <?= $recherche !== '' ? 'pour « ' . h($recherche) . ' »' : 'au catalogue' ?></p></div>
  <a class="bouton" href="produit.php"><?= icone('plus', 18) ?>Nouvelle offre</a>
</div>

<form method="get" class="recherche">
  <label for="q">Rechercher une offre</label>
  <input type="search" id="q" name="q" value="<?= h($recherche) ?>" placeholder="Désignation">
  <label for="cat">Catégorie</label>
  <select id="cat" name="cat">
    <option value="">Toutes</option>
    <?php foreach (CATEGORIES as $c): ?><option value="<?= h($c) ?>"<?= $c === $categorie ? ' selected' : '' ?>><?= h($c) ?></option><?php endforeach; ?>
  </select>
  <button type="submit" class="secondaire">Rechercher</button>
</form>

<?php if (!$produits): ?>
  <p class="vide-texte">Aucune offre trouvée. <a href="produit.php">Ajouter une offre</a>.</p>
<?php else: ?>
<div class="tableau-conteneur">
<table>
  <thead><tr><th scope="col"></th><th scope="col">N°</th><th scope="col">Désignation</th><th scope="col">Catégorie</th><th scope="col" class="num">Disponibilité</th><th scope="col" class="num">Tarif</th><th scope="col"></th></tr></thead>
  <tbody>
  <?php foreach ($produits as $p): ?>
    <tr>
      <td><?= vignette($p['photoProd'], $p['designProd']) ?></td>
      <td><?= (int) $p['idProd'] ?></td>
      <td><?= h($p['designProd']) ?></td>
      <td><span class="etiquette <?= (['Logiciels' => 'violet', 'Matériel' => 'cyan', 'Services' => 'vert'][$p['categorieProd']] ?? 'bleu') ?>"><?= h($p['categorieProd']) ?></span></td>
      <td class="num"><?= (int) $p['qteStock'] ?>
        <?php if ($p['qteStock'] <= 0): ?><span class="etiquette rouge">Indisponible</span>
        <?php elseif ($p['qteStock'] <= SEUIL_STOCK): ?><span class="etiquette ambre">Limité</span><?php endif; ?>
      </td>
      <td class="num"><?= h(fcfa($p['prixUProd'])) ?> <small>/ <?= h($p['uniteProd']) ?></small></td>
      <td class="actions"><a class="lien-action" href="produit.php?id=<?= (int) $p['idProd'] ?>"><?= icone('modifier', 16) ?>Modifier</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/inc/footer.php'; ?>
