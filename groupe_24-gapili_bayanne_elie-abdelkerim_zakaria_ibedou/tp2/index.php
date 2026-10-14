<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/graphiques.php';
$titre = 'Tableau de bord';

function date_valide(?string $v, string $defaut): string
{
    if ($v && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)
        && checkdate((int) substr($v, 5, 2), (int) substr($v, 8, 2), (int) substr($v, 0, 4))) {
        return $v;
    }
    return $defaut;
}

/* ---- Période analysée : par défaut, de la première à la dernière commande ---- */
$bornes = q('SELECT MIN(dateCmd) AS mn, MAX(dateCmd) AS mx FROM cmd_commande')->fetch();
$du = date_valide($_GET['du'] ?? null, $bornes['mn'] ?? date('Y-01-01'));
$au = date_valide($_GET['au'] ?? null, $bornes['mx'] ?? date('Y-m-d'));
if ($du > $au) {
    [$du, $au] = [$au, $du];
}
$p = ['du' => $du, 'au' => $au];

/* ---- Indicateurs : une seule requête agrégée sur la vue cmd_v_ligne_detail ---- */
$k = q(
    'SELECT COUNT(DISTINCT idCmd) AS nb_cmd, COUNT(DISTINCT idCli) AS nb_cli,
            COALESCE(SUM(qteCmd), 0) AS qte, COALESCE(SUM(montant), 0) AS ca
     FROM cmd_v_ligne_detail WHERE dateCmd BETWEEN :du AND :au', $p
)->fetch();
$panier = $k['nb_cmd'] > 0 ? $k['ca'] / $k['nb_cmd'] : 0;

$mensuel = q(
    "SELECT DATE_FORMAT(dateCmd, '%Y-%m') AS mois, SUM(montant) AS ca, COUNT(DISTINCT idCmd) AS nb
     FROM cmd_v_ligne_detail WHERE dateCmd BETWEEN :du AND :au
     GROUP BY DATE_FORMAT(dateCmd, '%Y-%m') ORDER BY mois", $p
)->fetchAll();

$sqlTopProduits =
    'SELECT p.designProd, SUM(v.qteCmd) AS qte, SUM(v.montant) AS ca
     FROM cmd_v_ligne_detail v JOIN cmd_produit p ON p.idProd = v.idProd
     WHERE v.dateCmd BETWEEN :du AND :au
     GROUP BY p.idProd, p.designProd ORDER BY ca DESC LIMIT 5';
$topProduits = q($sqlTopProduits, $p)->fetchAll();

$parDep = q(
    'SELECT d.nomDep, SUM(v.montant) AS ca, COUNT(DISTINCT v.idCmd) AS nb
     FROM cmd_v_ligne_detail v
     JOIN cmd_client cl      ON cl.idCli    = v.idCli
     JOIN cmd_region r       ON r.idRegion  = cl.idRegion
     JOIN cmd_departement d  ON d.idDep     = r.idDep
     WHERE v.dateCmd BETWEEN :du AND :au
     GROUP BY d.idDep, d.nomDep ORDER BY ca DESC', $p
)->fetchAll();

$parActivite = q(
    'SELECT p.categorieProd AS activite, SUM(v.montant) AS ca
     FROM cmd_v_ligne_detail v JOIN cmd_produit p ON p.idProd = v.idProd
     WHERE v.dateCmd BETWEEN :du AND :au
     GROUP BY p.categorieProd ORDER BY ca DESC', $p
)->fetchAll();

$topClients = q(
    "SELECT CONCAT(cl.nomCli, ' ', cl.prenomCli) AS client, SUM(v.montant) AS ca
     FROM cmd_v_ligne_detail v JOIN cmd_client cl ON cl.idCli = v.idCli
     WHERE v.dateCmd BETWEEN :du AND :au
     GROUP BY cl.idCli, cl.nomCli, cl.prenomCli ORDER BY ca DESC LIMIT 5", $p
)->fetchAll();

$stockBas = q(
    'SELECT designProd, qteStock FROM cmd_produit WHERE qteStock <= :seuil ORDER BY qteStock, designProd LIMIT 8',
    ['seuil' => SEUIL_STOCK]
)->fetchAll();

/* ---- Lecture : concentration des ventes sur les 5 premiers produits ---- */
$caTop = array_sum(array_column($topProduits, 'ca'));
$partTop = $k['ca'] > 0 ? round($caTop / $k['ca'] * 100) : 0;

/* ---- Analyse de performance : plan d'exécution à la demande ---- */
$explain = isset($_GET['explain']) ? q('EXPLAIN ' . $sqlTopProduits, $p)->fetchAll() : null;
$journal = $GLOBALS['journal_sql'] ?? [];

require __DIR__ . '/inc/header.php';
?>
<div class="titre-page"><div><h1>Tableau de bord</h1><p class="sous-titre">Ventes du <?= h(date('d/m/Y', strtotime($du))) ?> au <?= h(date('d/m/Y', strtotime($au))) ?></p></div></div>

<form method="get" class="formulaire en-ligne filtre">
  <div class="champ"><label for="du">Du</label><input type="date" id="du" name="du" value="<?= h($du) ?>"></div>
  <div class="champ"><label for="au">Au</label><input type="date" id="au" name="au" value="<?= h($au) ?>"></div>
  <button type="submit">Actualiser</button>
</form>

<?php if ((int) $k['nb_cmd'] === 0): ?>
  <p class="vide-texte">Aucune commande avec produits sur cette période. Modifiez les dates ou <a href="commandes.php">enregistrez une commande</a>.</p>
<?php else: ?>

<?php
$cartes = [
    ['argent',   "Chiffre d'affaires", fcfa($k['ca']), 'bleu'],
    ['commande', 'Commandes', (string) (int) $k['nb_cmd'], 'indigo'],
    ['panier',   'Panier moyen', fcfa($panier), 'ambre'],
    ['unites',   'Unités facturées', number_format((int) $k['qte'], 0, ',', ' '), 'vert'],
    ['client',   'Clients actifs', (string) (int) $k['nb_cli'], 'cyan'],
];
?>
<div class="indicateurs">
<?php foreach ($cartes as list($ic, $lib, $val, $ton)): ?>
  <div class="kpi <?= h($ton) ?>">
    <span class="kpi-icone"><?= icone($ic, 24) ?></span>
    <div><p class="kpi-lib"><?= h($lib) ?></p><p class="kpi-val"><?= h($val) ?></p></div>
  </div>
<?php endforeach; ?>
</div>

<section class="bloc" aria-labelledby="t-mois">
  <h2 id="t-mois">Chiffre d'affaires par mois</h2>
  <?= graphique_mensuel($mensuel) ?>
</section>

<div class="deux-colonnes">
  <section class="bloc" aria-labelledby="t-act">
    <h2 id="t-act">Chiffre d'affaires par catégorie</h2>
    <?= barres($parActivite, 'activite', 'ca') ?>
  </section>
  <section class="bloc" aria-labelledby="t-mois2">
    <h2 id="t-mois2">Lecture</h2>
    <p class="note">Les licences et abonnements rapportent un revenu récurrent ; le matériel pèse sur la trésorerie ; les services fidélisent. Une catégorie dominante signale une dépendance, une catégorie faible un potentiel.</p>
  </section>
</div>

<div class="deux-colonnes">
  <section class="bloc" aria-labelledby="t-prod">
    <h2 id="t-prod">Cinq offres qui rapportent le plus</h2>
    <?= barres($topProduits, 'designProd', 'ca') ?>
    <p class="note">Ces cinq offres représentent <?= (int) $partTop ?> % du chiffre d'affaires de la période.</p>
  </section>
  <section class="bloc" aria-labelledby="t-dep">
    <h2 id="t-dep">Chiffre d'affaires par département</h2>
    <?= barres($parDep, 'nomDep', 'ca') ?>
  </section>
</div>

<div class="deux-colonnes">
  <section class="bloc" aria-labelledby="t-cli">
    <h2 id="t-cli">Cinq meilleurs clients</h2>
    <?= barres($topClients, 'client', 'ca') ?>
  </section>
  <section class="bloc" aria-labelledby="t-stock">
    <h2 id="t-stock">Offres à disponibilité limitée</h2>
    <?php if (!$stockBas): ?>
      <p class="vide-texte">Aucune offre sous le seuil de <?= SEUIL_STOCK ?> unités.</p>
    <?php else: ?>
      <ul class="liste-stock">
      <?php foreach ($stockBas as $s): ?>
        <li><span><?= h($s['designProd']) ?></span>
            <span class="etiquette <?= (int) $s['qteStock'] <= 0 ? 'rouge' : 'ambre' ?>"><?= (int) $s['qteStock'] <= 0 ? 'Indisponible' : (int) $s['qteStock'] . ' disponible(s)' ?></span></li>
      <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <p class="note">Seuil d'alerte : <?= SEUIL_STOCK ?> unités (modifiable dans <code>inc/config.php</code>).</p>
  </section>
</div>
<?php endif; ?>

<section class="bloc" aria-labelledby="t-perf">
  <h2 id="t-perf">Analyse de performance</h2>
  <p>Temps d'exécution de chaque requête de cette page (hors affichage). Les requêtes de statistiques lisent toutes la vue <code>cmd_v_ligne_detail</code> filtrée par date, grâce à l'index <code>idx_commande_date</code>.</p>
  <div class="tableau-conteneur">
  <table class="compact">
    <thead><tr><th scope="col">Requête</th><th scope="col" class="num">Durée (ms)</th></tr></thead>
    <tbody>
    <?php foreach ($journal as [$sql, $ms]): ?>
      <tr><td class="sql"><?= h(mb_strimwidth($sql, 0, 110, '…')) ?></td><td class="num"><?= number_format($ms, 2, ',', ' ') ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php if ($explain === null): ?>
    <p><a href="?du=<?= h($du) ?>&amp;au=<?= h($au) ?>&amp;explain=1#t-perf">Afficher le plan d'exécution (EXPLAIN) de la requête « top offres »</a></p>
  <?php else: ?>
    <h3>Plan d'exécution (EXPLAIN) – top offres</h3>
    <div class="tableau-conteneur">
    <table class="compact">
      <thead><tr><?php foreach (array_keys($explain[0] ?? []) as $c): ?><th scope="col"><?= h($c) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
      <?php foreach ($explain as $ligne): ?>
        <tr><?php foreach ($ligne as $v): ?><td><?= h($v ?? 'NULL') ?></td><?php endforeach; ?></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <p class="note">Repérez la colonne <code>key</code> : un index est utilisé quand elle n'est pas NULL. <code>type = ALL</code> signifierait un parcours complet de la table.</p>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>
