<?php
require_once 'config.php'; require_login();
$pageTitle='Tableau de bord';
$stats=[
 'produits'=>(int)$pdo->query('SELECT COUNT(*) FROM Produit')->fetchColumn(),
 'clients'=>(int)$pdo->query('SELECT COUNT(*) FROM Client')->fetchColumn(),
 'commandes'=>(int)$pdo->query('SELECT COUNT(*) FROM Commande')->fetchColumn(),
 'lignes'=>(int)$pdo->query('SELECT COUNT(*) FROM ligneCommande')->fetchColumn(),
 'chiffre'=>(float)$pdo->query('SELECT COALESCE(SUM(qteCmd*prixUCommande),0) FROM ligneCommande')->fetchColumn(),
 'stock'=>(int)$pdo->query('SELECT COALESCE(SUM(qteStock),0) FROM Produit')->fetchColumn()
];
$top=$pdo->query("SELECT p.designProd,SUM(l.qteCmd) quantite,SUM(l.qteCmd*l.prixUCommande) total FROM ligneCommande l JOIN Produit p ON p.idProd=l.idProd GROUP BY p.idProd ORDER BY quantite DESC LIMIT 5")->fetchAll();
$monthly=$pdo->query("SELECT DATE_FORMAT(c.dateCmd,'%Y-%m') mois,COUNT(DISTINCT c.idCmd) commandes,COALESCE(SUM(l.qteCmd*l.prixUCommande),0) chiffre FROM Commande c LEFT JOIN ligneCommande l ON l.idCmd=c.idCmd GROUP BY DATE_FORMAT(c.dateCmd,'%Y-%m') ORDER BY mois DESC LIMIT 6")->fetchAll();
$pageTitle='Tableau de bord'; require 'includes/header.php';
?>
<div class="page-heading"><div><h1>Tableau de bord</h1><p class="muted">Vue d’ensemble et indicateurs de performance.</p></div></div>
<div class="stats-grid">
<div class="stat-card"><span>Produits</span><strong><?=$stats['produits']?></strong></div><div class="stat-card"><span>Clients</span><strong><?=$stats['clients']?></strong></div><div class="stat-card"><span>Commandes</span><strong><?=$stats['commandes']?></strong></div><div class="stat-card"><span>Lignes de commande</span><strong><?=$stats['lignes']?></strong></div><div class="stat-card"><span>Chiffre d’affaires estimé</span><strong><?=number_format($stats['chiffre'],0,',',' ')?> FCFA</strong></div><div class="stat-card"><span>Unités en stock</span><strong><?=$stats['stock']?></strong></div>
</div>
<div class="panel"><h2>Produits les plus commandés</h2><div class="table-wrap"><table><thead><tr><th>Produit</th><th>Quantité vendue</th><th>Montant</th></tr></thead><tbody>
<?php foreach($top as $r): ?><tr><td><?=h($r['designProd'])?></td><td><?=h($r['quantite'])?></td><td><?=number_format((float)$r['total'],0,',',' ')?> FCFA</td></tr><?php endforeach; ?>
<?php if(!$top): ?><tr><td colspan="3">Aucune ligne de commande pour le moment.</td></tr><?php endif; ?></tbody></table></div></div>
<div class="panel"><h2>Performance mensuelle (6 derniers mois actifs)</h2><p class="muted">Le chiffre d’affaires est calculé avec le prix enregistré dans chaque ligne au moment de l’ajout.</p><div class="table-wrap"><table><thead><tr><th>Mois</th><th>Commandes</th><th>Chiffre d’affaires</th></tr></thead><tbody>
<?php foreach($monthly as $r): ?><tr><td><?=h($r['mois'])?></td><td><?=h($r['commandes'])?></td><td><?=number_format((float)$r['chiffre'],0,',',' ')?> FCFA</td></tr><?php endforeach; ?></tbody></table></div></div>
<?php require 'includes/footer.php'; ?>
