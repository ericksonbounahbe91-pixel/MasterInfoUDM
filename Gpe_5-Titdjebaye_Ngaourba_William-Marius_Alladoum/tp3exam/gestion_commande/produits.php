<?php
require_once __DIR__ . '/inc/config.php';
$titre = 'Produits';

$recherche = trim((string) ($_GET['q'] ?? ''));
$sql = 'SELECT idProd, designProd, qteStock, prixUProd, photoProd FROM Produit';
$params = [];
if ($recherche !== '') {
    $sql .= ' WHERE designProd LIKE ?';
    $params[] = '%' . $recherche . '%';
}
$produits = q($sql . ' ORDER BY designProd', $params)->fetchAll();

require __DIR__ . '/inc/header.php';
?>
<div class="titre-page">
  <h1>Produits</h1>
  <a class="bouton" href="produit.php">Nouveau produit</a>
</div>

<form method="get" class="recherche">
  <label for="q">Rechercher un produit</label>
  <input type="search" id="q" name="q" value="<?= h($recherche) ?>" placeholder="Désignation">
  <button type="submit" class="secondaire">Rechercher</button>
</form>

<?php if (!$produits): ?>
  <p class="vide-texte">Aucun produit trouvé. <a href="produit.php">Ajouter un produit</a>.</p>
<?php else: ?>
<div class="tableau-conteneur">
<table>
  <thead><tr><th scope="col"></th><th scope="col">N°</th><th scope="col">Désignation</th><th scope="col" class="num">Stock</th><th scope="col" class="num">Prix unitaire</th><th scope="col"></th></tr></thead>
  <tbody>
  <?php foreach ($produits as $p): ?>
    <tr>
      <td><?= vignette($p['photoProd'], $p['designProd']) ?></td>
      <td><?= (int) $p['idProd'] ?></td>
      <td><?= h($p['designProd']) ?></td>
      <td class="num"><?= (int) $p['qteStock'] ?>
        <?php if ($p['qteStock'] <= 0): ?><span class="etiquette rouge">Rupture</span>
        <?php elseif ($p['qteStock'] <= SEUIL_STOCK): ?><span class="etiquette ambre">Stock bas</span><?php endif; ?>
      </td>
      <td class="num"><?= h(fcfa($p['prixUProd'])) ?></td>
      <td class="actions"><a href="produit.php?id=<?= (int) $p['idProd'] ?>">Modifier</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/inc/footer.php'; ?>
