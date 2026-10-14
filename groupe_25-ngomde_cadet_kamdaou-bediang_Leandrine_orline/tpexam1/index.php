<?php
/** Tableau de bord – statistiques sur les lignes de commande. */
require 'config.php';

// Statistiques générales
$stats = $pdo->query("
    SELECT
      (SELECT COUNT(*) FROM Produit) AS nbProduits,
      (SELECT COUNT(*) FROM Client) AS nbClients,
      (SELECT COUNT(*) FROM Commande) AS nbCommandes,
      (SELECT COALESCE(SUM(qteCmd), 0) FROM ligneCommande) AS totalArticles
")->fetch();

// Chiffre d'affaires total
$ca = $pdo->query("
    SELECT COALESCE(SUM(lc.qteCmd * p.prixUProd), 0) AS ca
    FROM ligneCommande lc
    JOIN Produit p ON p.idProd = lc.idProd
")->fetch()['ca'];

// Top 5 produits les plus commandés
$topProduits = $pdo->query("
    SELECT p.designProd, SUM(lc.qteCmd) AS totalQte, SUM(lc.qteCmd * p.prixUProd) AS totalCA
    FROM ligneCommande lc
    JOIN Produit p ON p.idProd = lc.idProd
    GROUP BY p.idProd
    ORDER BY totalQte DESC
    LIMIT 5
")->fetchAll();

// Commandes récentes
$recentCmd = $pdo->query("
    SELECT c.idCmd, c.dateCmd, CONCAT(cl.prenomCli, ' ', cl.nomCli) AS client,
           COUNT(lc.idProd) AS nbLignes, SUM(lc.qteCmd * p.prixUProd) AS montant
    FROM Commande c
    JOIN Client cl ON cl.idCli = c.idCli
    LEFT JOIN ligneCommande lc ON lc.idCmd = c.idCmd
    LEFT JOIN Produit p ON p.idProd = lc.idProd
    GROUP BY c.idCmd
    ORDER BY c.dateCmd DESC
    LIMIT 5
")->fetchAll();

// Produits en rupture ou stock faible
$stockFaible = $pdo->query("
    SELECT idProd, designProd, qteStock FROM Produit WHERE qteStock <= 10 ORDER BY qteStock
")->fetchAll();

// Ventes par mois (6 derniers mois)
$ventesParMois = $pdo->query("
    SELECT DATE_FORMAT(c.dateCmd, '%Y-%m') AS mois,
           COUNT(DISTINCT c.idCmd) AS nbCmd,
           COALESCE(SUM(lc.qteCmd * p.prixUProd), 0) AS ca
    FROM Commande c
    LEFT JOIN ligneCommande lc ON lc.idCmd = c.idCmd
    LEFT JOIN Produit p ON p.idProd = lc.idProd
    GROUP BY mois
    ORDER BY mois DESC
    LIMIT 6
")->fetchAll();
$ventesParMois = array_reverse($ventesParMois);

// CA par client
$caParClient = $pdo->query("
    SELECT CONCAT(cl.prenomCli, ' ', cl.nomCli) AS client,
           COALESCE(SUM(lc.qteCmd * p.prixUProd), 0) AS ca,
           COUNT(DISTINCT c.idCmd) AS nbCmd
    FROM Client cl
    LEFT JOIN Commande c ON c.idCli = cl.idCli
    LEFT JOIN ligneCommande lc ON lc.idCmd = c.idCmd
    LEFT JOIN Produit p ON p.idProd = lc.idProd
    GROUP BY cl.idCli
    ORDER BY ca DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tableau de bord – GestCommandes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  <style>
    .stat-card { border-left: 4px solid; }
    .stat-card.blue  { border-color: #0d6efd; }
    .stat-card.green { border-color: #198754; }
    .stat-card.orange{ border-color: #fd7e14; }
    .stat-card.red   { border-color: #dc3545; }
    .stat-icon { font-size: 2rem; opacity: .3; }
  </style>
</head>
<body class="bg-light">
<?php include 'includes/navbar.php'; ?>
<div class="container">
  <?= showFlash() ?>

  <!-- KPI Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
      <div class="card stat-card blue h-100">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div><div class="text-muted small">Produits</div><div class="fs-3 fw-bold"><?= $stats['nbProduits'] ?></div></div>
          <i class="bi bi-box stat-icon text-primary"></i>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card stat-card green h-100">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div><div class="text-muted small">Clients</div><div class="fs-3 fw-bold"><?= $stats['nbClients'] ?></div></div>
          <i class="bi bi-people stat-icon text-success"></i>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card stat-card orange h-100">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div><div class="text-muted small">Commandes</div><div class="fs-3 fw-bold"><?= $stats['nbCommandes'] ?></div></div>
          <i class="bi bi-cart3 stat-icon text-warning"></i>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card stat-card red h-100">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div><div class="text-muted small">Chiffre d'affaires</div><div class="fs-4 fw-bold"><?= number_format($ca, 0, ',', ' ') ?> F</div></div>
          <i class="bi bi-currency-exchange stat-icon text-danger"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <!-- Graphique CA par mois -->
    <div class="col-lg-8">
      <div class="card h-100">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-graph-up me-2"></i>Chiffre d'affaires par mois</div>
        <div class="card-body">
          <canvas id="chartCA" height="260"></canvas>
        </div>
      </div>
    </div>
    <!-- Top produits -->
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-trophy me-2"></i>Top 5 produits</div>
        <div class="card-body p-0">
          <canvas id="chartTop" height="260"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <!-- Commandes récentes -->
    <div class="col-lg-7">
      <div class="card">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-clock-history me-2"></i>Commandes récentes</div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>#</th><th>Date</th><th>Client</th><th>Lignes</th><th class="text-end">Montant</th></tr></thead>
            <tbody>
            <?php foreach ($recentCmd as $r): ?>
              <tr>
                <td><a href="commande_detail.php?id=<?= $r['idCmd'] ?>"><?= $r['idCmd'] ?></a></td>
                <td><?= h($r['dateCmd']) ?></td>
                <td><?= h($r['client']) ?></td>
                <td><span class="badge bg-secondary"><?= $r['nbLignes'] ?></span></td>
                <td class="text-end"><?= number_format($r['montant'] ?? 0, 0, ',', ' ') ?> F</td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Stock faible + CA par client -->
    <div class="col-lg-5">
      <?php if ($stockFaible): ?>
      <div class="card mb-3">
        <div class="card-header bg-white fw-semibold text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Stock faible</div>
        <ul class="list-group list-group-flush">
          <?php foreach ($stockFaible as $s): ?>
            <li class="list-group-item d-flex justify-content-between">
              <?= h($s['designProd']) ?>
              <span class="badge <?= $s['qteStock'] == 0 ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= $s['qteStock'] ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-person-lines-fill me-2"></i>CA par client</div>
        <ul class="list-group list-group-flush">
          <?php foreach ($caParClient as $cc): ?>
            <li class="list-group-item d-flex justify-content-between">
              <span><?= h($cc['client']) ?> <small class="text-muted">(<?= $cc['nbCmd'] ?> cmd)</small></span>
              <strong><?= number_format($cc['ca'], 0, ',', ' ') ?> F</strong>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// CA par mois
new Chart(document.getElementById('chartCA'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_column($ventesParMois, 'mois')) ?>,
    datasets: [{
      label: 'CA (FCFA)',
      data: <?= json_encode(array_map('floatval', array_column($ventesParMois, 'ca'))) ?>,
      backgroundColor: 'rgba(13,110,253,0.6)',
      borderRadius: 6
    },{
      label: 'Nb commandes',
      data: <?= json_encode(array_map('intval', array_column($ventesParMois, 'nbCmd'))) ?>,
      type: 'line',
      borderColor: '#fd7e14',
      yAxisID: 'y1',
      tension: 0.3
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    scales: {
      y:  { beginAtZero: true, ticks: { callback: v => v.toLocaleString('fr') + ' F' }},
      y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }}
    }
  }
});

// Top produits (doughnut)
new Chart(document.getElementById('chartTop'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_column($topProduits, 'designProd')) ?>,
    datasets: [{ data: <?= json_encode(array_map('intval', array_column($topProduits, 'totalQte'))) ?>,
      backgroundColor: ['#0d6efd','#198754','#fd7e14','#dc3545','#6f42c1'] }]
  },
  options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 }}}}
});
</script>
</body>
</html>
