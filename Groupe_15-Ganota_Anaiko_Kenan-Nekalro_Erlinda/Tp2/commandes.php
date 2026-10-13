<?php
require_once 'config.php';require_login();
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();$action=$_POST['action']??'';
 if($action==='create'){
  $idCli=(int)($_POST['idCli']??0);$date=trim($_POST['dateCmd']??'');
  if(!$idCli){flash('Sélectionnez un client.','error');redirect('commandes.php');}
  $s=$pdo->prepare('INSERT INTO Commande(dateCmd,idCli) VALUES(?,?)');$s->execute([$date?:date('Y-m-d H:i:s'),$idCli]);flash('Commande créée. Ajoutez ensuite ses produits.');redirect('ligne_commande.php?idCmd='.$pdo->lastInsertId());
 }
 if($action==='delete' && $_SESSION['user']['role']==='admin'){$s=$pdo->prepare('DELETE FROM Commande WHERE idCmd=?');$s->execute([(int)$_POST['idCmd']]);flash('Commande supprimée.');redirect('commandes.php');}
}
$clients=$pdo->query('SELECT idCli,nomCli,prenomCli FROM Client ORDER BY nomCli')->fetchAll();
$rows=$pdo->query('SELECT c.idCmd,c.dateCmd,cl.nomCli,cl.prenomCli,COUNT(l.idProd) nbProduits,COALESCE(SUM(l.qteCmd*l.prixUCommande),0) total FROM Commande c JOIN Client cl ON cl.idCli=c.idCli LEFT JOIN ligneCommande l ON l.idCmd=c.idCmd GROUP BY c.idCmd ORDER BY c.dateCmd DESC')->fetchAll();
$pageTitle='Commandes';require 'includes/header.php';
?>
<div class="page-heading"><div><h1>Commandes</h1><p class="muted">Consultez les commandes ou créez-en une pour un client.</p></div></div>
<section class="panel"><h2>Créer une commande</h2><?php if(!$clients): ?><p>Ajoutez d’abord un client (action administrateur).</p><?php else: ?><form method="post" class="form-grid"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="create"><div><label>Client</label><select name="idCli" required><option value="">Choisir un client</option><?php foreach($clients as $c): ?><option value="<?=$c['idCli']?>"><?=h($c['prenomCli'].' '.$c['nomCli'])?></option><?php endforeach; ?></select></div><div><label>Date (facultatif)</label><input type="datetime-local" name="dateCmd"></div><div class="form-submit"><button class="btn primary">Créer et ajouter les produits</button></div></form><?php endif; ?></section>
<section class="panel"><h2>Liste des commandes (<?=count($rows)?>)</h2><div class="table-wrap"><table><thead><tr><th>N°</th><th>Date</th><th>Client</th><th>Nb produits distincts</th><th>Total</th><th>Actions</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td>#<?=h($r['idCmd'])?></td><td><?=h($r['dateCmd'])?></td><td><?=h($r['prenomCli'].' '.$r['nomCli'])?></td><td><?=h($r['nbProduits'])?></td><td><?=number_format((float)$r['total'],0,',',' ')?> FCFA</td><td class="actions"><a class="btn small secondary" href="ligne_commande.php?idCmd=<?=$r['idCmd']?>">Gérer les lignes</a><?php if($_SESSION['user']['role']==='admin'): ?><form method="post" class="inline delete-form"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="idCmd" value="<?=$r['idCmd']?>"><button class="btn small danger">Supprimer</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php require 'includes/footer.php'; ?>
