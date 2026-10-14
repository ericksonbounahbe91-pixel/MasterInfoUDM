<?php
$pageTitle = 'Produits';
require __DIR__ . '/includes/header.php';

$search = trim($_GET['q'] ?? '');
$cols = ['designProd' => 'p.designProd', 'qteStock' => 'p.qteStock', 'prixUProd' => 'p.prixUProd', 'vendu' => 'vendu'];
$sort = array_key_exists($_GET['sort'] ?? '', $cols) ? $_GET['sort'] : 'designProd';
$dir  = ($_GET['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

$sql = 'SELECT p.idProd, p.designProd, p.qteStock, p.prixUProd, p.photoProd, COALESCE(SUM(l.qteCmd),0) AS vendu
        FROM Produit p LEFT JOIN ligneCommande l ON l.idProd = p.idProd';
$params = [];
if ($search !== '') { $sql .= ' WHERE p.designProd LIKE ?'; $params[] = "%$search%"; }
$sql .= " GROUP BY p.idProd, p.designProd, p.qteStock, p.prixUProd, p.photoProd ORDER BY {$cols[$sort]} $dir";
$produits = q($sql, $params);

function sort_link(string $col, string $label, string $sort, string $dir, string $search): string {
    $nd = ($sort === $col && $dir === 'ASC') ? 'desc' : 'asc';
    $arrow = $sort === $col ? ($dir === 'ASC' ? ' ↑' : ' ↓') : '';
    return '<a href="?' . h(http_build_query(['q' => $search, 'sort' => $col, 'dir' => $nd])) . '">' . h($label) . $arrow . '</a>';
}
?>
<div class="bar">
  <div class="grow"><h1>Produits</h1><p class="sub" style="margin:0"><?= count($produits) ?> produit(s) au catalogue.</p></div>
  <a class="btn" href="produit_form.php">Ajouter un produit</a>
</div>

<form class="bar" method="get">
  <div class="grow"><label for="q">Rechercher un produit</label><input id="q" name="q" value="<?= h($search) ?>" placeholder="Désignation" style="width:100%"></div>
  <button class="btn" type="submit">Rechercher</button>
  <?php if ($search !== ''): ?><a class="btn btn-ghost" href="produits.php">Effacer</a><?php endif; ?>
</form>

<div class="card tablewrap">
<?php if (!$produits): ?><p class="empty">Aucun produit trouvé.</p><?php else: ?>
<table>
  <thead><tr>
    <th>Produit</th>
    <th class="num"><?= sort_link('prixUProd', 'Prix unitaire', $sort, $dir, $search) ?></th>
    <th class="num"><?= sort_link('qteStock', 'Stock', $sort, $dir, $search) ?></th>
    <th class="num"><?= sort_link('vendu', 'Unités vendues', $sort, $dir, $search) ?></th>
    <th></th>
  </tr></thead>
  <tbody>
  <?php foreach ($produits as $p): ?>
    <tr>
      <td><div class="who"><?= thumb($p['photoProd']) ?><span><?= h($p['designProd']) ?></span></div></td>
      <td class="num"><?= money($p['prixUProd']) ?></td>
      <td class="num">
        <?= (int)$p['qteStock'] ?>
        <?php if ($p['qteStock'] == 0): ?><span class="tag tag-out">Rupture</span>
        <?php elseif ($p['qteStock'] <= STOCK_ALERT): ?><span class="tag tag-low">Stock bas</span><?php endif; ?>
      </td>
      <td class="num"><?= (int)$p['vendu'] ?></td>
      <td class="num"><a href="produit_form.php?id=<?= (int)$p['idProd'] ?>">Modifier</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
