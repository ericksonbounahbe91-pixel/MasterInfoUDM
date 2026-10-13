<?php
require_once 'config.php'; require_login();
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();$action=$_POST['action']??'';
 if($action==='save'){
  $id=(int)($_POST['idProd']??0);$design=trim($_POST['designProd']??'');$stock=filter_var($_POST['qteStock']??null,FILTER_VALIDATE_INT);$prix=filter_var($_POST['prixUProd']??null,FILTER_VALIDATE_FLOAT);
  if($design===''||$stock===false||$stock<0||$prix===false||$prix<0){flash('Veuillez vérifier les champs du produit.','error');redirect('produits.php');}
  if($id){$s=$pdo->prepare('UPDATE Produit SET designProd=?,qteStock=?,prixUProd=? WHERE idProd=?');$s->execute([$design,$stock,$prix,$id]);flash('Produit modifié.');}
  else {$s=$pdo->prepare('INSERT INTO Produit(designProd,qteStock,prixUProd) VALUES(?,?,?)');$s->execute([$design,$stock,$prix]);flash('Produit ajouté.');}
  redirect('produits.php');
 }
 if($action==='delete' && $_SESSION['user']['role']==='admin'){try{$s=$pdo->prepare('DELETE FROM Produit WHERE idProd=?');$s->execute([(int)$_POST['idProd']]);flash('Produit supprimé.');}catch(PDOException $e){flash('Ce produit figure dans une commande. Supprimez d’abord ses lignes de commande.','error');}redirect('produits.php');}
}
$edit=null;if(isset($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM Produit WHERE idProd=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch();}
$search=trim($_GET['q']??'');$s=$pdo->prepare('SELECT * FROM Produit WHERE designProd LIKE ? ORDER BY idProd DESC');$s->execute(['%'.$search.'%']);$rows=$s->fetchAll();
$pageTitle='Produits';require 'includes/header.php';
?>
<div class="page-heading"><div><h1>Produits</h1><p class="muted">Créer, rechercher et modifier le catalogue.</p></div></div>
<div class="two-col"><section class="panel"><h2><?=$edit?'Modifier le produit':'Ajouter un produit'?></h2>
<form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="idProd" value="<?=h($edit['idProd']??'')?>">
<label>Désignation</label><input name="designProd" required maxlength="160" value="<?=h($edit['designProd']??'')?>">
<label>Quantité en stock</label><input type="number" name="qteStock" min="0" required value="<?=h($edit['qteStock']??0)?>">
<label>Prix unitaire (FCFA)</label><input type="number" name="prixUProd" min="0" step="0.01" required value="<?=h($edit['prixUProd']??0)?>">
<button class="btn primary">Enregistrer</button><?php if($edit): ?> <a class="btn secondary" href="produits.php">Annuler</a><?php endif; ?></form></section>
<section class="panel"><h2>Recherche</h2><form method="get" class="inline-form"><input name="q" placeholder="Nom du produit…" value="<?=h($search)?>"><button class="btn secondary">Rechercher</button></form><p class="muted">La suppression d’un produit du catalogue est réservée à l’administrateur.</p></section></div>
<section class="panel"><h2>Liste des produits (<?=count($rows)?>)</h2><div class="table-wrap"><table><thead><tr><th>ID</th><th>Désignation</th><th>Stock</th><th>Prix unitaire</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?=h($r['idProd'])?></td><td><?=h($r['designProd'])?></td><td><?=h($r['qteStock'])?></td><td><?=number_format((float)$r['prixUProd'],0,',',' ')?> FCFA</td><td class="actions"><a class="btn small secondary" href="produits.php?edit=<?=$r['idProd']?>">Modifier</a><?php if($_SESSION['user']['role']==='admin'): ?><form method="post" class="inline delete-form"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="idProd" value="<?=$r['idProd']?>"><button class="btn small danger">Supprimer</button></form><?php endif; ?></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="5">Aucun produit trouvé.</td></tr><?php endif; ?></tbody></table></div></section>
<?php require 'includes/footer.php'; ?>
