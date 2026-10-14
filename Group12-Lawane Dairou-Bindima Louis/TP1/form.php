<?php
/** TP5 – Formulaire partagé création / modification.
 *  GET ?id=XXX : charge l'étudiant à modifier ; sans id : formulaire vierge. */
require 'config.php';

$id = $_GET['id'] ?? null;
$id = is_string($id) && $id !== '' ? $id : null;
$etu = array_fill_keys(CHAMPS, '');
if ($id !== null) {
    $stmt = $pdo->prepare('SELECT * FROM etudiant WHERE idEtu = ?');
    $stmt->execute([$id]);
    $etu = $stmt->fetch() ?: exit('Étudiant introuvable.');
}
// Après une erreur de validation : on réaffiche ce que l'utilisateur avait saisi
$erreurs = $_SESSION['errors'] ?? [];
if (isset($_SESSION['old'])) { $etu = array_merge($etu, $_SESSION['old']); }
unset($_SESSION['errors'], $_SESSION['old']);

$choix = ['regEtu' => REGIONS, 'mentionEtu' => MENTIONS, 'cycleEtu' => CYCLES];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $id ? 'Modifier' : 'Créer' ?> un étudiant</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body>
<div class="container py-4" style="max-width:760px">
  <h1 class="h3 bandeau mb-3"><?= $id ? 'Modifier' : 'Nouvel' ?> étudiant</h1>

  <?php if ($erreurs): ?>
    <div class="alert alert-danger"><ul class="mb-0">
      <?php foreach ($erreurs as $m): ?><li><?= h($m) ?></li><?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <form method="post" action="save.php" class="carte p-4 shadow-sm row g-3">
    <?= csrf_field() ?>
    <input type="hidden" name="mode" value="<?= $id ? 'update' : 'create' ?>">
    <?php foreach (CHAMPS as $c): ?>
      <div class="col-md-6">
        <label class="form-label" for="<?= $c ?>"><?= h(LIBELLES[$c]) ?></label>
        <?php if (isset($choix[$c])): ?>
          <select class="form-select" id="<?= $c ?>" name="<?= $c ?>" required>
            <option value="">-- Choisir --</option>
            <?php foreach ($choix[$c] as $o): ?>
              <option value="<?= h($o) ?>" <?= $etu[$c] === $o ? 'selected' : '' ?>><?= h($o) ?></option>
            <?php endforeach; ?>
          </select>
        <?php else: ?>
          <input class="form-control" id="<?= $c ?>" name="<?= $c ?>" maxlength="150" required
                 type="<?= $c === 'dateNaiss' ? 'date' : ($c === 'emailEtu' ? 'email' : ($c === 'numTelEtu' ? 'tel' : 'text')) ?>"
                 <?= $c === 'dateNaiss' ? 'max="' . date('Y-m-d') . '"' : '' ?>
                 <?= $c === 'idEtu' ? 'pattern="[A-Za-z0-9_\-]{3,20}" title="3 à 20 caractères : lettres, chiffres, - ou _"' : '' ?>
                 <?= $c === 'numTelEtu' ? 'pattern="\+?[0-9 ]{8,15}" title="8 à 15 chiffres"' : '' ?>
                 value="<?= h((string)$etu[$c]) ?>"
                 <?= ($id && $c === 'idEtu') ? 'readonly' : '' ?>>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <div class="col-12">
      <button class="btn btn-accent px-4">Enregistrer</button>
      <a class="btn btn-link" href="index.php">Annuler</a>
    </div>
  </form>
</div>
</body>
</html>
