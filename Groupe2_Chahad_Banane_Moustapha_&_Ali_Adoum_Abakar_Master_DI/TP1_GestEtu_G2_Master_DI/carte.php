<?php
/**
 * =============================================================================
 *  carte.php — Aperçu et téléchargement de la carte d'un étudiant
 * =============================================================================
 *  Utilisation :
 *    carte.php?id=XXX                      -> aperçu HTML (recto)
 *    carte.php?id=XXX&verso=1              -> aperçu avec le verso
 *    carte.php?id=XXX&format=pdf           -> téléchargement PDF (CR80, 300 ppp)
 *    carte.php?id=XXX&format=png           -> téléchargement PNG haute résolution
 * =============================================================================
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

/* Chargement automatique de Composer (Dompdf, Endroid QR Code) s'il est installé. */
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

$id     = trim((string)($_GET['id'] ?? ''));
$format = strtolower((string)($_GET['format'] ?? 'html'));
$verso  = !empty($_GET['verso']);

if ($id === '') {
    flash('danger', 'Aucun matricule n’a été fourni.');
    rediriger('index.php');
}

/* --- Récupération de l'étudiant ------------------------------------------- */
$requete = $pdo->prepare('SELECT * FROM etudiant WHERE idEtu = :id');
$requete->execute([':id' => $id]);
$etu = $requete->fetch();

if (!$etu) {
    flash('danger', 'Étudiant introuvable : le matricule « ' . $id . ' » n’existe pas.');
    rediriger('index.php');
}

/* --- Export PDF ou PNG ---------------------------------------------------- */
if ($format === 'pdf' || $format === 'png') {
    try {
        $binaire   = $format === 'pdf'
            ? pdf_carte($etu, $verso, false)
            : png_carte($etu, $verso, false);
        $resultat  = enregistrer_carte($pdo, $etu, $binaire, $format);
        $typeMime  = $format === 'pdf' ? 'application/pdf' : 'image/png';
        $nom       = 'carte_' . preg_replace('/[^A-Za-z0-9_-]/', '', $id) . '.' . $format;

        header('Content-Type: ' . $typeMime);
        header('Content-Disposition: attachment; filename="' . $nom . '"');
        header('Content-Length: ' . strlen($binaire));
        header('X-Carte-Hash: ' . $resultat['hash']);
        echo $binaire;
        exit;

    } catch (Throwable $e) {
        error_log('[TP5] carte.php (' . $format . ') : ' . $e->getMessage());
        flash('warning', 'Génération ' . strtoupper($format) . ' impossible : ' . $e->getMessage()
                       . ' L’aperçu HTML reste consultable et imprimable.');
        rediriger('carte.php?id=' . urlencode($id));
    }
}

/* --- Aperçu HTML ----------------------------------------------------------- */
$titre_page  = 'Carte de ' . $etu['prenomEtu'] . ' ' . $etu['nomEtu'];
$page_active = 'etudiants';
require __DIR__ . '/includes/header.php';
?>

<div class="entete-page d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div>
    <h1>Carte étudiante</h1>
    <p class="text-secondary mb-0">
      <?= h($etu['prenomEtu'] . ' ' . $etu['nomEtu']) ?> — matricule <code><?= h($etu['idEtu']) ?></code>
    </p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-outline-secondary" href="index.php"><i class="bi bi-arrow-left"></i> Liste</a>
    <a class="btn btn-outline-primary" href="form.php?id=<?= urlencode($id) ?>">
      <i class="bi bi-pencil"></i> Modifier la fiche</a>
  </div>
</div>

<div class="carte-bloc mb-4">
  <div class="d-flex flex-wrap gap-2 mb-4">
    <a class="btn btn-primary" href="carte.php?id=<?= urlencode($id) ?>&amp;format=pdf">
      <i class="bi bi-file-earmark-pdf"></i> Télécharger le PDF</a>
    <a class="btn btn-success" href="carte.php?id=<?= urlencode($id) ?>&amp;format=png">
      <i class="bi bi-file-earmark-image"></i> Télécharger le PNG</a>
    <a class="btn btn-outline-dark" id="btn-imprimer" href="#">
      <i class="bi bi-printer"></i> Imprimer</a>
    <a class="btn btn-outline-secondary" href="carte.php?id=<?= urlencode($id) ?>&amp;verso=<?= $verso ? '0' : '1' ?>">
      <i class="bi bi-arrow-repeat"></i> <?= $verso ? 'Masquer' : 'Afficher' ?> le verso</a>
  </div>

  <div class="zone-apercu">
    <?= html_carte($etu, $verso, false) ?>
  </div>

  <p class="text-secondary small mt-4 mb-0">
    Format CR80 (85,6 × 54 mm), marge de sécurité de 3 mm. Le QR code encode une URL de
    vérification signée par HMAC-SHA256 : toute modification du matricule invalide la signature.
  </p>
</div>

<div class="carte-bloc">
  <h2 class="h5">Journal des générations</h2>
  <?php
    try {
        $historique = $pdo->prepare(
            'SELECT date_generation, chemin_pdf, hash_qr
               FROM carte_etudiant
              WHERE idEtu = :id
              ORDER BY date_generation DESC
              LIMIT 10'
        );
        $historique->execute([':id' => $id]);
        $lignes = $historique->fetchAll();
    } catch (PDOException $e) {
        $lignes = [];
        echo '<p class="text-secondary mb-0">Table <code>carte_etudiant</code> absente : '
           . 'exécutez <code>schema_migration.sql</code>.</p>';
    }
  ?>
  <?php if (!empty($lignes)): ?>
    <div class="table-responsive">
      <table class="table table-pro align-middle">
        <thead><tr><th>Date</th><th>Fichier</th><th>Empreinte QR</th></tr></thead>
        <tbody>
        <?php foreach ($lignes as $ligne): ?>
          <tr>
            <td><?= h($ligne['date_generation']) ?></td>
            <td><code><?= h($ligne['chemin_pdf']) ?></code></td>
            <td class="small text-secondary"><?= h(substr((string)$ligne['hash_qr'], 0, 24)) ?>…</td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
