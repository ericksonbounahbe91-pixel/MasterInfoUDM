<?php
require_once 'config.php';
if (!empty($_SESSION['user'])) redirect('dashboard.php');
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    check_csrf();
    $st=$pdo->prepare('SELECT idUser,nom,email,motDePasse,role FROM users WHERE email=?');
    $st->execute([strtolower(trim($_POST['email']??''))]); $u=$st->fetch();
    if ($u && password_verify($_POST['password']??'', $u['motDePasse'])) {
        session_regenerate_id(true);
        $_SESSION['user']=['idUser'=>$u['idUser'],'nom'=>$u['nom'],'email'=>$u['email'],'role'=>$u['role']];
        redirect('dashboard.php');
    } else $error='E-mail ou mot de passe incorrect.';
}
$pageTitle='Connexion'; require 'includes/header.php';
?>
<div class="auth-card"><h1>Connexion</h1><p class="muted">Authentifiez-vous pour accéder à la gestion des commandes.</p>
<?php if($error): ?><div class="alert error"><?=h($error)?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>">
<label>E-mail</label><input type="email" name="email" required autocomplete="username">
<label>Mot de passe</label><input type="password" name="password" required autocomplete="current-password">
<button class="btn primary full">Se connecter</button></form><p>Pas encore de compte ? <a href="register.php">Créer un compte</a></p></div>
<?php require 'includes/footer.php'; ?>
