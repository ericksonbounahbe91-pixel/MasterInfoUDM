-- =============================================================================
--  schema_migration.sql — Module « cartes étudiantes » (TP5 refactorisé)
--  Base : universite (XAMPP / MariaDB)
--  À exécuter une seule fois, après avoir créé la table etudiant (leçon2/tp4).
--  Import : phpMyAdmin > base « universite » > onglet SQL > coller > Exécuter
--           ou : mysql -u root universite < schema_migration.sql
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 1. Table de journalisation des cartes générées
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS carte_etudiant (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identifiant interne',
    idEtu           VARCHAR(20)     NOT NULL                COMMENT 'Matricule de l''étudiant',
    date_generation DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                            COMMENT 'Horodatage de la génération',
    hash_qr         CHAR(64)        NOT NULL                COMMENT 'Signature HMAC-SHA256 du matricule',
    chemin_pdf      VARCHAR(255)    NOT NULL                COMMENT 'Nom du fichier dans /cartes',
    verifie_le      DATETIME        NULL                    COMMENT 'Dernière vérification du QR code',
    PRIMARY KEY (id),
    KEY idx_carte_etudiant (idEtu),
    KEY idx_carte_hash (hash_qr),
    CONSTRAINT fk_carte_etudiant
        FOREIGN KEY (idEtu) REFERENCES etudiant (idEtu)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Cartes étudiantes générées (PDF/PNG) et empreintes de vérification';

-- -----------------------------------------------------------------------------
-- 2. Colonne photo (facultative) : utilisée par la carte si elle est renseignée
-- -----------------------------------------------------------------------------
-- Décommenter si la colonne n'existe pas encore dans la table etudiant :
-- ALTER TABLE etudiant
--     ADD COLUMN photo VARCHAR(255) NULL COMMENT 'Nom du fichier dans assets/img/photos';

-- -----------------------------------------------------------------------------
-- 3. Index de performance sur les colonnes filtrées et triées
-- -----------------------------------------------------------------------------
-- Décommenter selon les besoins (l'index sur une colonne déjà indexée échoue) :
-- ALTER TABLE etudiant ADD INDEX idx_etu_nom (nomEtu);
-- ALTER TABLE etudiant ADD INDEX idx_etu_mention (mentionEtu);
-- ALTER TABLE etudiant ADD INDEX idx_etu_cycle (cycleEtu);
-- ALTER TABLE etudiant ADD INDEX idx_etu_depart (departEtu);

-- -----------------------------------------------------------------------------
-- 4. Vérification
-- -----------------------------------------------------------------------------
-- SHOW CREATE TABLE carte_etudiant;
-- SELECT COUNT(*) AS cartes_generees FROM carte_etudiant;
