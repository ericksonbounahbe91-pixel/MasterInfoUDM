<?php
require_once __DIR__ . '/../config/config.php';

$QUERY_LOG = []; // journal des requêtes (utilisé par l'analyse de performance)

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            exit('<p style="font-family:sans-serif;padding:2rem">Connexion à la base impossible. '
               . 'Vérifiez que sql/schema.sql et sql/seed.sql sont importés et que config/config.php est correct.</p>');
        }
    }
    return $pdo;
}

/** SELECT chronométré : retourne toutes les lignes. */
function q(string $sql, array $params = []): array {
    global $QUERY_LOG;
    $t = microtime(true);
    $st = db()->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll();
    $QUERY_LOG[] = ['sql' => $sql, 'ms' => (microtime(true) - $t) * 1000, 'rows' => count($rows)];
    return $rows;
}

/** SELECT chronométré : retourne la première ligne ou null. */
function q1(string $sql, array $params = []): ?array {
    $r = q($sql, $params);
    return $r[0] ?? null;
}

/** INSERT / UPDATE / DELETE : retourne le nombre de lignes affectées. */
function run(string $sql, array $params = []): int {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->rowCount();
}
