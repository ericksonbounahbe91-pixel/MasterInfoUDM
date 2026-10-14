<?php
/**
 * TP5 – Connexion PDO à MySQL (XAMPP : utilisateur root, mot de passe vide).
 * PDO + requêtes préparées = protection contre les injections SQL.
 * Placer le dossier tp5 dans C:\xampp\htdocs\ puis ouvrir http://localhost/tp5/
 * La base « universiteMbdTest » est celle créée par lecon2/tp4/schema.sql.
 */
declare(strict_types=1);
session_start();   // nécessaire pour le jeton CSRF et les messages flash

$dsn = 'mysql:host=localhost;dbname=universite;charset=utf8mb4';
try {
    $pdo = new PDO($dsn, 'root', '');
    // See the "errors" folder for details...
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log($e->getMessage());                             // détail dans les logs uniquement
    exit('Connexion à la base impossible.');
}

/** Échappement HTML pour tout affichage (protection contre le XSS). */
function h(?string $valeur): string {
    return htmlspecialchars($valeur ?? '', ENT_QUOTES, 'UTF-8');
}

/** Liste des champs du formulaire étudiant (identique à la Leçon 2). */
const CHAMPS = ['idEtu','nomEtu','prenomEtu','dateNaiss','emailEtu','numTelEtu',
                'regEtu','departEtu','arrondisEtu','mentionEtu','cycleEtu','ParcoursEtu'];


/** Libellés affichés pour chaque champ. */
const LIBELLES = ['idEtu'=>'Matricule','nomEtu'=>'Nom','prenomEtu'=>'Prénom','dateNaiss'=>'Date de naissance',
  'emailEtu'=>'Email','numTelEtu'=>'Téléphone','regEtu'=>'Région','departEtu'=>'Département',
  'arrondisEtu'=>'Arrondissement','mentionEtu'=>'Mention','cycleEtu'=>'Cycle','ParcoursEtu'=>'Parcours'];

/** Listes de valeurs autorisées pour les champs à choix (menus déroulants + validation serveur). */
const REGIONS  = ['Dakar','Diourbel','Fatick','Kaffrine','Kaolack','Kédougou','Kolda',
                  'Louga','Matam','Saint-Louis','Sédhiou','Tambacounda','Thiès','Ziguinchor'];
const MENTIONS = ['Informatique','Mathématiques','Physique','Chimie','Biologie',
                  'Droit','Économie','Gestion','Lettres'];
const CYCLES   = ['Licence','Master','Doctorat'];

/** Protection CSRF : un jeton unique par session, à inclure dans chaque formulaire POST. */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}
function csrf_verifier(): void {
    $t = $_POST['csrf'] ?? '';
    if (!is_string($t) || !hash_equals(csrf_token(), $t)) { http_response_code(403); exit('Jeton CSRF invalide.'); }
}

/** Messages flash (affichés une seule fois après une redirection). */
function flash(string $type, string $msg): void { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }
function flash_lire(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

/** Lecture sûre d'un paramètre GET texte (ignore les tableaux). */
function get_str(string $cle): string { $v = $_GET[$cle] ?? ''; return is_string($v) ? trim($v) : ''; }
