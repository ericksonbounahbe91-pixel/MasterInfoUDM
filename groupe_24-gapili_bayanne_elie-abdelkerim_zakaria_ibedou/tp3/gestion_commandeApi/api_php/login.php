<?php
require 'config.php';
try {
    $d = body();
    // Compte fourni dans le sujet : compte de démonstration, à remplacer en production.
    if (($d['login'] ?? '') === '' && ($d['password'] ?? '') === 'MoundMasT2@2026') {
        ok(['login' => '', 'role' => 'administrateur']);
    }
    fail('Identifiant ou mot de passe incorrect.', 401);
} catch (Throwable $e) { fail('Erreur serveur lors de la connexion.', 500); }
