<?php
/**
 * =============================================================================
 *  index.php — READ : liste des étudiants (recherche, filtres, tri, pagination)
 * =============================================================================
 *  Fonctionnalités :
 *    - recherche par nom, prénom ou matricule (paramètre ?q=) ;
 *    - filtres avancés : mention, cycle, département ;
 *    - tri serveur sur toutes les colonnes (liste blanche) ;
 *    - pagination de 10 étudiants par page ;
 *    - recherche live (fetch -> api/search.php) ;
 *    - sélection multiple pour la génération des cartes en lot.
 *  Sécurité : ORDER BY construit depuis une LISTE BLANCHE, LIMIT/OFFSET liés en
 *  entier (PDO::PARAM_INT), toutes les sorties passent par h().
 * =============================================================================
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

/* -----------------------------------------------------------------------------
 * 1. Lecture et nettoyage des paramètres d'URL
 * --------------------------------------------------------------------------- */
$q       = trim((string)($_GET['q'] ?? ''));
$mention = trim((string)($_GET['mention'] ?? ''));
$cycle   = trim((string)($_GET['cycle'] ?? ''));
$depart  = trim((string)($_GET['depart'] ?? ''));
$tri     = colonne_triable(isset($_GET['tri']) ? (string)$_GET['tri'] : null, 'nomEtu');
$ordre   = (strtolower((string)($_GET['ordre'] ?? 'asc')) === 'desc') ? 'DESC' : 'ASC';
$page    = max(1, (int)($_GET['page'] ?? 1));

/* -----------------------------------------------------------------------------
 * 2. Construction de la clause WHERE (paramètres liés, jamais concaténés)
 *    NB : les requêtes préparées natives n'autorisent pas la réutilisation d'un
 *    même marqueur nommé ; on utilise donc :q1, :q2 et :q3 pour la même valeur.
 * --------------------------------------------------------------------------- */
$conditions = [];
$parametres = [];

if ($q !== '') {
    $conditions[]    = '(nomEtu LIKE :q1 OR prenomEtu LIKE :q2 OR idEtu LIKE :q3)';
    $parametres[':q1'] = '%' . $q . '%';
    $parametres[':q2'] = '%' . $q . '%';
    $parametres[':q3'] = '%' . $q . '%';
}
if ($mention !== '') { $conditions[] = 'mentionEtu = :mention'; $parametres[':mention'] = $mention; }
if ($cycle !== '')   { $conditions[] = 'cycleEtu = :cycle';     $parametres[':cycle']   = $cycle; }
if ($depart !== '')  { $conditions[] = 'departEtu = :depart';   $parametres[':depart']  = $depart; }

$clause = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

/* -----------------------------------------------------------------------------
 * 3. Comptage puis extraction de la page courante
 * --------------------------------------------------------------------------- */
$requeteComptage = $pdo->prepare('SELECT COUNT(*) FROM etudiant' . $clause);
$requeteComptage->execute($parametres);
$total   = (int)$requeteComptage->fetchColumn();
$nbPages = max(1, (int)ceil($total / PAR_PAGE));
$page    = min($page, $nbPages);
$offset  = ($page - 1) * PAR_PAGE;

$requete = $pdo->prepare(
    'SELECT * FROM etudiant' . $clause .
    ' ORDER BY ' . $tri . ' ' . $ordre . ' LIMIT :limite OFFSET :decalage'
);
foreach ($parametres as $cle => $valeur) {
    $requete->bindValue($cle, $valeur, PDO::PARAM_STR);
}
$requete->bindValue(':limite', PAR_PAGE, PDO::PARAM_INT);
$requete->bindValue(':decalage', $offset, PDO::PARAM_INT);
$requete->execute();
$etudiants = $requete->fetchAll();

/* -----------------------------------------------------------------------------
 * 4. Données alimentant les filtres et les cartes de statistiques
 * --------------------------------------------------------------------------- */
$listeMentions = $pdo->query(
    "SELECT DISTINCT mentionEtu FROM etudiant WHERE mentionEtu <> '' ORDER BY mentionEtu"
)->fetchAll(PDO::FETCH_COLUMN);

$listeCycles = $pdo->query(
    "SELECT DISTINCT cycleEtu FROM etudiant WHERE cycleEtu <> '' ORDER BY cycleEtu"
)->fetchAll(PDO::FETCH_COLUMN);

$listeDeparts = $pdo->query(
    "SELECT DISTINCT departEtu FROM etudiant WHERE departEtu <> '' ORDER BY departEtu"
)->fetchAll(PDO::FETCH_COLUMN);

$stats = $pdo->query(
    "SELECT COUNT(*) AS total,
            SUM(cycleEtu = 'Licence')  AS licences,
            SUM(cycleEtu = 'Master')   AS masters,
            COUNT(DISTINCT departEtu)  AS departements
       FROM etudiant"
)->fetch();

$titre_page  = 'Étudiants';
$page_active = 'etudiants';
require __DIR__ . '/includes/header.php';
?>

<div class="entete-page d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div>
    <h1>Étudiants</h1>
    <p class="text-secondary mb-0">
      <?= (int)$stats['total'] ?> étudiant(s) enregistré(s) —
      <?= (int)$stats['departements'] ?> département(s) représenté(s)
    </p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-primary" href="form.php"><i class="bi bi-person-plus"></i> Nouvel étudiant</a>
  </div>
</div>

<?php /* ------------------------- Cartes de statistiques --------------------- */ ?>
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="carte-bloc stat">
      <span class="stat-icone"><i class="bi bi-people-fill"></i></span>
      <span><span class="stat-valeur"><?= (int)$stats['total'] ?></span>
        <span class="stat-libelle d-block">Étudiants</span></span>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="carte-bloc stat">
      <span class="stat-icone" style="background:#0d6efd"><i class="bi bi-book"></i></span>
      <span><span class="stat-valeur"><?= (int)$stats['licences'] ?></span>
        <span class="stat-libelle d-block">Licence</span></span>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="carte-bloc stat">
      <span class="stat-icone" style="background:#6f42c1"><i class="bi bi-mortarboard"></i></span>
      <span><span class="stat-valeur"><?= (int)$stats['masters'] ?></span>
        <span class="stat-libelle d-block">Master</span></span>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="carte-bloc stat">
      <span class="stat-icone" style="background:#198754"><i class="bi bi-geo-alt"></i></span>
      <span><span class="stat-valeur"><?= (int)$stats['departements'] ?></span>
        <span class="stat-libelle d-block">Départements</span></span>
    </div>
  </div>
</div>

<?php /* --------------------- Recherche, filtres et tri ---------------------- */ ?>
<div class="carte-bloc mb-4">
  <form class="row g-3 align-items-end" method="get" action="index.php" role="search">
    <div class="col-12 col-lg-4 position-relative">
      <label class="form-label" for="recherche-live">Recherche rapide</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input class="form-control" id="recherche-live" name="q" autocomplete="off"
               placeholder="Nom, prénom ou matricule…" value="<?= h($q) ?>">
      </div>
      <div class="list-group position-absolute w-100 shadow-sm d-none" id="resultats-live"
           style="z-index:20; max-height:240px; overflow:auto" role="listbox"
           aria-label="Suggestions de recherche"></div>
    </div>

    <div class="col-6 col-lg-2">
      <label class="form-label" for="filtre-mention">Mention</label>
      <select class="form-select" id="filtre-mention" name="mention" data-filtre-auto>
        <option value="">Toutes</option>
        <?php foreach ($listeMentions as $valeur): ?>
          <option value="<?= h($valeur) ?>" <?= $mention === $valeur ? 'selected' : '' ?>><?= h($valeur) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-6 col-lg-2">
      <label class="form-label" for="filtre-cycle">Cycle</label>
      <select class="form-select" id="filtre-cycle" name="cycle" data-filtre-auto>
        <option value="">Tous</option>
        <?php foreach ($listeCycles as $valeur): ?>
          <option value="<?= h($valeur) ?>" <?= $cycle === $valeur ? 'selected' : '' ?>><?= h($valeur) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-6 col-lg-2">
      <label class="form-label" for="filtre-depart">Département</label>
      <select class="form-select" id="filtre-depart" name="depart" data-filtre-auto>
        <option value="">Tous</option>
        <?php foreach ($listeDeparts as $valeur): ?>
          <option value="<?= h($valeur) ?>" <?= $depart === $valeur ? 'selected' : '' ?>><?= h($valeur) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-6 col-lg-2 d-flex gap-2">
      <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel"></i> Filtrer</button>
      <a class="btn btn-outline-secondary" href="index.php" title="Réinitialiser les filtres"
         aria-label="Réinitialiser les filtres"><i class="bi bi-arrow-counterclockwise"></i></a>
    </div>
    <input type="hidden" name="tri" value="<?= h($tri) ?>">
    <input type="hidden" name="ordre" value="<?= h(strtolower($ordre)) ?>">
  </form>
</div>

<?php /* ----------------------------- Tableau ------------------------------- */ ?>
<form method="post" action="cartes_batch.php" id="formulaire-lot" data-chargement>
  <?= csrf_field() ?>
  <div class="carte-bloc">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <span class="text-secondary small">
        <?= $total ?> résultat(s) — page <?= $page ?> sur <?= $nbPages ?>
      </span>
      <button class="btn btn-outline-primary btn-sm" type="submit" id="btn-lot" disabled>
        <i class="bi bi-collection"></i> Générer les cartes sélectionnées (0)
      </button>
    </div>

    <div class="table-responsive">
      <table class="table table-pro align-middle">
        <caption class="visually-hidden">Liste des étudiants</caption>
        <thead>
          <tr>
            <th scope="col" style="width:38px">
              <input class="form-check-input" type="checkbox" id="tout-cocher"
                     aria-label="Tout sélectionner">
            </th>
            <th scope="col"><?= lien_tri('nomEtu', 'Étudiant', $tri, $ordre) ?></th>
            <th scope="col"><?= lien_tri('idEtu', 'Matricule', $tri, $ordre) ?></th>
            <th scope="col"><?= lien_tri('emailEtu', 'Email', $tri, $ordre) ?></th>
            <th scope="col"><?= lien_tri('mentionEtu', 'Mention', $tri, $ordre) ?></th>
            <th scope="col"><?= lien_tri('cycleEtu', 'Cycle', $tri, $ordre) ?></th>
            <th scope="col"><?= lien_tri('departEtu', 'Département', $tri, $ordre) ?></th>
            <th scope="col" class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($etudiants as $e): ?>
          <tr>
            <td>
              <input class="form-check-input case-etu" type="checkbox" name="ids[]"
                     value="<?= h($e['idEtu']) ?>" aria-label="Sélectionner <?= h($e['nomEtu']) ?>">
            </td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <?= avatar($e) ?>
                <span>
                  <strong class="d-block lh-1"><?= h($e['nomEtu']) ?></strong>
                  <span class="text-secondary small"><?= h($e['prenomEtu']) ?></span>
                </span>
              </div>
            </td>
            <td><code><?= h($e['idEtu']) ?></code></td>
            <td class="small"><?= h($e['emailEtu']) ?></td>
            <td><?= badge_mention($e['mentionEtu']) ?></td>
            <td><?= badge_cycle($e['cycleEtu']) ?></td>
            <td class="small"><?= h($e['departEtu']) ?><br>
              <span class="text-secondary"><?= h($e['regEtu']) ?></span></td>
            <td class="text-end text-nowrap">
              <a class="btn btn-sm btn-outline-dark" href="carte.php?id=<?= urlencode((string)$e['idEtu']) ?>"
                 title="Aperçu et téléchargement de la carte">
                <i class="bi bi-person-vcard"></i> Carte</a>
              <a class="btn btn-sm btn-outline-primary"
                 href="form.php?id=<?= urlencode((string)$e['idEtu']) ?>" title="Modifier">
                <i class="bi bi-pencil"></i></a>
              <button class="btn btn-sm btn-outline-danger" type="submit" form="formulaire-suppression"
                      name="idEtu" value="<?= h($e['idEtu']) ?>"
                      onclick="return confirm('Supprimer définitivement cet étudiant ?');"
                      title="Supprimer"><i class="bi bi-trash"></i></button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$etudiants): ?>
          <tr><td colspan="8" class="text-center text-secondary py-4">
            <i class="bi bi-inbox fs-4 d-block mb-2"></i>Aucun étudiant ne correspond à votre recherche.
          </td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
      <p class="text-secondary small mb-0">Tri actuel : <?= h($tri) ?> (<?= h(strtolower($ordre)) ?>)</p>
      <?= pagination_html($page, $nbPages, $total) ?>
    </div>
  </div>
</form>

<?php /* Formulaire technique de suppression : un POST par ligne, jamais un GET */ ?>
<form method="post" action="delete.php" id="formulaire-suppression" class="d-none">
  <?= csrf_field() ?>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
