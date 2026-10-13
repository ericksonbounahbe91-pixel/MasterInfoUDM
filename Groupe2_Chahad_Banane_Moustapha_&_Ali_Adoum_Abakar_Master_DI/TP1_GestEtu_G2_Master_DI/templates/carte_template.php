<?php
/**
 * =============================================================================
 *  templates/carte_template.php — Gabarit de la carte étudiante (CR80)
 * =============================================================================
 *  Contraintes techniques :
 *    - AUCUN framework CSS : le gabarit doit rester rendu identique par Dompdf ;
 *    - toutes les dimensions sont exprimées en MILLIMÈTRES (mm) ;
 *    - format CR80 : 85,6 × 54 mm, avec 3 mm de marge de sécurité intérieure ;
 *    - la mise en page repose sur un conteneur à dimensions fixes et sur des
 *      blocs positionnés en absolu : c'est la seule structure fidèlement
 *      reproduite par les trois moteurs de rendu (navigateur, wkhtmltoimage,
 *      Dompdf) et elle garantit une carte par page ;
 *    - la feuille de style assets/css/carte.css est INLINÉE ici ;
 *    - aucune ressource distante autre que le QR code (isRemoteEnabled = true).
 *
 *  Variables fournies par html_carte() (functions.php) :
 *    $etu, $qr_src, $logo_src, $photo_src, $initiales, $couleur, $couleur_claire,
 *    $date_validite, $avec_verso, $filigrane, $etablissement, $annee,
 *    $code_barres, $contexte_pdf
 * =============================================================================
 */

/* --- Feuille de style embarquée (source unique pour les trois moteurs) ----- */
$css_carte = __DIR__ . '/../assets/css/carte.css';
?>
<style>
<?php
if (is_file($css_carte)) {
    echo "\n" . (string)file_get_contents($css_carte) . "\n";
}
if (!empty($contexte_pdf)) {
    /* Dompdf encapsule le fragment dans son propre document : on annule les
       marges du corps et de la page pour que la carte occupe EXACTEMENT la
       page CR80 (85,6 × 54 mm = 242,65 × 153,07 pt). Sans cela, la marge
       par défaut réduit la zone imprimable et la carte est découpée. */
    echo "\n@page { margin: 0mm; }\n"
       . "html, body { margin: 0; padding: 0; background: #ffffff; }\n"
       . ".carte-cr80 { margin: 0; }\n";
}
?>
</style>

<?php /* =========================== RECTO ==================================== */ ?>
<div class="carte-cr80<?= !empty($contexte_pdf) ? ' contexte-pdf' : '' ?>">
  <div class="carte-recto">

    <?php /* Filigrane discret, puis bandeau supérieur ----------------------- */ ?>
    <div class="filigrane-discret"><?= htmlspecialchars($etablissement, ENT_QUOTES, 'UTF-8') ?></div>

    <div class="bandeau" style="background: <?= htmlspecialchars($couleur, ENT_QUOTES, 'UTF-8') ?>">
      <?php if ($logo_src !== ''): ?>
        <img class="logo" src="<?= htmlspecialchars($logo_src, ENT_QUOTES, 'UTF-8') ?>" alt="Logo">
      <?php else: ?>
        <div class="logo logo-secours"
             style="background: #ffffff; color: <?= htmlspecialchars($couleur, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(mb_substr($etablissement, 0, 1), ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
      <div class="etablissement">
        <strong><?= htmlspecialchars($etablissement, ENT_QUOTES, 'UTF-8') ?></strong>
        <span style="color: <?= htmlspecialchars($couleur_claire, ENT_QUOTES, 'UTF-8') ?>">CARTE D’ÉTUDIANT — <?= htmlspecialchars($annee, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <div class="qr">
        <img src="<?= htmlspecialchars($qr_src, ENT_QUOTES, 'UTF-8') ?>" alt="QR code de vérification">
      </div>
    </div>

    <?php /* Corps : photo (ou initiales) + état civil ---------------------- */ ?>
    <div class="corps">
      <div class="colonne-photo">
        <?php if ($photo_src !== ''): ?>
          <img class="photo" src="<?= htmlspecialchars($photo_src, ENT_QUOTES, 'UTF-8') ?>" alt="Photo de l’étudiant">
        <?php else: ?>
          <div class="photo photo-initiales"
               style="background: <?= htmlspecialchars($couleur, ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($initiales, ENT_QUOTES, 'UTF-8') ?>
          </div>
        <?php endif; ?>
        <div class="matricule"><?= htmlspecialchars((string)$etu['idEtu'], ENT_QUOTES, 'UTF-8') ?></div>
      </div>

      <div class="colonne-infos">
        <div class="nom">
          <?= htmlspecialchars((string)$etu['nomEtu'], ENT_QUOTES, 'UTF-8') ?>
          <?= htmlspecialchars((string)$etu['prenomEtu'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <table class="infos">
          <tr>
            <th>Mention</th>
            <td><?= htmlspecialchars((string)$etu['mentionEtu'], ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
          <tr>
            <th>Cycle</th>
            <td><?= htmlspecialchars((string)$etu['cycleEtu'], ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
          <tr>
            <th>Parcours</th>
            <td><?= htmlspecialchars((string)$etu['ParcoursEtu'], ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
          <tr>
            <th>Département</th>
            <td><?= htmlspecialchars((string)$etu['departEtu'], ENT_QUOTES, 'UTF-8') ?>
                <span class="doux">(<?= htmlspecialchars((string)$etu['regEtu'], ENT_QUOTES, 'UTF-8') ?>)</span></td>
          </tr>
          <tr>
            <th>Naissance</th>
            <td><?= htmlspecialchars(
                    (string)($etu['dateNaiss'] ? date('d/m/Y', strtotime((string)$etu['dateNaiss'])) : '—'),
                    ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
        </table>
      </div>
    </div>

    <?php /* Pied de carte ---------------------------------------------------- */ ?>
    <div class="pied">
      <div class="validite">
        <span class="etiquette">Valable jusqu’au</span>
        <strong><?= htmlspecialchars($date_validite, ENT_QUOTES, 'UTF-8') ?></strong>
      </div>
      <div class="zone-code-barres"><?= $code_barres /* HTML déjà construit et échappé */ ?></div>
      <div class="mention-legale">
        Carte strictement personnelle.<br>Vérification : <?= htmlspecialchars('verifier.php', ENT_QUOTES, 'UTF-8') ?>
      </div>
    </div>

    <?php /* Filigrane « COPIE » : uniquement en génération par lot ---------- */ ?>
    <?php if ($filigrane): ?>
      <div class="filigrane">COPIE</div>
    <?php endif; ?>
  </div>
</div>

<?php /* =========================== VERSO ==================================== */ ?>
<?php if ($avec_verso): ?>
<div class="carte-cr80<?= !empty($contexte_pdf) ? ' contexte-pdf' : '' ?>">
  <table class="verso">
    <tr class="v-titre">
      <td class="v-titre" colspan="2">CONDITIONS D’UTILISATION</td>
    </tr>
    <tr class="v-cond">
      <td class="v-cond" colspan="2">
        <table class="conditions">
          <tr><td class="puce">•</td><td>Cette carte est strictement personnelle et incessible.</td></tr>
          <tr><td class="puce">•</td><td>Elle doit être présentée à toute réquisition des autorités académiques.</td></tr>
          <tr><td class="puce">•</td><td>En cas de perte, une déclaration doit être faite au service de la scolarité.</td></tr>
          <tr><td class="puce">•</td><td>La validité de la carte peut être vérifiée en ligne au moyen du QR code du recto.</td></tr>
        </table>
      </td>
    </tr>
    <tr class="v-sign">
      <td class="v-sign-g">
        <div class="ligne-signature"></div>
        <div class="etiquette">Le Directeur des études</div>
      </td>
      <td class="v-sign-d">
        <div class="cachet">CACHET<br>OFFICIEL</div>
        <div class="etiquette">Cachet de l’établissement</div>
      </td>
    </tr>
    <tr class="v-pied">
      <td class="v-pied" colspan="2">
        <table class="pied-verso">
          <tr>
            <td class="cell-cb"><?= $code_barres ?></td>
            <td class="cell-date">Document généré le <?= htmlspecialchars(date('d/m/Y à H:i'), ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>
<?php endif; ?>
