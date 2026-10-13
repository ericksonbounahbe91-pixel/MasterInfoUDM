<?php
/**
 * =============================================================================
 *  cartes_batch.php — Génération des cartes en lot (POST + CSRF)
 * =============================================================================
 *  Reçoit les matricules cochés dans index.php, puis produit :
 *    - un PDF multipages (une carte par page, format CR80) ;
 *    - un ZIP contenant un PDF individuel par étudiant.
 *  Chaque carte de lot porte un filigrane « COPIE » et chaque génération est
 *  journalisée dans la table carte_etudiant.
 * =============================================================================
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/* --- Méthode HTTP et CSRF -------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}
verifier_csrf();

/* --- Sélection des étudiants ---------------------------------------------- */
$identifiants = array_values(array_filter(
    array_map('trim', (array)($_POST['ids'] ?? [])),
    static fn(string $v): bool => $v !== ''
));

if (!$identifiants) {
    flash('warning', 'Aucun étudiant n’a été sélectionné : cochez au moins une ligne.');
    rediriger('index.php');
}

if (count($identifiants) > 200) {
    flash('warning', 'La génération est limitée à 200 cartes par opération.');
    $identifiants = array_slice($identifiants, 0, 200);
}

/* --- Chargement des fiches (placeholders générés dynamiquement) ------------ */
$marqueurs = implode(', ', array_fill(0, count($identifiants), '?'));
$requete   = $pdo->prepare('SELECT * FROM etudiant WHERE idEtu IN (' . $marqueurs . ') ORDER BY nomEtu');
$requete->execute($identifiants);
$etudiants = $requete->fetchAll();

if (!$etudiants) {
    flash('danger', 'Aucun des étudiants sélectionnés n’a été trouvé en base.');
    rediriger('index.php');
}

$avecVerso = !empty($_POST['avec_verso']);

/* --- Production des fichiers ---------------------------------------------- */
try {
    if (!class_exists('\Dompdf\Dompdf')) {
        throw new RuntimeException('Dompdf est absent : exécutez « composer install ».');
    }
    preparer_dossier_cartes();

    /* 1. PDF multipages : chaque carte est placée dans un bloc « page ».
       La pagination est obtenue par CSS (page-break-after) et non en
       concaténant des fichiers PDF, ce qui produirait un flux invalide. */
    $blocs = [];
    foreach ($etudiants as $etu) {
        $blocs[] = '<div class="bloc-page">' . html_carte($etu, $avecVerso, true, true) . '</div>';
    }

    $dompdf = new \Dompdf\Dompdf([
        'isRemoteEnabled'      => true,
        'isHtml5ParserEnabled' => true,
        'chroot'               => __DIR__,
        'defaultFont'          => 'DejaVu Sans',
        'dpi'                  => 300,
    ]);
    $dompdf->setPaper(dimensions_cr80(), 'portrait', [0.0, 0.0, 0.0, 0.0]);
    $dompdf->loadHtml('<style>@page { margin: 0; }</style>' . implode('', $blocs), 'UTF-8');
    $dompdf->render();
    $fluxGlobal = (string)$dompdf->output();

    $horodatage = date('Ymd-His');
    $nomGlobal  = 'cartes_lot_' . count($etudiants) . '_' . $horodatage . '.pdf';
    file_put_contents(DOSSIER_CARTES . '/' . $nomGlobal, $fluxGlobal);

    /* 2. Journalisation + archives individuelles dans un ZIP. */
    $zip = new ZipArchive();
    $nomZip = 'cartes_lot_' . count($etudiants) . '_' . $horodatage . '.zip';
    $cheminZip = DOSSIER_CARTES . '/' . $nomZip;
    if ($zip->open($cheminZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        foreach ($etudiants as $etu) {
            $resultat = enregistrer_carte($pdo, $etu, pdf_carte($etu, $avecVerso, true), 'pdf');
            $zip->addFile(DOSSIER_CARTES . '/' . $resultat['chemin'], $resultat['chemin']);
        }
        $zip->addFromString('liste.txt', implode("\n", array_map(
            static fn(array $e): string => $e['idEtu'] . ' — ' . $e['nomEtu'] . ' ' . $e['prenomEtu'],
            $etudiants
        )));
        $zip->close();
    }

    /* 3. Envoi du ZIP au navigateur. */
    $contenu = (string)file_get_contents($cheminZip);
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $nomZip . '"');
    header('Content-Length: ' . strlen($contenu));
    echo $contenu;
    exit;

} catch (Throwable $e) {
    error_log('[TP5] cartes_batch.php : ' . $e->getMessage());
    flash('danger', 'Génération en lot impossible : ' . $e->getMessage());
    rediriger('index.php');
}
