<?php
require_once 'config.php';require_login();
$idCmd=(int)($_GET['idCmd']??$_POST['idCmd']??0);
$s=$pdo->prepare('SELECT c.*,cl.nomCli,cl.prenomCli FROM Commande c JOIN Client cl ON cl.idCli=c.idCli WHERE c.idCmd=?');$s->execute([$idCmd]);$commande=$s->fetch();
if(!$commande){flash('Commande introuvable.','error');redirect('commandes.php');}
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();$action=$_POST['action']??'';
 if($action==='add'){
  $idProd=(int)($_POST['idProd']??0);$qte=filter_var($_POST['qteCmd']??null,FILTER_VALIDATE_INT);
  try{
   if(!$idProd||$qte===false||$qte<1)throw new RuntimeException('Sélectionnez un produit et une quantité valide.');
   $pdo->beginTransaction();
   $p=$pdo->prepare('SELECT * FROM Produit WHERE idProd=? FOR UPDATE');$p->execute([$idProd]);$prod=$p->fetch();
   if(!$prod)throw new RuntimeException('Produit introuvable.');
   $old=$pdo->prepare('SELECT qteCmd FROM ligneCommande WHERE idCmd=? AND idProd=?');$old->execute([$idCmd,$idProd]);$existing=$old->fetch();
   $needed=$qte+(int)($existing['qteCmd']??0);
   if($needed>(int)$prod['qteStock'])throw new RuntimeException('Stock insuffisant. Stock disponible : '.$prod['qteStock'].'.');
   if($existing){$up=$pdo->prepare('UPDATE ligneCommande SET qteCmd=qteCmd+? WHERE idCmd=? AND idProd=?');$up->execute([$qte,$idCmd,$idProd]);}
   else{$up=$pdo->prepare('INSERT INTO ligneCommande(idCmd,idProd,qteCmd,prixUCommande) VALUES(?,?,?,?)');$up->execute([$idCmd,$idProd,$qte,$prod['prixUProd']]);}
   $pdo->prepare('UPDATE Produit SET qteStock=qteStock-? WHERE idProd=?')->execute([$qte,$idProd]);
   $pdo->commit();flash('Produit ajouté à la commande.');
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash($e instanceof RuntimeException?$e->getMessage():'Impossible d’ajouter ce produit.','error');}
  redirect('ligne_commande.php?idCmd='.$idCmd);
 }
 if($action==='delete_line'){
  try{$pdo->beginTransaction();$q=$pdo->prepare('SELECT qteCmd FROM ligneCommande WHERE idCmd=? AND idProd=? FOR UPDATE');$q->execute([$idCmd,(int)$_POST['idProd']]);$line=$q->fetch();if($line){$pdo->prepare('UPDATE Produit SET qteStock=qteStock+? WHERE idProd=?')->execute([(int)$line['qteCmd'],(int)$_POST['idProd']]);$pdo->prepare('DELETE FROM ligneCommande WHERE idCmd=? AND idProd=?')->execute([$idCmd,(int)$_POST['idProd']]);}$pdo->commit();flash('Ligne supprimée et stock restauré.');}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash('Erreur lors de la suppression.','error');}
  redirect('ligne_commande.php?idCmd='.$idCmd);
 }
}
$products=$pdo->query('SELECT idProd,designProd,qteStock,prixUProd FROM Produit WHERE qteStock>0 ORDER BY designProd')->fetchAll();
$s=$pdo->prepare('SELECT l.*,p.designProd FROM ligneCommande l JOIN Produit p ON p.idProd=l.idProd WHERE l.idCmd=? ORDER BY p.designProd');$s->execute([$idCmd]);$lines=$s->fetchAll();
$total=0;foreach($lines as $l)$total+=(int)$l['qteCmd']*(float)$l['prixUCommande'];
$pageTitle='Ligne de commande';require 'includes/header.php';
?>
<div class="page-heading"><div><h1>Commande #<?=h($idCmd)?></h1><p class="muted">Client : <?=h($commande['prenomCli'].' '.$commande['nomCli'])?> · Date : <?=h($commande['dateCmd'])?></p></div><a class="btn secondary" href="commandes.php">Retour aux commandes</a></div>
<section class="panel"><h2>Ajouter un produit à cette commande</h2><?php if(!$products): ?><p>Aucun produit disponible en stock. Ajoutez des produits ou réapprovisionnez le stock.</p><?php else: ?><form method="post" class="form-grid"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="add"><input type="hidden" name="idCmd" value="<?=$idCmd?>"><div><label>Produit</label><select name="idProd" required><option value="">Choisir un produit</option><?php foreach($products as $p): ?><option value="<?=$p['idProd']?>"><?=h($p['designProd'])?> — stock <?=$p['qteStock']?> — <?=number_format((float)$p['prixUProd'],0,',',' ')?> FCFA</option><?php endforeach; ?></select></div><div><label>Quantité</label><input type="number" name="qteCmd" min="1" value="1" required></div><div class="form-submit"><button class="btn primary">Ajouter à la commande</button></div></form><?php endif; ?></section>
<section class="panel"><h2>Produits commandés</h2><div class="table-wrap"><table><thead><tr><th>Produit</th><th>Quantité</th><th>Prix unitaire enregistré</th><th>Sous-total</th><th>Action</th></tr></thead><tbody><?php foreach($lines as $l): ?><tr><td><?=h($l['designProd'])?></td><td><?=h($l['qteCmd'])?></td><td><?=number_format((float)$l['prixUCommande'],0,',',' ')?> FCFA</td><td><?=number_format((int)$l['qteCmd']*(float)$l['prixUCommande'],0,',',' ')?> FCFA</td><td><form method="post" class="delete-form"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="delete_line"><input type="hidden" name="idCmd" value="<?=$idCmd?>"><input type="hidden" name="idProd" value="<?=$l['idProd']?>"><button class="btn small danger">Retirer de la commande</button></form></td></tr><?php endforeach; ?><?php if(!$lines): ?><tr><td colspan="5">Cette commande ne contient encore aucun produit.</td></tr><?php endif; ?></tbody><tfoot><tr><th colspan="3">Total commande</th><th colspan="2"><?=number_format($total,0,',',' ')?> FCFA</th></tr></tfoot></table></div></section>
<?php require 'includes/footer.php'; ?>
