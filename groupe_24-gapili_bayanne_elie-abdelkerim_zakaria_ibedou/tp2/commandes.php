<?php
require_once __DIR__ . '/inc/config.php';
$titre = 'Commandes';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    $idCli = (int) ($_POST['idCli'] ?? 0);
    $date  = (string) ($_POST['dateCmd'] ?? '');
    $d     = DateTime::createFromFormat('Y-m-d', $date);
    $client = q('SELECT idCli FROM cmd_client WHERE idCli = ?', [$idCli])->fetch();

    if (!$client || !$d || $d->format('Y-m-d') !== $date) {
        flash('erreur', 'Choisissez un client et une date valide.');
        redirect('commandes.php');
    }
    q('INSERT INTO cmd_commande (dateCmd, idCli) VALUES (?, ?)', [$date, $idCli]);
    $nouvelle = (int) db()->lastInsertId();
    flash('ok', 'Commande créée. Ajoutez maintenant ses produits.');
    redirect('commande.php?id=' . $nouvelle);
}

$commandes = q(
    'SELECT c.idCmd, c.dateCmd, cl.nomCli, cl.prenomCli,
            COUNT(lc.idProd) AS nb_lignes,
            COALESCE(SUM(lc.qteCmd * p.prixUProd), 0) AS montant
     FROM cmd_commande c
     JOIN cmd_client cl             ON cl.idCli  = c.idCli
     LEFT JOIN cmd_lignecommande lc ON lc.idCmd  = c.idCmd
     LEFT JOIN cmd_produit p        ON p.idProd  = lc.idProd
     GROUP BY c.idCmd, c.dateCmd, cl.idCli, cl.nomCli, cl.prenomCli
     ORDER BY c.dateCmd DESC, c.idCmd DESC'
)->fetchAll();
$clients = q('SELECT idCli, nomCli, prenomCli FROM cmd_client ORDER BY nomCli, prenomCli')->fetchAll();

require __DIR__ . '/inc/header.php';
?>
<div class="titre-page"><div><h1>Commandes</h1><p class="sous-titre"><?= count($commandes) ?> commande(s) enregistrée(s)</p></div></div>

<section class="bloc" aria-labelledby="t-nouvelle">
  <h2 id="t-nouvelle">Nouvelle commande</h2>
  <form method="post" class="formulaire en-ligne">
    <?= csrf_field() ?>
    <div class="champ">
      <label for="idCli">Client</label>
      <select id="idCli" name="idCli" required>
        <option value="">Choisir un client</option>
        <?php foreach ($clients as $c): ?>
          <option value="<?= (int) $c['idCli'] ?>"><?= h($c['nomCli'] . ' ' . $c['prenomCli']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="champ">
      <label for="dateCmd">Date</label>
      <input type="date" id="dateCmd" name="dateCmd" required value="<?= h(date('Y-m-d')) ?>">
    </div>
    <button type="submit"><?= icone('plus', 18) ?>Créer la commande</button>
  </form>
</section>

<?php if (!$commandes): ?>
  <p class="vide-texte">Aucune commande pour le moment. Créez la première ci-dessus.</p>
<?php else: ?>
<div class="tableau-conteneur">
<table>
  <thead><tr><th scope="col">N°</th><th scope="col">Date</th><th scope="col">Client</th><th scope="col" class="num">Produits</th><th scope="col" class="num">Montant</th><th scope="col"></th></tr></thead>
  <tbody>
  <?php foreach ($commandes as $c): ?>
    <tr>
      <td><?= (int) $c['idCmd'] ?></td>
      <td><?= h(date('d/m/Y', strtotime($c['dateCmd']))) ?></td>
      <td><?= h($c['nomCli'] . ' ' . $c['prenomCli']) ?></td>
      <td class="num"><?= (int) $c['nb_lignes'] ?></td>
      <td class="num"><?= h(fcfa($c['montant'])) ?></td>
      <td class="actions"><a class="lien-action" href="commande.php?id=<?= (int) $c['idCmd'] ?>">Ouvrir<?= icone('ouvrir', 16) ?></a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/inc/footer.php'; ?>
