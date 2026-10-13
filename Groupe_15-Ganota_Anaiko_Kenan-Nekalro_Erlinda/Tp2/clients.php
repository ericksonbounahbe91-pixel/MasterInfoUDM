<?php
require_once 'config.php';require_login();
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();require_admin();$nom=trim($_POST['nomCli']??'');$prenom=trim($_POST['prenomCli']??'');$email=strtolower(trim($_POST['email']??''));$region=(int)($_POST['idRegion']??0);
 if($nom===''||$prenom===''||!filter_var($email,FILTER_VALIDATE_EMAIL)){flash('Informations client invalides.','error');redirect('clients.php');}
 try{$s=$pdo->prepare('INSERT INTO Client(nomCli,prenomCli,email,idRegion) VALUES(?,?,?,?)');$s->execute([$nom,$prenom,$email,$region?:null]);flash('Client ajouté.');}catch(PDOException $e){flash('E-mail déjà utilisé ou région invalide.','error');}redirect('clients.php');}
$regions=$pdo->query('SELECT idRegion,nomRegion FROM Region ORDER BY nomRegion')->fetchAll();
$rows=$pdo->query('SELECT c.*,r.nomRegion,d.nomDep FROM Client c LEFT JOIN Region r ON c.idRegion=r.idRegion LEFT JOIN `Département` d ON r.idDep=d.idDep ORDER BY c.idCli DESC')->fetchAll();
$pageTitle='Clients';require 'includes/header.php';
?>
<div class="page-heading"><div><h1>Clients</h1><p class="muted">Liste des clients et leur localisation.</p></div></div>
<?php if($_SESSION['user']['role']==='admin'): ?><section class="panel"><h2>Ajouter un client</h2><form method="post" class="form-grid"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><div><label>Nom</label><input name="nomCli" required></div><div><label>Prénom</label><input name="prenomCli" required></div><div><label>E-mail</label><input type="email" name="email" required></div><div><label>Région</label><select name="idRegion"><option value="">Non précisée</option><?php foreach($regions as $r): ?><option value="<?=$r['idRegion']?>"><?=h($r['nomRegion'])?></option><?php endforeach; ?></select></div><div class="form-submit"><button class="btn primary">Ajouter le client</button></div></form></section><?php endif; ?>
<section class="panel"><h2>Liste des clients (<?=count($rows)?>)</h2><div class="table-wrap"><table><thead><tr><th>ID</th><th>Nom complet</th><th>E-mail</th><th>Région</th><th>Département</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?=h($r['idCli'])?></td><td><?=h($r['prenomCli'].' '.$r['nomCli'])?></td><td><?=h($r['email'])?></td><td><?=h($r['nomRegion']??'—')?></td><td><?=h($r['nomDep']??'—')?></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php require 'includes/footer.php'; ?>
