<?php
require_once 'config.php';
if (!empty($_SESSION['user'])) redirect('dashboard.php');
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    check_csrf();
    $nom=trim($_POST['nom']??''); $email=strtolower(trim($_POST['email']??''));
    $password=$_POST['password']??''; $confirm=$_POST['confirm']??'';
    if ($nom==='') $errors[]='Le nom est obligatoire.';
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Adresse e-mail invalide.';
    if (strlen($password)<8) $errors[]='Le mot de passe doit contenir au moins 8 caractères.';
    if ($password!==$confirm) $errors[]='Les mots de passe ne correspondent pas.';
    if (!$errors) {
        try {
            $count=(int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
            // Le premier compte devient admin. Les inscriptions suivantes sont des utilisateurs simples.
            $role=$count===0?'admin':'user';
            $st=$pdo->prepare('INSERT INTO users(nom,email,motDePasse,role) VALUES(?,?,?,?)');
            $st->execute([$nom,$email,password_hash($password,PASSWORD_DEFAULT),$role]);
            flash($role==='admin'?'Compte créé. Vous êtes le premier utilisateur et administrateur.':'Compte créé. Connectez-vous.');
            redirect('login.php');
        } catch (PDOException $e) { $errors[]='Cette adresse e-mail est déjà utilisée.'; }
    }
}
$pageTitle='Créer un compte';
require 'includes/header.php';
?>
<div class="auth-card"><h1>Créer un compte</h1><p class="muted">Le premier compte créé reçoit le rôle administrateur. Les comptes suivants sont des utilisateurs simples.</p>
<?php foreach($errors as $err): ?><div class="alert error"><?=h($err)?></div><?php endforeach; ?>
<form method="post">
<input type="hidden" name="csrf" value="<?=h(csrf_token())?>">
<label>Nom complet</label><input name="nom" required value="<?=h($_POST['nom']??'')?>">
<label>Adresse e-mail</label><input type="email" name="email" required value="<?=h($_POST['email']??'')?>">
<label>Mot de passe (8 caractères minimum)</label><input type="password" name="password" minlength="8" required>
<label>Confirmer le mot de passe</label><input type="password" name="confirm" minlength="8" required>
<button class="btn primary full">Créer mon compte</button></form><p>Déjà inscrit ? <a href="login.php">Se connecter</a></p></div>
<?php require 'includes/footer.php'; ?>
