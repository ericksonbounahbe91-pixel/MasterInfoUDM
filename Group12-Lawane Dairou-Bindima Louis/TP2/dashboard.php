<?php
$pageTitle = 'Tableau de bord';
require __DIR__ . '/includes/header.php';

// ---- Période analysée (par défaut : toute l'activité enregistrée) ----
$range = q1('SELECT MIN(dateCmd) AS d1, MAX(dateCmd) AS d2 FROM Commande');
$valid = fn($d) => ($x = DateTime::createFromFormat('Y-m-d', (string)$d)) && $x->format('Y-m-d') === $d;
$d1 = $valid($_GET['d1'] ?? '') ? $_GET['d1'] : ($range['d1'] ?? date('Y-01-01'));
$d2 = $valid($_GET['d2'] ?? '') ? $_GET['d2'] : ($range['d2'] ?? date('Y-m-d'));
if ($d1 > $d2) [$d1, $d2] = [$d2, $d1];
$P = [$d1, $d2];

// ---- 1. Indicateurs clés (une seule requête sur la vue) ----
$k = q1('SELECT COUNT(DISTINCT idCmd) AS nbCmd, COUNT(DISTINCT idCli) AS nbCli,
                COALESCE(SUM(qteCmd),0) AS unites, COALESCE(SUM(montant),0) AS ca
         FROM v_lignes WHERE dateCmd BETWEEN ? AND ?', $P);
$panier = $k['nbCmd'] ? $k['ca'] / $k['nbCmd'] : 0;

// ---- 2. Évolution mensuelle ----
$mois = q('SELECT DATE_FORMAT(dateCmd, "%Y-%m") AS mois, SUM(montant) AS ca, COUNT(DISTINCT idCmd) AS nb
           FROM v_lignes WHERE dateCmd BETWEEN ? AND ? GROUP BY mois ORDER BY mois', $P);

// ---- 3. Top produits par chiffre d'affaires ----
$top = q('SELECT designProd, SUM(qteCmd) AS unites, SUM(montant) AS ca
          FROM v_lignes WHERE dateCmd BETWEEN ? AND ? GROUP BY idProd, designProd ORDER BY ca DESC LIMIT 6', $P);

// ---- 4. Chiffre d'affaires par région / département ----
$regions = q('SELECT r.nomRegion, d.nomDep, SUM(v.montant) AS ca, COUNT(DISTINCT v.idCmd) AS nb
              FROM v_lignes v JOIN Client cl ON cl.idCli = v.idCli
              JOIN Region r ON r.idRegion = cl.idRegion JOIN Departement d ON d.idDep = r.idDep
              WHERE v.dateCmd BETWEEN ? AND ?
              GROUP BY r.idRegion, r.nomRegion, d.nomDep ORDER BY ca DESC', $P);
$deps = q('SELECT d.nomDep, SUM(v.montant) AS ca
           FROM v_lignes v JOIN Client cl ON cl.idCli = v.idCli
           JOIN Region r ON r.idRegion = cl.idRegion JOIN Departement d ON d.idDep = r.idDep
           WHERE v.dateCmd BETWEEN ? AND ? GROUP BY d.idDep, d.nomDep ORDER BY ca DESC', $P);

// ---- 5. Meilleurs clients ----
$clients = q('SELECT cl.prenomCli, cl.nomCli, COUNT(DISTINCT v.idCmd) AS nb, SUM(v.montant) AS ca
              FROM v_lignes v JOIN Client cl ON cl.idCli = v.idCli
              WHERE v.dateCmd BETWEEN ? AND ? GROUP BY cl.idCli, cl.prenomCli, cl.nomCli ORDER BY ca DESC LIMIT 5', $P);

// ---- 6. Alertes de stock : produits sous le seuil, avec la demande de la période ----
$alertes = q('SELECT p.idProd, p.designProd, p.qteStock, COALESCE(v.unites,0) AS unites
              FROM Produit p
              LEFT JOIN (SELECT idProd, SUM(qteCmd) AS unites FROM v_lignes WHERE dateCmd BETWEEN ? AND ? GROUP BY idProd) v
                     ON v.idProd = p.idProd
              WHERE p.qteStock <= ? ORDER BY p.qteStock, unites DESC', [$d1, $d2, STOCK_ALERT]);

$totalDep = array_sum(array_column($deps, 'ca')) ?: 1;
$jsonMois = json_encode(['l' => array_column($mois, 'mois'), 'ca' => array_map('floatval', array_column($mois, 'ca')), 'nb' => array_map('intval', array_column($mois, 'nb'))]);
$jsonTop  = json_encode(['l' => array_column($top, 'designProd'), 'ca' => array_map('floatval', array_column($top, 'ca'))]);
$jsonReg  = json_encode(['l' => array_column($regions, 'nomRegion'), 'ca' => array_map('floatval', array_column($regions, 'ca'))]);
?>
<h1>Tableau de bord</h1>
<p class="sub">Activité des lignes de commande du <?= h(date('d/m/Y', strtotime($d1))) ?> au <?= h(date('d/m/Y', strtotime($d2))) ?>. Le chiffre d'affaires est calculé avec le prix actuel des produits.</p>

<form class="bar" method="get">
  <div><label for="d1">Du</label><input id="d1" name="d1" type="date" value="<?= h($d1) ?>"></div>
  <div><label for="d2">Au</label><input id="d2" name="d2" type="date" value="<?= h($d2) ?>"></div>
  <button class="btn" type="submit">Appliquer</button>
  <a class="btn btn-ghost" href="dashboard.php">Toute la période</a>
</form>

<?php if (!$k['nbCmd']): ?>
  <div class="card"><p class="empty">Aucune ligne de commande sur cette période. Élargissez les dates ou enregistrez des produits dans une commande.</p></div>
<?php else: ?>
<section class="kpis" aria-label="Indicateurs clés">
  <div class="kpi"><b><?= money($k['ca']) ?></b><span>Chiffre d'affaires</span></div>
  <div class="kpi"><b><?= (int)$k['nbCmd'] ?></b><span>Commandes</span></div>
  <div class="kpi"><b><?= money($panier) ?></b><span>Panier moyen</span></div>
  <div class="kpi"><b><?= (int)$k['unites'] ?></b><span>Unités vendues</span></div>
  <div class="kpi"><b><?= (int)$k['nbCli'] ?></b><span>Clients actifs</span></div>
</section>

<div class="card">
  <h2>Chiffre d'affaires par mois</h2>
  <div class="chart"><canvas id="cMois" role="img" aria-label="Courbe du chiffre d'affaires mensuel"></canvas></div>
</div>

<div class="grid2" style="margin-bottom:1.25rem">
  <div class="card">
    <h2>Produits qui rapportent le plus</h2>
    <div class="chart"><canvas id="cTop" role="img" aria-label="Chiffre d'affaires des six premiers produits"></canvas></div>
  </div>
  <div class="card">
    <h2>Chiffre d'affaires par région</h2>
    <div class="chart"><canvas id="cReg" role="img" aria-label="Chiffre d'affaires par région"></canvas></div>
  </div>
</div>

<div class="grid2" style="margin-bottom:1.25rem">
  <div class="card tablewrap">
    <h2>Poids de chaque département</h2>
    <table>
      <thead><tr><th>Département</th><th class="num">CA</th><th class="num">Part</th></tr></thead>
      <tbody>
      <?php foreach ($deps as $d): ?>
        <tr><td><?= h($d['nomDep']) ?></td><td class="num"><?= money($d['ca']) ?></td><td class="num"><?= number_format($d['ca'] / $totalDep * 100, 1, ',', '') ?> %</td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card tablewrap">
    <h2>Cinq meilleurs clients</h2>
    <table>
      <thead><tr><th>Client</th><th class="num">Commandes</th><th class="num">CA</th></tr></thead>
      <tbody>
      <?php foreach ($clients as $c): ?>
        <tr><td><?= h($c['prenomCli'] . ' ' . $c['nomCli']) ?></td><td class="num"><?= (int)$c['nb'] ?></td><td class="num"><?= money($c['ca']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="card tablewrap">
  <h2>Stocks à surveiller (<?= STOCK_ALERT ?> unités ou moins)</h2>
<?php if (!$alertes): ?>
  <p class="empty">Aucun produit sous le seuil d'alerte.</p>
<?php else: ?>
  <table>
    <thead><tr><th>Produit</th><th class="num">En stock</th><th class="num">Vendu sur la période</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($alertes as $a): ?>
      <tr>
        <td><?= h($a['designProd']) ?></td>
        <td class="num"><?= (int)$a['qteStock'] ?></td>
        <td class="num"><?= (int)$a['unites'] ?></td>
        <td><?= $a['qteStock'] == 0 ? '<span class="tag tag-out">Rupture</span>' : '<span class="tag tag-low">Stock bas</span>' ?>
            <a href="produit_form.php?id=<?= (int)$a['idProd'] ?>">Réapprovisionner</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
</div>

<?php
// ---- Analyse de performance : temps de chaque requête de cette page ----
$ms = array_column($QUERY_LOG, 'ms');
$max = max($ms ?: [1]);
$pageMs = (microtime(true) - $PAGE_START) * 1000;
?>
<details class="card perf">
  <summary><strong>Analyse de performance de cette page</strong></summary>
  <p class="hint">Page générée en <?= number_format($pageMs, 1, ',', '') ?> ms, dont <?= number_format(array_sum($ms), 1, ',', '') ?> ms pour les <?= count($QUERY_LOG) ?> requêtes SQL.
     Mémoire PHP : <?= number_format(memory_get_peak_usage() / 1048576, 1, ',', '') ?> Mo. Détail dans le README (index, choix de conception).</p>
  <div class="tablewrap"><table>
    <thead><tr><th>Requête</th><th class="num">Lignes</th><th class="num">Temps</th><th style="width:25%"></th></tr></thead>
    <tbody>
    <?php foreach ($QUERY_LOG as $e): ?>
      <tr>
        <td><code><?= h(preg_replace('/\s+/', ' ', substr($e['sql'], 0, 110))) ?>…</code></td>
        <td class="num"><?= (int)$e['rows'] ?></td>
        <td class="num"><?= number_format($e['ms'], 2, ',', '') ?> ms</td>
        <td><span class="bar-ms" style="width:<?= max(2, round($e['ms'] / $max * 100)) ?>%"></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</details>

<?php if ($k['nbCmd']): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
  if (typeof Chart === 'undefined') return; // hors ligne : les tableaux restent lisibles
  var fmt = function (v) { return new Intl.NumberFormat('fr-FR').format(v) + ' <?= h(CURRENCY) ?>'; };
  var slate = '#2f5d8a', accent = '#1b7f5c', amber = '#d99a06';
  Chart.defaults.font.family = '"Segoe UI", system-ui, sans-serif';
  Chart.defaults.color = '#5d6a82';
  var tip = { callbacks: { label: function (c) { return ' ' + fmt(c.parsed.y !== undefined && c.chart.options.indexAxis !== 'y' ? c.parsed.y : c.parsed.x); } } };

  var M = <?= $jsonMois ?>;
  new Chart(document.getElementById('cMois'), {
    type: 'line',
    data: { labels: M.l, datasets: [{ label: 'CA', data: M.ca, borderColor: accent, backgroundColor: 'rgba(27,127,92,.13)', fill: true, tension: .25, pointRadius: 3 }] },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: tip }, scales: { y: { beginAtZero: true, ticks: { callback: fmt } } } }
  });
  var T = <?= $jsonTop ?>;
  new Chart(document.getElementById('cTop'), {
    type: 'bar',
    data: { labels: T.l, datasets: [{ data: T.ca, backgroundColor: slate }] },
    options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: tip }, scales: { x: { beginAtZero: true, ticks: { callback: fmt } } } }
  });
  var R = <?= $jsonReg ?>;
  new Chart(document.getElementById('cReg'), {
    type: 'bar',
    data: { labels: R.l, datasets: [{ data: R.ca, backgroundColor: amber }] },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: tip }, scales: { y: { beginAtZero: true, ticks: { callback: fmt } } } }
  });
})();
</script>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
