<?php
/**
 * =============================================================================
 *  functions.php — Bibliothèque de fonctions utilitaires (PHP procédural)
 * =============================================================================
 *  Regroupe, par thème, tous les services transverses de l'application :
 *    A. Libellés et référentiels (mentions, cycles)
 *    B. Session : messages flash
 *    C. Sécurité : jeton CSRF, signature HMAC des QR codes
 *    D. Affichage : avatars, badges, tri, pagination, liens conservant les filtres
 *    E. Cartes : QR code, logo, génération de fichiers, code-barres
 * =============================================================================
 */
declare(strict_types=1);

/* =============================================================================
 * A. Libellés et référentiels
 * ========================================================================== */

/** Libellés lisibles des champs du formulaire. */
const LIBELLES = [
    'idEtu'       => 'Matricule',
    'nomEtu'      => 'Nom',
    'prenomEtu'   => 'Prénom',
    'dateNaiss'   => 'Date de naissance',
    'emailEtu'    => 'Email',
    'numTelEtu'   => 'Téléphone',
    'regEtu'      => 'Région',
    'departEtu'   => 'Département',
    'arrondisEtu' => 'Arrondissement',
    'mentionEtu'  => 'Mention',
    'cycleEtu'    => 'Cycle',
    'ParcoursEtu' => 'Parcours',
];

/** Type de champ HTML attendu pour chaque colonne. */
const TYPES = [
    'dateNaiss' => 'date',
    'emailEtu'  => 'email',
    'numTelEtu' => 'tel',
];

/** Cycles proposés dans le formulaire (le champ reste libre si autre valeur). */
const CYCLES = ['Licence', 'Master', 'Doctorat'];

/** Mentions proposées par défaut (la liste réelle est lue en base). */
const MENTIONS = ['DI', 'IRS', 'Droit', 'Économie', 'Lettres', 'Sciences'];

/** Colonnes autorisées pour le tri (LISTE BLANCHE : jamais d'entrée utilisateur). */
const TRI_COLONNES = ['idEtu','nomEtu','prenomEtu','dateNaiss','emailEtu','numTelEtu',
                      'regEtu','departEtu','arrondisEtu','mentionEtu','cycleEtu','ParcoursEtu'];

/** Longueurs maximales admises pour la validation serveur. */
const LONGUEURS_MAX = [
    'idEtu' => 20, 'nomEtu' => 60, 'prenomEtu' => 60, 'emailEtu' => 150,
    'numTelEtu' => 20, 'regEtu' => 60, 'departEtu' => 60, 'arrondisEtu' => 60,
    'mentionEtu' => 30, 'cycleEtu' => 20, 'ParcoursEtu' => 60,
];

/** Expression régulière d'un numéro de téléphone (8 à 15 chiffres, « + » facultatif). */
const REGEX_TEL = '/^\+?[0-9]{8,15}$/';

/* =============================================================================
 * B. Session : messages flash
 * ========================================================================== */

/**
 * Enregistre un message à afficher sur la prochaine page (toast Bootstrap).
 * @param string $type    success | danger | warning | info
 * @param string $message Texte affiché à l'utilisateur
 */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Récupère puis vide la file des messages flash. */
function lire_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/** Mémorise la saisie pour la restituer en cas d'erreur de validation. */
function memoriser_saisie(array $donnees): void
{
    $_SESSION['ancien'] = $donnees;
}

/** Restitue puis oublie la saisie précédemment mémorisée. */
function saisie_precedente(): array
{
    $ancien = $_SESSION['ancien'] ?? [];
    unset($_SESSION['ancien']);
    return $ancien;
}

/* =============================================================================
 * C. Sécurité : CSRF et signature HMAC
 * ========================================================================== */

/** Retourne le jeton CSRF de la session (créé à la demande). */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Champ caché à insérer dans chaque formulaire POST. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

/**
 * Vérifie le jeton CSRF d'une requête POST.
 * Toute écriture (save.php, delete.php, cartes_batch.php) passe par ici AVANT
 * de toucher à la base de données.
 */
function verifier_csrf(): void
{
    $recu = (string)($_POST['csrf'] ?? '');
    if ($recu === '' || !hash_equals(csrf_token(), $recu)) {
        http_response_code(419);
        flash('danger', 'Session expirée ou jeton de sécurité invalide. Veuillez recommencer.');
        rediriger('index.php');
    }
}

/** Interrompt le script et renvoie l'utilisateur vers une page. */
function rediriger(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/** Signature HMAC-SHA256 d'un matricule : c'est elle qui protège le QR code. */
function token_carte(string $idEtu): string
{
    return hash_hmac('sha256', $idEtu, CLE_SECRETE);
}

/** Vérifie une signature en temps constant (hash_equals : pas de timing attack). */
function token_valide(string $idEtu, string $token): bool
{
    return hash_equals(token_carte($idEtu), $token);
}

/** URL absolue de base de l'application (utilisée dans les QR codes). */
function url_base(): string
{
    $https  = (($_SERVER['HTTPS'] ?? '') === 'on') ? 'https' : 'http';
    $hote   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $script = rtrim($script, '/');
    return $https . '://' . $hote . $script . '/';
}

/* =============================================================================
 * D. Affichage : avatars, badges, tri, pagination
 * ========================================================================== */

/** Initiales (2 lettres) servant d'avatar lorsque la photo est absente. */
function initiales(string $nom, string $prenom): string
{
    $n = mb_substr(trim($nom), 0, 1);
    $p = mb_substr(trim($prenom), 0, 1);
    return mb_strtoupper($n . $p, 'UTF-8');
}

/** Palette stable : une même chaîne produit toujours la même couleur. */
function couleur_depuis_chaine(string $texte): string
{
    $palette = ['#1e3a8a','#0f766e','#7c3aed','#b45309','#be123c','#0369a1','#15803d','#4d7c0f'];
    return $palette[abs(crc32(mb_strtolower($texte, 'UTF-8'))) % count($palette)];
}

/**
 * Variante claire d'une couleur #rrggbb, utilisée pour les textes secondaires
 * du bandeau de la carte (lisible sur fond foncé).
 */
function couleur_claire(string $hex): string
{
    if (!preg_match('/^#?([0-9a-f]{6})$/i', trim($hex), $m)) {
        return '#ffffff';
    }
    $canal = static function (int $c): int {
        return max(0, min(255, (int)round($c + (255 - $c) * 0.62)));
    };
    $r = hexdec(substr($m[1], 0, 2));
    $v = hexdec(substr($m[1], 2, 2));
    $b = hexdec(substr($m[1], 4, 2));
    return sprintf('#%02x%02x%02x', $canal($r), $canal($v), $canal($b));
}

/** Badge coloré pour une mention (couleur dérivée du libellé). */
function badge_mention(?string $mention): string
{
    $mention = trim((string)$mention);
    if ($mention === '') { return '<span class="badge text-bg-secondary">—</span>'; }
    $couleur = couleur_depuis_chaine($mention);
    return '<span class="badge badge-mention" style="--badge:' . h($couleur) . '">'
         . h($mention) . '</span>';
}

/** Badge coloré pour un cycle (couleurs conventionnelles). */
function badge_cycle(?string $cycle): string
{
    $cycle = trim((string)$cycle);
    if ($cycle === '') { return '<span class="badge text-bg-secondary">—</span>'; }
    $couleurs = ['Licence' => '#0d6efd', 'Master' => '#6f42c1', 'Doctorat' => '#198754'];
    $couleur  = $couleurs[$cycle] ?? couleur_depuis_chaine($cycle);
    return '<span class="badge badge-cycle" style="--badge:' . h($couleur) . '">'
         . h($cycle) . '</span>';
}

/** Avatar (photo ou initiales). */
function avatar(array $etu, int $taille = 40): string
{
    $initiales = initiales((string)$etu['nomEtu'], (string)$etu['prenomEtu']);
    $style     = 'width:' . $taille . 'px;height:' . $taille . 'px;'
               . 'font-size:' . (int)round($taille / 2.4) . 'px;'
               . 'background:' . couleur_depuis_chaine((string)$etu['idEtu']) . ';';
    return '<span class="avatar" style="' . h($style) . '" aria-hidden="true">'
         . h($initiales) . '</span>';
}

/** Valide une colonne de tri contre la liste blanche. */
function colonne_triable(?string $colonne, string $defaut = 'nomEtu'): string
{
    return in_array($colonne, TRI_COLONNES, true) ? $colonne : $defaut;
}

/** Construit une URL en conservant les paramètres courants. */
function url_avec(array $modifications): string
{
    $params = array_merge($_GET, $modifications);
    $params = array_filter($params, static fn($v) => $v !== '' && $v !== null);
    return 'index.php' . ($params ? '?' . http_build_query($params) : '');
}

/** En-tête de colonne cliquable (tri serveur) avec indicateur de direction. */
function lien_tri(string $colonne, string $libelle, string $tri, string $ordre): string
{
    $nouvelOrdre = ($tri === $colonne && $ordre === 'ASC') ? 'desc' : 'asc';
    $icone = '';
    if ($tri === $colonne) {
        $icone = $ordre === 'ASC' ? ' <i class="bi bi-caret-up-fill"></i>'
                                  : ' <i class="bi bi-caret-down-fill"></i>';
    }
    return '<a class="lien-tri" href="' . h(url_avec(['tri' => $colonne, 'ordre' => $nouvelOrdre, 'page' => 1]))
         . '">' . h($libelle) . $icone . '</a>';
}

/** Pagination Bootstrap complète. */
function pagination_html(int $page, int $nbPages, int $total): string
{
    if ($nbPages <= 1) {
        return '<p class="text-secondary small mb-0">' . $total . ' étudiant(s) affiché(s).</p>';
    }
    $html  = '<nav aria-label="Pagination des étudiants"><ul class="pagination pagination-sm mb-0">';
    $html .= '<li class="page-item' . ($page <= 1 ? ' disabled' : '') . '">'
           . '<a class="page-link" href="' . h(url_avec(['page' => $page - 1])) . '" aria-label="Précédent">'
           . '<i class="bi bi-chevron-left"></i></a></li>';
    for ($i = 1; $i <= $nbPages; $i++) {
        $html .= '<li class="page-item' . ($i === $page ? ' active' : '') . '">'
               . '<a class="page-link" href="' . h(url_avec(['page' => $i])) . '">' . $i . '</a></li>';
    }
    $html .= '<li class="page-item' . ($page >= $nbPages ? ' disabled' : '') . '">'
           . '<a class="page-link" href="' . h(url_avec(['page' => $page + 1])) . '" aria-label="Suivant">'
           . '<i class="bi bi-chevron-right"></i></a></li></ul></nav>';
    return $html;
}

/* =============================================================================
 * E. Cartes étudiantes : QR code, logo, fichiers, code-barres
 * ========================================================================== */

/**
 * Produit une image de QR code pour un texte donné.
 *   1) Endroid\QrCode (Composer)  -> image PNG encodée en data: URI (hors ligne) ;
 *   2) Repli                      -> service api.qrserver.com (autorisé par la CSP).
 */
function qr_src(string $texte): string
{
    if (class_exists('\Endroid\QrCode\Writer\PngWriter')) {
        try {
            $classe = '\Endroid\QrCode\QrCode';
            $qr = method_exists($classe, 'create') ? $classe::create($texte) : new $classe($texte);
            if (method_exists($qr, 'setSize')) { $qr->setSize(300); }
            if (method_exists($qr, 'setMargin')) { $qr->setMargin(4); }
            $ecriture = new \Endroid\QrCode\Writer\PngWriter();
            return $ecriture->write($qr)->getDataUri();
        } catch (Throwable $e) {
            error_log('[TP5] QR Endroid : ' . $e->getMessage());
        }
    }
    return 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=8&data='
         . rawurlencode($texte);
}

/** Logo de l'établissement : fichier local, sinon repli sur un data: URI vide. */
function logo_src(): string
{
    $chemin = __DIR__ . '/assets/img/logo.png';
    if (is_file($chemin)) {
        return url_base() . 'assets/img/logo.png';
    }
    return '';
}

/** Photo de l'étudiant si la colonne existe et contient un fichier valide. */
function photo_src(array $etu): string
{
    $nom = trim((string)($etu['photo'] ?? ''));
    if ($nom === '') { return ''; }
    $chemin = __DIR__ . '/assets/img/photos/' . basename($nom);
    return is_file($chemin) ? url_base() . 'assets/img/photos/' . rawurlencode(basename($nom)) : '';
}

/** URL complète encodée dans le QR code (page publique de vérification). */
function url_verification(string $idEtu): string
{
    return url_base() . 'verifier.php?id=' . rawurlencode($idEtu)
         . '&token=' . token_carte($idEtu);
}

/** Date de fin de validité de la carte (31 août de l'année académique en cours). */
function date_validite(): string
{
    $anneeFin = (int)substr(ANNEE_ACADEMIQUE, 5, 4);
    return '31/08/' . $anneeFin;
}

/**
 * Code-barres purement visuel (aucune dépendance externe).
 * Les barres sont dérivées de la valeur ASCII des caractères du matricule.
 */
function code_barres_html(string $idEtu): string
{
    /* Les largeurs sont exprimées en MILLIMÈTRES : le code-barres garde ainsi
       exactement les mêmes proportions dans le navigateur (96 ppp) et dans le
       PDF (Dompdf, 300 ppp), où une largeur en px serait divisée par 3. */
    $unite = 0.16;                                  // mm par unité de barre
    $html  = '<div class="cb" aria-hidden="true">';
    $chaine = strtoupper($idEtu);
    for ($i = 0; $i < strlen($chaine); $i++) {
        $valeur = ord($chaine[$i]);
        for ($b = 0; $b < 4; $b++) {
            $largeur = (1 + (($valeur >> $b) & 1) * 2) * $unite;   // barre de 1 ou 3 unités
            $blanc   = ((($valeur >> $b) & 1) ? 1 : 2) * $unite;
            $html .= '<span style="width:' . number_format($largeur, 2, '.', '') . 'mm"></span>';
            $html .= '<span class="blanc" style="width:' . number_format($blanc, 2, '.', '') . 'mm"></span>';
        }
    }
    return $html . '</div><div class="cb-txt">' . h($idEtu) . '</div>';
}

/**
 * Rend le gabarit de carte et renvoie le HTML (avec ou sans verso).
 * @param array $etu     Ligne complète de la table etudiant
 * @param bool  $avecVerso Inclut le verso (conditions + signature)
 * @param bool  $filigrane Ajoute le filigrane « COPIE » (mode lot)
 * @param bool  $contextePdf Ajoute les règles réservées au PDF (marges nulles,
 *                           suppression de l'ombre) sans toucher à l'aperçu HTML
 */
function html_carte(array $etu, bool $avecVerso = false, bool $filigrane = false,
                   bool $contextePdf = false): string
{
    $donnees = [
        'etu'             => $etu,
        'qr_src'          => qr_src(url_verification((string)$etu['idEtu'])),
        'logo_src'        => logo_src(),
        'photo_src'       => photo_src($etu),
        'initiales'       => initiales((string)$etu['nomEtu'], (string)$etu['prenomEtu']),
        'couleur'         => couleur_depuis_chaine((string)$etu['idEtu']),
        'couleur_claire'  => couleur_claire(couleur_depuis_chaine((string)$etu['idEtu'])),
        'contexte_pdf'    => $contextePdf,
        'date_validite'   => date_validite(),
        'avec_verso'      => $avecVerso,
        'filigrane'       => $filigrane,
        'etablissement'   => ETABLISSEMENT,
        'annee'           => ANNEE_ACADEMIQUE,
        'code_barres'     => code_barres_html((string)$etu['idEtu']),
    ];
    extract($donnees, EXTR_SKIP);
    ob_start();
    require __DIR__ . '/templates/carte_template.php';
    return (string)ob_get_clean();
}

/* =============================================================================
 * F. Génération des fichiers (PDF via Dompdf, PNG via wkhtmltoimage / Imagick)
 * ========================================================================== */

/** S'assure que le dossier des cartes existe et reste protégé. */
function preparer_dossier_cartes(): void
{
    if (!is_dir(DOSSIER_CARTES)) {
        mkdir(DOSSIER_CARTES, 0775, true);
    }
    $protection = DOSSIER_CARTES . '/.htaccess';
    if (!is_file($protection)) {
        file_put_contents($protection, "Require all denied\n");
    }
}

/** Dimensions CR80 (85,6 × 54 mm) converties en points PostScript (1 mm = 2,8346 pt). */
function dimensions_cr80(): array
{
    return [0.0, 0.0, 85.6 * 2.834645669, 54.0 * 2.834645669];
}

/**
 * Génère le PDF d'une carte et renvoie son contenu binaire.
 * Dompdf est chargé depuis vendor/ (composer require dompdf/dompdf).
 */
function pdf_carte(array $etu, bool $avecVerso = false, bool $filigrane = false): string
{
    if (!class_exists('\Dompdf\Dompdf')) {
        throw new RuntimeException('Dompdf est absent : exécutez « composer install ».');
    }
    $dompdf = new \Dompdf\Dompdf([
        'isRemoteEnabled' => true,        // nécessaire pour le QR distant / le logo
        'isHtml5ParserEnabled' => true,
        'chroot'          => __DIR__,     // limite les lectures à la racine du projet
        'defaultFont'     => 'DejaVu Sans',
        'dpi'             => 300,         // qualité d'impression (300 ppp)
    ]);
    // dimensions_cr80() renvoie déjà [0, 0, largeur, hauteur] en points :
    // l'orientation doit donc rester « portrait », sinon Dompdf permute les
    // deux valeurs et produit une page CR80 pivotée de 90°.
    /* 3e argument = marges de page en points. Sans marges nulles, Dompdf
       applique son défaut (~1 cm) : la zone imprimable devient plus petite que
       la carte (54 mm) et le document est découpé en plusieurs pages. */
    $dompdf->setPaper(dimensions_cr80(), 'portrait', [0.0, 0.0, 0.0, 0.0]);
    $dompdf->loadHtml(html_carte($etu, $avecVerso, $filigrane, true), 'UTF-8');
    $dompdf->render();
    return (string)$dompdf->output();
}

/**
 * Génère un PNG haute résolution à partir du HTML de la carte.
 *   1) wkhtmltoimage (qualité d'impression, si présent dans le PATH) ;
 *   2) Imagick sur le PDF (nécessite Ghostscript) ;
 *   3) échec : le PDF reste disponible.
 */
function png_carte(array $etu, bool $avecVerso = false, bool $filigrane = false): string
{
    $html    = html_carte($etu, $avecVerso, $filigrane);
    $tmpHtml = tempnam(sys_get_temp_dir(), 'carte') . '.html';
    file_put_contents($tmpHtml, $html);

    // --- 1) wkhtmltoimage -------------------------------------------------
    $binaire = trim((string)@shell_exec('where wkhtmltoimage 2>NUL || which wkhtmltoimage 2>/dev/null'));
    if ($binaire !== '') {
        $tmpPng = $tmpHtml . '.png';
        $commande = escapeshellarg($binaire) . ' --enable-local-file-access --width 1011 '
                  . '--quality 100 ' . escapeshellarg($tmpHtml) . ' ' . escapeshellarg($tmpPng) . ' 2>&1';
        @shell_exec($commande);
        if (is_file($tmpPng) && filesize($tmpPng) > 0) {
            $contenu = (string)file_get_contents($tmpPng);
            @unlink($tmpPng); @unlink($tmpHtml);
            return $contenu;
        }
    }

    // --- 2) Imagick (PDF -> PNG, exige Ghostscript) -----------------------
    if (class_exists('\Imagick')) {
        $tmpPdf = $tmpHtml . '.pdf';
        file_put_contents($tmpPdf, pdf_carte($etu, $avecVerso, $filigrane));
        try {
            $image = new \Imagick($tmpPdf . '[0]');
            $image->setImageResolution(300, 300);
            $image->setImageFormat('png');
            $image->setImageBackgroundColor('white');
            $binaire = $image->getImageBlob();
            $image->clear();
            @unlink($tmpPdf); @unlink($tmpHtml);
            return $binaire;
        } catch (Throwable $e) {
            error_log('[TP5] Imagick : ' . $e->getMessage());
            @unlink($tmpPdf);
        }
    }

    @unlink($tmpHtml);
    throw new RuntimeException(
        'Export PNG indisponible : installez wkhtmltoimage (wkhtmltopdf) '
        . 'ou activez l’extension Imagick avec Ghostscript. Le PDF reste disponible.'
    );
}

/**
 * Enregistre une carte générée et journalise l'opération en base.
 * @return array{chemin:string,hash:string}
 */
function enregistrer_carte(PDO $pdo, array $etu, string $binaire, string $extension): array
{
    preparer_dossier_cartes();
    $hash   = token_carte((string)$etu['idEtu']);
    $fichier = 'carte_' . preg_replace('/[^A-Za-z0-9_-]/', '', (string)$etu['idEtu'])
             . '_' . date('Ymd-His') . '.' . $extension;
    $chemin = DOSSIER_CARTES . '/' . $fichier;
    file_put_contents($chemin, $binaire);

    $pdo->prepare(
        'INSERT INTO carte_etudiant (idEtu, date_generation, hash_qr, chemin_pdf)
         VALUES (:idEtu, NOW(), :hash, :chemin)'
    )->execute([
        ':idEtu'  => $etu['idEtu'],
        ':hash'   => $hash,
        ':chemin' => $fichier,
    ]);

    return ['chemin' => $fichier, 'hash' => $hash];
}
