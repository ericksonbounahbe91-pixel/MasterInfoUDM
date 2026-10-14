<?php
require_once __DIR__ . '/includes/functions.php';

// Création d'une commande vide : l'enregistrement des produits se fait ensuite sur sa page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $idCli = (int)($_POST['idCli'] ?? 0);
    $date  = $_POST['dateCmd'] ?? '';
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$idCli || !q1('SELECT idCli FROM Client WHERE idCli = ?', [$idCli])) {
        flash('err', 'Choisissez un client.');
    } elseif (!$d || $d->format('Y-m-d') !== $date) {
        flash('err', 'La date de la commande n\'est pas valide.');
    } else {
        run('INSERT INTO Commande (dateCmd, idCli) VALUES (?, ?)', [$date, $idCli]);
        flash('ok', 'Commande n° ' . db()->lastInsertId() . ' créée. Ajoutez maintenant ses produits.');
        redirect('commande.php?id=' . db()->lastInsertId());
    }
    redirect('commandes.php');
}

$pageTitle = 'Commandes';
require __DIR__ . '/includes/header.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$total = (int)q1('SELECT COUNT(*) AS n FROM Commande')['n'];
$pages = max(1, (int)ceil($total / PER_PAGE));
$page = min($page, $pages);
$offset = ($page - 1) * PER_PAGE;

// Agrégat par commande calculé une seule fois dans une sous-requête, puis jointure
$cmds = q('SELECT c.idCmd, c.dateCmd, cl.nomCli, cl.prenomCli,
                  COALESCE(t.nbProd, 0) AS nbProd, COALESCE(t.unites, 0) AS unites, COALESCE(t.montant, 0) AS montant
           FROM Commande c
           JOIN Client cl ON cl.idCli = c.idCli
           LEFT JOIN (SELECT idCmd, COUNT(*) AS nbProd, SUM(qteCmd) AS unites, SUM(montant) AS montant
                      FROM v_lignes GROUP BY idCmd) t ON t.idCmd = c.idCmd
           ORDER BY c.dateCmd DESC, c.idCmd DESC
           LIMIT ' . PER_PAGE . ' OFFSET ' . $offset);
$clients = q('SELECT idCli, nomCli, prenomCli FROM Client ORDER BY nomCli, prenomCli');
?>
<h1>Commandes</h1>
<p class="sub"><?= $total ?> commande(s) au total.</p>

<form class="card bar" method="post">
  <?= csrf_field() ?>
  <div class="grow"><label for="idCli">Nouvelle commande — client</label>
    <select id="idCli" name="idCli" required style="width:100%">
      <option value="">Choisir un client</option>
      <?php foreach ($clients as $c): ?><option value="<?= (int)$c['idCli'] ?>"><?= h($c['nomCli'] . ' ' . $c['prenomCli']) ?></option><?php endforeach; ?>
    </select></div>
  <div><label for="dateCmd">Date</label><input id="dateCmd" name="dateCmd" type="date" required value="<?= date('Y-m-d') ?>"></div>
  <button class="btn" type="submit">Créer la commande</button>
</form>

<div class="card tablewrap">
<?php if (!$cmds): ?><p class="empty">Aucune commande pour le moment. Créez-en une avec le formulaire ci-dessus.</p><?php else: ?>
<table>
  <thead><tr><th>N°</th><th>Date</th><th>Client</th><th class="num">Produits</th><th class="num">Unités</th><th class="num">Montant</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($cmds as $c): ?>
    <tr>
      <td>#<?= (int)$c['idCmd'] ?></td>
      <td><?= h(date('d/m/Y', strtotime($c['dateCmd']))) ?></td>
      <td><?= h($c['prenomCli'] . ' ' . $c['nomCli']) ?></td>
      <td class="num"><?= (int)$c['nbProd'] ?></td>
      <td class="num"><?= (int)$c['unites'] ?></td>
      <td class="num"><?= money($c['montant']) ?></td>
      <td class="num"><a href="commande.php?id=<?= (int)$c['idCmd'] ?>">Ouvrir</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php if ($pages > 1): ?>
<nav class="pager" aria-label="Pagination">
  <?php for ($i = 1; $i <= $pages; $i++): ?>
    <?= $i === $page ? '<span aria-current="page">' . $i . '</span>' : '<a href="?page=' . $i . '">' . $i . '</a>' ?>
  <?php endfor; ?>
</nav>
<?php endif; endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
