<?php
/**
 * =============================================================================
 *  form.php — Formulaire partagé création / modification
 * =============================================================================
 *  GET ?id=XXX : charge l'étudiant à modifier ; sans identifiant : formulaire vierge.
 *  Les valeurs saisies lors d'un échec de validation sont restituées ($_SESSION).
 * =============================================================================
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$id  = isset($_GET['id']) ? trim((string)$_GET['id']) : null;
$etu = array_fill_keys(CHAMPS, '');

/* Chargement de la fiche existante */
if ($id !== null && $id !== '') {
    $requete = $pdo->prepare('SELECT * FROM etudiant WHERE idEtu = :id');
    $requete->execute([':id' => $id]);
    $ligne = $requete->fetch();
    if (!$ligne) {
        flash('danger', 'Étudiant introuvable : le matricule « ' . $id . ' » n’existe pas.');
        rediriger('index.php');
    }
    $etu = array_merge($etu, $ligne);
}

/* Restitution de la saisie en cas d'erreur de validation */
$ancien = saisie_precedente();
if ($ancien) {
    $etu = array_merge($etu, array_intersect_key($ancien, $etu));
}

/* Listes d'aide à la saisie (suggestions) */
$suggestions = [
    'mentionEtu'  => $pdo->query("SELECT DISTINCT mentionEtu  FROM etudiant WHERE mentionEtu  <> '' ORDER BY mentionEtu")->fetchAll(PDO::FETCH_COLUMN),
    'regEtu'      => $pdo->query("SELECT DISTINCT regEtu      FROM etudiant WHERE regEtu      <> '' ORDER BY regEtu")->fetchAll(PDO::FETCH_COLUMN),
    'departEtu'   => $pdo->query("SELECT DISTINCT departEtu   FROM etudiant WHERE departEtu   <> '' ORDER BY departEtu")->fetchAll(PDO::FETCH_COLUMN),
    'arrondisEtu' => $pdo->query("SELECT DISTINCT arrondisEtu FROM etudiant WHERE arrondisEtu <> '' ORDER BY arrondisEtu")->fetchAll(PDO::FETCH_COLUMN),
    'ParcoursEtu' => $pdo->query("SELECT DISTINCT ParcoursEtu FROM etudiant WHERE ParcoursEtu <> '' ORDER BY ParcoursEtu")->fetchAll(PDO::FETCH_COLUMN),
];

$mode        = ($id !== null && $id !== '') ? 'update' : 'create';
$titre_page  = $mode === 'update' ? 'Modifier un étudiant' : 'Nouvel étudiant';
$page_active = $mode === 'update' ? 'etudiants' : 'nouveau';
require __DIR__ . '/includes/header.php';
?>

<div class="entete-page d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div>
    <h1><?= $mode === 'update' ? 'Modifier' : 'Nouvel' ?> étudiant</h1>
    <p class="text-secondary mb-0">
      Les champs marqués d’un astérisque sont obligatoires. Le matricule sert d’identifiant unique.
    </p>
  </div>
  <a class="btn btn-outline-secondary" href="index.php"><i class="bi bi-arrow-left"></i> Retour à la liste</a>
</div>

<div class="carte-bloc">
  <form method="post" action="save.php" data-chargement novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="mode" value="<?= h($mode) ?>">

    <div class="row g-3">
      <?php foreach (CHAMPS as $champ): ?>
        <?php
          $libelle = LIBELLES[$champ];
          $type    = TYPES[$champ] ?? 'text';
          $valeur  = (string)($etu[$champ] ?? '');
          $lectureSeule = ($mode === 'update' && $champ === 'idEtu');
          $colonne = in_array($champ, ['nomEtu', 'prenomEtu'], true) ? 'col-12 col-md-6' : 'col-12 col-md-4';
        ?>
        <div class="<?= $colonne ?>">
          <label class="form-label" for="<?= h($champ) ?>">
            <?= h($libelle) ?> <span class="text-danger" aria-hidden="true">*</span>
          </label>

          <?php if ($champ === 'cycleEtu'): ?>
            <select class="form-select" id="<?= h($champ) ?>" name="<?= h($champ) ?>" required>
              <option value="">— Choisir un cycle —</option>
              <?php foreach (CYCLES as $valeurCycle): ?>
                <option value="<?= h($valeurCycle) ?>" <?= $valeur === $valeurCycle ? 'selected' : '' ?>>
                  <?= h($valeurCycle) ?></option>
              <?php endforeach; ?>
              <?php if ($valeur !== '' && !in_array($valeur, CYCLES, true)): ?>
                <option value="<?= h($valeur) ?>" selected><?= h($valeur) ?></option>
              <?php endif; ?>
            </select>

          <?php elseif (isset($suggestions[$champ])): ?>
            <input class="form-control" id="<?= h($champ) ?>" name="<?= h($champ) ?>" type="text"
                   value="<?= h($valeur) ?>" list="liste-<?= h($champ) ?>" maxlength="60" required>
            <datalist id="liste-<?= h($champ) ?>">
              <?php foreach ($suggestions[$champ] as $suggestion): ?>
                <option value="<?= h($suggestion) ?>"></option>
              <?php endforeach; ?>
            </datalist>

          <?php else: ?>
            <input class="form-control" id="<?= h($champ) ?>" name="<?= h($champ) ?>" type="<?= h($type) ?>"
                   value="<?= h($valeur) ?>"
                   maxlength="<?= (int)(LONGUEURS_MAX[$champ] ?? 60) ?>"
                   <?= $champ === 'emailEtu' ? 'inputmode="email"' : '' ?>
                   <?= $champ === 'numTelEtu' ? 'inputmode="tel" pattern="\+?[0-9]{8,15}"' : '' ?>
                   <?= $lectureSeule ? 'readonly' : '' ?> required>
            <?php if ($lectureSeule): ?>
              <div class="form-text">Le matricule ne peut pas être modifié.</div>
            <?php elseif ($champ === 'numTelEtu'): ?>
              <div class="form-text">8 à 15 chiffres, indicatif « + » accepté.</div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <hr class="my-4">

    <div class="d-flex flex-wrap gap-2">
      <button class="btn btn-primary" type="submit">
        <i class="bi bi-check2-circle"></i> Enregistrer
      </button>
      <?php if ($mode === 'update'): ?>
        <a class="btn btn-outline-dark" href="carte.php?id=<?= urlencode((string)$id) ?>">
          <i class="bi bi-person-vcard"></i> Voir la carte</a>
      <?php endif; ?>
      <a class="btn btn-link ms-auto" href="index.php">Annuler</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
