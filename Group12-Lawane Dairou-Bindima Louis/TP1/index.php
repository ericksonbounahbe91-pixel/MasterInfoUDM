<?php
/** TP5 – READ : liste des étudiants (GET) avec recherche, filtres, tri et pagination. */
require 'config.php';

const PAR_PAGE = 10;
$colonnes = ['idEtu'=>'Matricule','nomEtu'=>'Nom','prenomEtu'=>'Prénom','emailEtu'=>'Email','mentionEtu'=>'Mention','cycleEtu'=>'Cycle'];

// Paramètres (le tri est limité à une liste blanche : jamais de colonne venant de l'utilisateur dans le SQL)
$q       = get_str('q');
$mention = in_array(get_str('mention'), MENTIONS, true) ? get_str('mention') : '';
$cycle   = in_array(get_str('cycle'), CYCLES, true) ? get_str('cycle') : '';
$tri     = isset($colonnes[get_str('tri')]) ? get_str('tri') : 'nomEtu';
$dir     = get_str('dir') === 'desc' ? 'DESC' : 'ASC';

$where = []; $p = [];
if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $where[] = '(nomEtu LIKE :q1 OR prenomEtu LIKE :q2 OR idEtu LIKE :q3 OR emailEtu LIKE :q4)';
    $p += [':q1' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like];
}
if ($mention !== '') { $where[] = 'mentionEtu = :mention'; $p[':mention'] = $mention; }
if ($cycle !== '')   { $where[] = 'cycleEtu = :cycle';     $p[':cycle'] = $cycle; }
$clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM etudiant $clause");
$stmt->execute($p);
$total  = (int)$stmt->fetchColumn();
$pages  = max(1, (int)ceil($total / PAR_PAGE));
$page   = min(max(1, (int)get_str('page')), $pages);
$offset = ($page - 1) * PAR_PAGE;

$stmt = $pdo->prepare(
    "SELECT idEtu, nomEtu, prenomEtu, emailEtu, mentionEtu, cycleEtu
       FROM etudiant $clause
      ORDER BY $tri $dir, idEtu
      LIMIT " . PAR_PAGE . " OFFSET $offset"
);
$stmt->execute($p);
$etudiants = $stmt->fetchAll();
$flash = flash_lire();

/** Construit une URL en conservant les paramètres courants. */
function lien(array $change): string { return '?' . http_build_query(array_merge($_GET, $change)); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestion des étudiants</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="bandeau d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Étudiants <span class="badge text-bg-secondary"><?= $total ?></span></h1>
    <a class="btn btn-light fw-semibold" href="form.php">+ Nouvel étudiant</a>
  </div>

  <form class="row g-2 mb-3" method="get">
    <div class="col-12 col-md-4"><input class="form-control" name="q" placeholder="Nom, prénom, matricule, email…" value="<?= h($q) ?>"></div>
    <div class="col-6 col-md-3">
      <select class="form-select" name="mention">
        <option value="">Toutes les mentions</option>
        <?php foreach (MENTIONS as $o): ?><option <?= $mention === $o ? 'selected' : '' ?>><?= h($o) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <select class="form-select" name="cycle">
        <option value="">Tous les cycles</option>
        <?php foreach (CYCLES as $o): ?><option <?= $cycle === $o ? 'selected' : '' ?>><?= h($o) ?></option><?php endforeach; ?>
      </select>
    </div>
    <input type="hidden" name="tri" value="<?= h($tri) ?>">
    <input type="hidden" name="dir" value="<?= strtolower($dir) ?>">
    <div class="col-auto">
      <button class="btn btn-primary">Filtrer</button>
      <a class="btn btn-link" href="index.php">Réinitialiser</a>
    </div>
  </form>

  <?php if ($flash): ?>
    <div class="alert alert-<?= h($flash['type']) ?> alert-dismissible fade show">
      <?= h($flash['msg']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-striped table-hover bg-white align-middle shadow-sm">
      <thead><tr>
        <?php foreach ($colonnes as $cle => $lib):
            $actif = ($tri === $cle);
            $suivant = ($actif && $dir === 'ASC') ? 'desc' : 'asc'; ?>
          <th><a class="text-decoration-none text-dark" href="<?= h(lien(['tri' => $cle, 'dir' => $suivant, 'page' => 1])) ?>">
            <?= h($lib) ?><?= $actif ? ($dir === 'ASC' ? ' ▲' : ' ▼') : '' ?></a></th>
        <?php endforeach; ?>
        <th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($etudiants as $e): ?>
        <tr>
          <td><?= h($e['idEtu']) ?></td><td><?= h($e['nomEtu']) ?></td><td><?= h($e['prenomEtu']) ?></td>
          <td><?= h($e['emailEtu']) ?></td><td><span class="pastille mention"><?= h($e['mentionEtu']) ?></span></td>
          <td><span class="pastille cycle-<?= h(strtolower($e['cycleEtu'])) ?>"><?= h($e['cycleEtu']) ?></span></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-primary" href="form.php?id=<?= urlencode($e['idEtu']) ?>">Modifier</a>
            <!-- Suppression par POST + jeton CSRF (jamais par GET) -->
            <form method="post" action="delete.php" class="d-inline" onsubmit="return confirm('Supprimer cet étudiant ?');">
              <?= csrf_field() ?>
              <input type="hidden" name="idEtu" value="<?= h($e['idEtu']) ?>">
              <button class="btn btn-sm btn-outline-danger">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$etudiants): ?><tr><td colspan="7" class="text-center text-muted">Aucun résultat.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
  <nav><ul class="pagination justify-content-center">
    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= h(lien(['page' => $page - 1])) ?>">«</a></li>
    <?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?>
      <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="<?= h(lien(['page' => $i])) ?>"><?= $i ?></a></li>
    <?php endfor; ?>
    <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>"><a class="page-link" href="<?= h(lien(['page' => $page + 1])) ?>">»</a></li>
  </ul></nav>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
