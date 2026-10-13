<?php
/**
 * =============================================================================
 *  verifier.php — Page PUBLIQUE de vérification d'une carte étudiante
 * =============================================================================
 *  Appelée par le QR code : verifier.php?id=XXX&token=HASH
 *  Le jeton est un HMAC-SHA256 du matricule calculé avec la clé secrète de
 *  config.php : impossible à forger sans connaître cette clé.
 *  Aucune donnée sensible n'est exposée : ni téléphone, ni email.
 * =============================================================================
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$id    = trim((string)($_GET['id'] ?? ''));
$token = trim((string)($_GET['token'] ?? ''));

$valide  = false;
$etudiant = null;
$motif    = '';

if ($id === '' || $token === '') {
    $motif = 'Paramètres incomplets : le matricule et le jeton sont requis.';
} elseif (!token_valide($id, $token)) {
    $motif = 'La signature du QR code est invalide : la carte n’est pas authentique.';
} else {
    $requete = $pdo->prepare(
        'SELECT idEtu, nomEtu, prenomEtu, dateNaiss, mentionEtu, cycleEtu,
                ParcoursEtu, departEtu, regEtu
           FROM etudiant
          WHERE idEtu = :id'
    );
    $requete->execute([':id' => $id]);
    $etudiant = $requete->fetch();

    if ($etudiant) {
        $valide = true;
        /* Journalisation facultative de la vérification. */
        try {
            $pdo->prepare('UPDATE carte_etudiant SET verifie_le = NOW()
                            WHERE idEtu = :id AND verifie_le IS NULL')
                ->execute([':id' => $id]);
        } catch (PDOException $e) {
            /* La colonne verifie_le est optionnelle : on ignore silencieusement. */
        }
    } else {
        $motif = 'Le matricule est signé correctement mais ne correspond à aucun étudiant inscrit.';
    }
}

$titre_page  = 'Vérification d’une carte';
$page_active = 'verification';
require __DIR__ . '/includes/header.php';
?>

<div class="entete-page">
  <h1>Vérification d’une carte étudiante</h1>
  <p class="text-secondary mb-0">Contrôle d’authenticité du QR code — accès public.</p>
</div>

<div class="carte-bloc" style="max-width:760px">
  <?php if ($valide): ?>
    <div class="d-flex align-items-center gap-3 mb-4">
      <span class="stat-icone" style="background:#198754;width:52px;height:52px;font-size:1.5rem">
        <i class="bi bi-patch-check-fill"></i>
      </span>
      <div>
        <h2 class="h4 mb-0 text-success">Carte authentique</h2>
        <p class="text-secondary mb-0">La signature correspond à un étudiant inscrit.</p>
      </div>
    </div>

    <table class="table table-pro align-middle">
      <tbody>
        <tr><th scope="row" style="width:35%">Nom et prénom</th>
            <td><?= h($etudiant['nomEtu'] . ' ' . $etudiant['prenomEtu']) ?></td></tr>
        <tr><th scope="row">Matricule</th><td><code><?= h($etudiant['idEtu']) ?></code></td></tr>
        <tr><th scope="row">Date de naissance</th>
            <td><?= h($etudiant['dateNaiss'] ? date('d/m/Y', strtotime((string)$etudiant['dateNaiss'])) : '—') ?></td></tr>
        <tr><th scope="row">Mention</th><td><?= badge_mention($etudiant['mentionEtu']) ?></td></tr>
        <tr><th scope="row">Cycle</th><td><?= badge_cycle($etudiant['cycleEtu']) ?></td></tr>
        <tr><th scope="row">Parcours</th><td><?= h($etudiant['ParcoursEtu']) ?></td></tr>
        <tr><th scope="row">Département / Région</th>
            <td><?= h($etudiant['departEtu']) ?> — <?= h($etudiant['regEtu']) ?></td></tr>
        <tr><th scope="row">Année académique</th><td><?= h(ANNEE_ACADEMIQUE) ?></td></tr>
      </tbody>
    </table>

  <?php else: ?>
    <div class="d-flex align-items-center gap-3 mb-3">
      <span class="stat-icone" style="background:#be123c;width:52px;height:52px;font-size:1.5rem">
        <i class="bi bi-shield-exclamation"></i>
      </span>
      <div>
        <h2 class="h4 mb-0 text-danger">Carte non vérifiée</h2>
        <p class="text-secondary mb-0"><?= h($motif) ?></p>
      </div>
    </div>
    <p class="mb-0">
      Si vous pensez qu’il s’agit d’une erreur, rapprochez-vous du service de la scolarité de
      <?= h(ETABLISSEMENT) ?>.
    </p>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
