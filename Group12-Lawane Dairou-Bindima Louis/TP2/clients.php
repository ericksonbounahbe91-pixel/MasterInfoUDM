<?php
$pageTitle = 'Clients';
require __DIR__ . '/includes/header.php';

$search = trim($_GET['q'] ?? '');
$sql = 'SELECT cl.idCli, cl.nomCli, cl.prenomCli, cl.email, cl.photo, r.nomRegion, d.nomDep,
               COUNT(DISTINCT c.idCmd) AS nbCmd, COALESCE(SUM(v.montant),0) AS total
        FROM Client cl
        JOIN Region r ON r.idRegion = cl.idRegion
        JOIN Departement d ON d.idDep = r.idDep
        LEFT JOIN Commande c ON c.idCli = cl.idCli
        LEFT JOIN v_lignes v ON v.idCmd = c.idCmd AND v.idProd IS NOT NULL';
$params = [];
if ($search !== '') {
    $sql .= ' WHERE cl.nomCli LIKE ? OR cl.prenomCli LIKE ? OR cl.email LIKE ?';
    $params = ["%$search%", "%$search%", "%$search%"];
}
$sql .= ' GROUP BY cl.idCli, cl.nomCli, cl.prenomCli, cl.email, cl.photo, r.nomRegion, d.nomDep ORDER BY cl.nomCli, cl.prenomCli';
$clients = q($sql, $params);
?>
<h1>Clients</h1>
<p class="sub"><?= count($clients) ?> client(s)<?= $search !== '' ? ' pour « ' . h($search) . ' »' : '' ?>.</p>

<form class="bar" method="get">
  <div class="grow"><label for="q">Rechercher un client</label><input id="q" name="q" value="<?= h($search) ?>" placeholder="Nom, prénom ou e-mail" style="width:100%"></div>
  <button class="btn" type="submit">Rechercher</button>
  <?php if ($search !== ''): ?><a class="btn btn-ghost" href="clients.php">Effacer</a><?php endif; ?>
</form>

<div class="card tablewrap">
<?php if (!$clients): ?><p class="empty">Aucun client ne correspond à cette recherche.</p><?php else: ?>
<table>
  <thead><tr><th>Client</th><th>E-mail</th><th>Région</th><th>Département</th><th class="num">Commandes</th><th class="num">Total acheté</th></tr></thead>
  <tbody>
  <?php foreach ($clients as $c): ?>
    <tr>
      <td><div class="who"><?= avatar($c['photo'], $c['prenomCli'] . ' ' . $c['nomCli']) ?><span><?= h($c['prenomCli'] . ' ' . $c['nomCli']) ?></span></div></td>
      <td><?= h($c['email']) ?></td>
      <td><?= h($c['nomRegion']) ?></td>
      <td><?= h($c['nomDep']) ?></td>
      <td class="num"><?= (int)$c['nbCmd'] ?></td>
      <td class="num"><?= money($c['total']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
