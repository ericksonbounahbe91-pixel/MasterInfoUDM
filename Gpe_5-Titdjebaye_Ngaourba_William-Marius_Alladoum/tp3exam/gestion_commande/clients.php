<?php
require_once __DIR__ . '/inc/config.php';
$titre = 'Clients';

$clients = q(
    'SELECT cl.idCli, cl.nomCli, cl.prenomCli, cl.email, cl.photo, r.nomRegion, d.nomDep,
            COUNT(DISTINCT c.idCmd) AS nb_cmd,
            COALESCE(SUM(lc.qteCmd * p.prixUProd), 0) AS total
     FROM Client cl
     LEFT JOIN Region r        ON r.idRegion = cl.idRegion
     LEFT JOIN Departement d   ON d.idDep    = r.idDep
     LEFT JOIN Commande c      ON c.idCli    = cl.idCli
     LEFT JOIN ligneCommande lc ON lc.idCmd  = c.idCmd
     LEFT JOIN Produit p       ON p.idProd   = lc.idProd
     GROUP BY cl.idCli, cl.nomCli, cl.prenomCli, cl.email, cl.photo, r.nomRegion, d.nomDep
     ORDER BY cl.nomCli, cl.prenomCli'
)->fetchAll();

require __DIR__ . '/inc/header.php';
?>
<div class="titre-page"><h1>Clients</h1></div>

<?php if (!$clients): ?>
  <p class="vide-texte">Aucun client enregistré dans la base.</p>
<?php else: ?>
<div class="tableau-conteneur">
<table>
  <thead><tr><th scope="col"></th><th scope="col">N°</th><th scope="col">Nom et prénom</th><th scope="col">E-mail</th><th scope="col">Région</th><th scope="col">Département</th><th scope="col" class="num">Commandes</th><th scope="col" class="num">Montant total</th></tr></thead>
  <tbody>
  <?php foreach ($clients as $c): ?>
    <tr>
      <td><?= vignette($c['photo'], $c['prenomCli']) ?></td>
      <td><?= (int) $c['idCli'] ?></td>
      <td><?= h($c['nomCli'] . ' ' . $c['prenomCli']) ?></td>
      <td><?= h($c['email']) ?></td>
      <td><?= h($c['nomRegion'] ?? '—') ?></td>
      <td><?= h($c['nomDep'] ?? '—') ?></td>
      <td class="num"><?= (int) $c['nb_cmd'] ?></td>
      <td class="num"><?= h(fcfa($c['total'])) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/inc/footer.php'; ?>
