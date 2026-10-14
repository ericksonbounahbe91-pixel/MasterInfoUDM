<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$cmd = q1('SELECT c.idCmd, c.dateCmd, cl.nomCli, cl.prenomCli, cl.email, cl.photo
           FROM Commande c JOIN Client cl ON cl.idCli = c.idCli WHERE c.idCmd = ?', [$id]);
if (!$cmd) { flash('err', 'Commande introuvable.'); redirect('commandes.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'add_lines') {
        [$ok, $msg] = add_lines($id, (array)($_POST['idProd'] ?? []), (array)($_POST['qteCmd'] ?? []));
        flash($ok ? 'ok' : 'err', $msg);
    } elseif ($action === 'delete_line') {
        [$ok, $msg] = delete_line($id, (int)($_POST['idProd'] ?? 0));
        flash($ok ? 'ok' : 'err', $msg);
    }
    redirect('commande.php?id=' . $id);
}

$pageTitle = 'Commande n° ' . $id;
require __DIR__ . '/includes/header.php';

$lignes = q('SELECT l.idProd, p.designProd, p.photoProd, p.prixUProd, p.qteStock, l.qteCmd, (l.qteCmd * p.prixUProd) AS montant
             FROM ligneCommande l JOIN Produit p ON p.idProd = l.idProd
             WHERE l.idCmd = ? ORDER BY p.designProd', [$id]);
$total = array_sum(array_column($lignes, 'montant'));
$produits = q('SELECT idProd, designProd, prixUProd, qteStock FROM Produit WHERE qteStock > 0 ORDER BY designProd');
?>
<p class="hint" style="margin:0 0 .5rem"><a href="commandes.php">Toutes les commandes</a></p>
<h1>Commande n° <?= $id ?></h1>
<div class="who sub" style="margin-bottom:1.5rem">
  <?= avatar($cmd['photo'], $cmd['prenomCli'] . ' ' . $cmd['nomCli']) ?>
  <span><?= h($cmd['prenomCli'] . ' ' . $cmd['nomCli']) ?> · <?= h($cmd['email']) ?> · passée le <?= h(date('d/m/Y', strtotime($cmd['dateCmd']))) ?></span>
</div>

<div class="card tablewrap">
  <h2>Produits de la commande</h2>
<?php if (!$lignes): ?>
  <p class="empty">Cette commande ne contient encore aucun produit. Utilisez le formulaire ci-dessous.</p>
<?php else: ?>
<table>
  <thead><tr><th>Produit</th><th class="num">Prix unitaire</th><th class="num">Quantité</th><th class="num">Montant</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($lignes as $l): ?>
    <tr>
      <td><div class="who"><?= thumb($l['photoProd']) ?><span><?= h($l['designProd']) ?></span></div></td>
      <td class="num"><?= money($l['prixUProd']) ?></td>
      <td class="num"><?= (int)$l['qteCmd'] ?></td>
      <td class="num"><?= money($l['montant']) ?></td>
      <td class="num">
        <form method="post" data-confirm="Retirer « <?= h($l['designProd']) ?> » de cette commande ?">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete_line">
          <input type="hidden" name="idProd" value="<?= (int)$l['idProd'] ?>">
          <button class="btn btn-danger" type="submit">Retirer</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot><tr><td colspan="3" class="num">Total</td><td class="num"><?= money($total) ?></td><td></td></tr></tfoot>
</table>
<?php endif; ?>
</div>

<form class="card" method="post">
  <h2>Enregistrer des produits dans cette commande</h2>
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="add_lines">
<?php if (!$produits): ?>
  <p class="empty">Aucun produit disponible en stock.</p>
<?php else: ?>
  <div class="form-row head"><label>Produit</label><label>Quantité</label><span></span></div>
  <div id="lignes">
    <div class="form-row">
      <select name="idProd[]" required aria-label="Produit">
        <option value="">Choisir un produit</option>
        <?php foreach ($produits as $p): ?>
          <option value="<?= (int)$p['idProd'] ?>"><?= h($p['designProd']) ?> — <?= money($p['prixUProd']) ?> (stock : <?= (int)$p['qteStock'] ?>)</option>
        <?php endforeach; ?>
      </select>
      <input type="number" name="qteCmd[]" min="1" value="1" required aria-label="Quantité">
      <button type="button" class="btn btn-danger rm" aria-label="Retirer cette ligne">Retirer</button>
    </div>
  </div>
  <p><button type="button" id="addRow" class="btn btn-ghost">Ajouter un autre produit</button></p>
  <button class="btn" type="submit">Enregistrer les produits</button>
  <p class="hint">Le stock est mis à jour automatiquement. Un produit déjà présent voit sa quantité augmenter.</p>
<?php endif; ?>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
