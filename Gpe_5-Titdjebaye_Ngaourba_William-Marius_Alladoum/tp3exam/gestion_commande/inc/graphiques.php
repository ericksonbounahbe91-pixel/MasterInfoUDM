<?php
/** Histogramme SVG du chiffre d'affaires par mois (aucune bibliothèque externe : fonctionne hors ligne). */
function graphique_mensuel(array $lignes): string
{
    if (!$lignes) {
        return '<p class="vide-texte">Aucune vente sur cette période. Élargissez les dates ou enregistrez des produits dans une commande.</p>';
    }
    $max = max(array_map('floatval', array_column($lignes, 'ca'))) ?: 1;
    $w = 680; $h = 240; $marge = 24; $base = $h - 34; $haut = $base - 34;
    $n = count($lignes);
    $pas = ($w - 2 * $marge) / $n;
    $lb  = min(64, $pas * 0.7);

    $svg = '<svg class="graph" viewBox="0 0 ' . $w . ' ' . $h . '" role="img" aria-label="Chiffre d\'affaires par mois">';
    $svg .= '<line x1="' . $marge . '" y1="' . $base . '" x2="' . ($w - $marge) . '" y2="' . $base . '" class="axe"/>';
    foreach ($lignes as $i => $l) {
        $bh = max(2, ((float) $l['ca'] / $max) * $haut);
        $x  = $marge + $i * $pas + ($pas - $lb) / 2;
        $y  = $base - $bh;
        $cx = $x + $lb / 2;
        $svg .= '<rect x="' . round($x, 1) . '" y="' . round($y, 1) . '" width="' . round($lb, 1) . '" height="' . round($bh, 1) . '" class="colonne"><title>'
              . h($l['mois'] . ' : ' . fcfa($l['ca']) . ' (' . $l['nb'] . ' commande(s))') . '</title></rect>';
        $svg .= '<text x="' . round($cx, 1) . '" y="' . round($y - 6, 1) . '" text-anchor="middle" class="valeur">' . h(number_format((float) $l['ca'] / 1000, 0, ',', ' ')) . ' k</text>';
        $svg .= '<text x="' . round($cx, 1) . '" y="' . ($base + 18) . '" text-anchor="middle" class="mois">' . h($l['mois']) . '</text>';
    }
    return $svg . '</svg>';
}

/** Liste à barres horizontales (CSS pur). */
function barres(array $lignes, string $cleLibelle, string $cleValeur): string
{
    if (!$lignes) {
        return '<p class="vide-texte">Aucune vente sur cette période.</p>';
    }
    $max = max(array_map('floatval', array_column($lignes, $cleValeur))) ?: 1;
    $html = '<ul class="barres">';
    foreach ($lignes as $l) {
        $pct = round((float) $l[$cleValeur] / $max * 100);
        $html .= '<li><span class="lib">' . h($l[$cleLibelle]) . '</span>'
              . '<span class="piste"><span class="rempli" style="width:' . $pct . '%"></span></span>'
              . '<span class="val">' . h(fcfa($l[$cleValeur])) . '</span></li>';
    }
    return $html . '</ul>';
}
