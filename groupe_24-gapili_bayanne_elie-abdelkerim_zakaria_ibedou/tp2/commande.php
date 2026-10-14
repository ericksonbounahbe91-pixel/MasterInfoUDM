<?php
require_once __DIR__ . '/inc/config.php';

$id  = (int) ($_GET['id'] ?? 0);
$cmd = q(
    'SELECT c.idCmd, c.dateCmd, cl.nomCli, cl.prenomCli, cl.email
     FROM cmd_commande c JOIN cmd_client cl ON cl.idCli = c.idCli
     WHERE c.idCmd = ?',
    [$id]
)->fetch();
if (!$cmd) {
    flash('erreur', 'Commande introuvable.');
    redirect('commandes.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    $action = (string) ($_POST['action'] ?? '');
    $pdo    = db();

    /* ---- Enregistrer les offres de la commande (plusieurs lignes d'un coup) ---- */
    if ($action === 'ajouter') {
        $lignes = [];
        foreach ((array) ($_POST['idProd'] ?? []) as $i => $idp) {
            $idp = (int) $idp;
            $qte = (int) (((array) ($_POST['qte'] ?? []))[$i] ?? 0);
            if ($idp > 0 && $qte > 0) {
                $lignes[$idp] = ($lignes[$idp] ?? 0) + $qte;   // même produit saisi deux fois : on cumule
            }
        }
        if (!$lignes) {
            flash('erreur', 'Choisissez au moins un produit avec une quantité supérieure à 0.');
            redirect('commande.php?id=' . $id);
        }
        ksort($lignes);                                        // ordre de verrouillage constant : évite les blocages
        $pdo->beginTransaction();
        try {
            foreach ($lignes as $idp => $qte) {
                $p = q('SELECT designProd, qteStock FROM cmd_produit WHERE idProd = ? FOR UPDATE', [$idp])->fetch();
                if (!$p) {
                    throw new RuntimeException('Un des produits choisis n\'existe plus.');
                }
                if ((int) $p['qteStock'] < $qte) {
                    throw new RuntimeException('Disponibilité insuffisante pour « ' . $p['designProd'] . ' » : '
                        . (int) $p['qteStock'] . ' disponible(s), ' . $qte . ' demandé(s). Aucune ligne n\'a été enregistrée.');
                }
                q('INSERT INTO cmd_lignecommande (idCmd, idProd, qteCmd) VALUES (?, ?, ?)
                   ON DUPLICATE KEY UPDATE qteCmd = qteCmd + VALUES(qteCmd)', [$id, $idp, $qte]);
                q('UPDATE cmd_produit SET qteStock = qteStock - ? WHERE idProd = ?', [$qte, $idp]);
            }
            $pdo->commit();
            flash('ok', count($lignes) . ' offre(s) enregistrée(s) dans la commande. La disponibilité a été mise à jour.');
        } catch (Throwable $e) {
            $pdo->rollBack();
            flash('erreur', $e instanceof RuntimeException ? $e->getMessage() : 'Erreur technique : enregistrement annulé.');
        }
        redirect('commande.php?id=' . $id);
    }

    /* ---- Supprimer un produit de la commande (le stock est rétabli) ---- */
    if ($action === 'supprimer_ligne') {
        $idp = (int) ($_POST['idProd'] ?? 0);
        $pdo->beginTransaction();
        try {
            $l = q('SELECT qteCmd FROM cmd_lignecommande WHERE idCmd = ? AND idProd = ? FOR UPDATE', [$id, $idp])->fetch();
            if ($l) {
                q('DELETE FROM cmd_lignecommande WHERE idCmd = ? AND idProd = ?', [$id, $idp]);
                q('UPDATE cmd_produit SET qteStock = qteStock + ? WHERE idProd = ?', [(int) $l['qteCmd'], $idp]);
                flash('ok', 'Produit retiré de la commande. La disponibilité a été rétablie.');
            } else {
                flash('erreur', 'Ce produit ne figure plus dans la commande.');
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            flash('erreur', 'Erreur technique : suppression annulée.');
        }
        redirect('commande.php?id=' . $id);
    }
    redirect('commande.php?id=' . $id);
}

$lignes = q(
    'SELECT lc.idProd, p.designProd, lc.qteCmd, p.prixUProd, lc.qteCmd * p.prixUProd AS sous_total
     FROM cmd_lignecommande lc JOIN cmd_produit p ON p.idProd = lc.idProd
     WHERE lc.idCmd = ? ORDER BY p.designProd',
    [$id]
)->fetchAll();
$total    = array_sum(array_column($lignes, 'sous_total'));
$produits = q('SELECT idProd, designProd, categorieProd, uniteProd, qteStock, prixUProd FROM cmd_produit ORDER BY categorieProd, designProd')->fetchAll();

$titre = 'Commande n° ' . $id;
require __DIR__ . '/inc/header.php';

function options_produits(array $produits): string
{
    $o = '<option value="">Choisir une offre</option>';
    foreach ($produits as $p) {
        $o .= '<option value="' . (int) $p['idProd'] . '"' . ((int) $p['qteStock'] <= 0 ? ' disabled' : '') . '>'
            . h('[' . $p['categorieProd'] . '] ' . $p['designProd'] . ' (dispo ' . (int) $p['qteStock'] . ', ' . fcfa($p['prixUProd']) . ' / ' . $p['uniteProd'] . ')') . '</option>';
    }
    return $o;
}
?>
<div class="titre-page">
  <div><h1>Commande n° <?= (int) $cmd['idCmd'] ?></h1><p class="sous-titre"><?= count($lignes) ?> offre(s) dans cette commande</p></div>
  <a class="bouton secondaire" href="commandes.php"><?= icone('retour', 18) ?>Toutes les commandes</a>
</div>

<dl class="fiche">
  <div><dt>Client</dt><dd><?= h($cmd['nomCli'] . ' ' . $cmd['prenomCli']) ?></dd></div>
  <div><dt>Date</dt><dd><?= h(date('d/m/Y', strtotime($cmd['dateCmd']))) ?></dd></div>
  <div><dt>E-mail</dt><dd><?= h($cmd['email'] ?? '—') ?></dd></div>
  <div><dt>Total</dt><dd><strong><?= h(fcfa($total)) ?></strong></dd></div>
</dl>

<section class="bloc" aria-labelledby="t-lignes">
  <h2 id="t-lignes">Offres de la commande</h2>
  <?php if (!$lignes): ?>
    <p class="vide-texte">Cette commande ne contient encore aucune offre. Utilisez le formulaire ci-dessous.</p>
  <?php else: ?>
  <div class="tableau-conteneur">
  <table>
    <thead><tr><th scope="col">Offre</th><th scope="col" class="num">Quantité</th><th scope="col" class="num">Prix unitaire</th><th scope="col" class="num">Sous-total</th><th scope="col"></th></tr></thead>
    <tbody>
    <?php foreach ($lignes as $l): ?>
      <tr>
        <td><?= h($l['designProd']) ?></td>
        <td class="num"><?= (int) $l['qteCmd'] ?></td>
        <td class="num"><?= h(fcfa($l['prixUProd'])) ?></td>
        <td class="num"><?= h(fcfa($l['sous_total'])) ?></td>
        <td class="actions">
          <form method="post" onsubmit="return confirm('Retirer cette offre de la commande ? La disponibilité sera rétablie.');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="supprimer_ligne">
            <input type="hidden" name="idProd" value="<?= (int) $l['idProd'] ?>">
            <button type="submit" class="lien-danger"><?= icone('retirer', 16) ?>Retirer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr><th scope="row" colspan="3">Total de la commande</th><td class="num"><strong><?= h(fcfa($total)) ?></strong></td><td></td></tr></tfoot>
  </table>
  </div>
  <?php endif; ?>
</section>

<section class="bloc" aria-labelledby="t-ajout">
  <h2 id="t-ajout">Enregistrer des offres dans cette commande</h2>
  <form method="post" id="form-lignes" class="formulaire">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="ajouter">
    <div id="lignes">
      <div class="ligne-saisie">
        <select name="idProd[]" aria-label="Produit"><?= options_produits($produits) ?></select>
        <input type="number" name="qte[]" min="1" step="1" placeholder="Quantité" aria-label="Quantité">
        <button type="button" class="secondaire retirer" aria-label="Retirer cette ligne de saisie">Retirer</button>
      </div>
    </div>
    <div class="barre-actions">
      <button type="button" class="secondaire" id="ajouter-ligne"><?= icone('plus', 18) ?>Ajouter une ligne</button>
      <button type="submit"><?= icone('check', 18) ?>Enregistrer les offres</button>
    </div>
  </form>
</section>

<script>
(function () {
  var zone = document.getElementById('lignes');
  document.getElementById('ajouter-ligne').addEventListener('click', function () {
    var copie = zone.firstElementChild.cloneNode(true);
    copie.querySelector('select').value = '';
    copie.querySelector('input').value = '';
    zone.appendChild(copie);
    copie.querySelector('select').focus();
  });
  zone.addEventListener('click', function (e) {
    if (!e.target.classList.contains('retirer')) return;
    if (zone.children.length > 1) { e.target.closest('.ligne-saisie').remove(); }
    else { zone.firstElementChild.querySelector('select').value = ''; zone.firstElementChild.querySelector('input').value = ''; }
  });
})();
</script>
<?php require __DIR__ . '/inc/footer.php'; ?>
