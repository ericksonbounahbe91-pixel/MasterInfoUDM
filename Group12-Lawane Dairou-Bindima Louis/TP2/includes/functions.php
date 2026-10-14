<?php
session_start();
$PAGE_START = microtime(true);
require_once __DIR__ . '/db.php';

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($v): string { return number_format((float)$v, 0, ',', ' ') . ' ' . CURRENCY; }
function redirect(string $url): never { header('Location: ' . $url); exit; }

function flash(string $type, string $msg): void { $_SESSION['flash'][] = [$type, $msg]; }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Requête invalide (jeton CSRF). Rechargez la page et réessayez.');
    }
}

/** Avatar : photo si elle existe, sinon initiales. */
function avatar(?string $photo, string $label): string {
    if ($photo && is_file(UPLOAD_DIR . basename($photo))) {
        return '<img class="avatar" src="' . h(UPLOAD_URL . basename($photo)) . '" alt="">';
    }
    $ini = mb_strtoupper(mb_substr($label, 0, 1) . mb_substr(strstr($label, ' ') ?: ' ?', 1, 1));
    return '<span class="avatar avatar-ini" aria-hidden="true">' . h($ini) . '</span>';
}

/** Vignette produit : photo ou pictogramme neutre. */
function thumb(?string $photo): string {
    if ($photo && is_file(UPLOAD_DIR . basename($photo))) {
        return '<img class="thumb" src="' . h(UPLOAD_URL . basename($photo)) . '" alt="">';
    }
    return '<span class="thumb thumb-none" aria-hidden="true">▢</span>';
}

/** Enregistre plusieurs produits dans une commande (transaction + contrôle de stock). */
function add_lines(int $idCmd, array $ids, array $qtes): array {
    $items = [];
    foreach ($ids as $i => $id) {
        $id = (int)$id; $qte = (int)($qtes[$i] ?? 0);
        if ($id > 0 && $qte > 0) $items[$id] = ($items[$id] ?? 0) + $qte; // fusionne les doublons
    }
    if (!$items) return [false, 'Ajoutez au moins un produit avec une quantité supérieure à 0.'];

    $pdo = db();
    $pdo->beginTransaction();
    try {
        foreach ($items as $id => $qte) {
            $st = $pdo->prepare('SELECT designProd, qteStock FROM Produit WHERE idProd = ? FOR UPDATE');
            $st->execute([$id]);
            $p = $st->fetch();
            if (!$p) throw new RuntimeException("Produit introuvable (n° $id).");
            if ($p['qteStock'] < $qte) {
                throw new RuntimeException("Stock insuffisant pour « {$p['designProd']} » : {$p['qteStock']} disponible(s), $qte demandé(s).");
            }
            $pdo->prepare('INSERT INTO ligneCommande (idCmd, idProd, qteCmd) VALUES (?, ?, ?)
                           ON DUPLICATE KEY UPDATE qteCmd = qteCmd + VALUES(qteCmd)')->execute([$idCmd, $id, $qte]);
            $pdo->prepare('UPDATE Produit SET qteStock = qteStock - ? WHERE idProd = ?')->execute([$qte, $id]);
        }
        $pdo->commit();
        return [true, count($items) . ' produit(s) enregistré(s) dans la commande.'];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return [false, $e->getMessage()];
    }
}

/** Supprime un produit d'une commande et remet la quantité en stock. */
function delete_line(int $idCmd, int $idProd): array {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('SELECT qteCmd FROM ligneCommande WHERE idCmd = ? AND idProd = ? FOR UPDATE');
        $st->execute([$idCmd, $idProd]);
        $l = $st->fetch();
        if (!$l) throw new RuntimeException('Cette ligne n\'existe plus.');
        $pdo->prepare('DELETE FROM ligneCommande WHERE idCmd = ? AND idProd = ?')->execute([$idCmd, $idProd]);
        $pdo->prepare('UPDATE Produit SET qteStock = qteStock + ? WHERE idProd = ?')->execute([$l['qteCmd'], $idProd]);
        $pdo->commit();
        return [true, 'Produit retiré de la commande ; ' . $l['qteCmd'] . ' unité(s) remise(s) en stock.'];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return [false, $e->getMessage()];
    }
}
