-- ============================================================
-- Base de données attendue par config.php :
--   host = localhost | dbname = universite | user = root | mdp = (vide)
-- À importer dans phpMyAdmin (onglet « Importer ») ou en ligne de commande :
--   mysql -u root < universite.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS universite
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE universite;

DROP TABLE IF EXISTS etudiant;

CREATE TABLE etudiant (
  idEtu        VARCHAR(30)  NOT NULL,           -- Matricule (clé primaire, non modifiable dans form.php)
  nomEtu       VARCHAR(100) NOT NULL,
  prenomEtu    VARCHAR(100) NOT NULL,
  dateNaiss    DATE         NOT NULL,
  emailEtu     VARCHAR(150) NOT NULL,
  numTelEtu    VARCHAR(30)  NOT NULL,
  regEtu       VARCHAR(100) NOT NULL,           -- Région
  departEtu    VARCHAR(100) NOT NULL,           -- Département
  arrondisEtu  VARCHAR(100) NOT NULL,           -- Arrondissement
  mentionEtu   VARCHAR(100) NOT NULL,
  cycleEtu     VARCHAR(50)  NOT NULL,
  ParcoursEtu  VARCHAR(100) NOT NULL,
  PRIMARY KEY (idEtu),
  UNIQUE KEY uq_etudiant_email (emailEtu)       -- save.php gère l'erreur 23000 « Matricule ou email déjà utilisé »
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données d'exemple (facultatif : supprimez ces lignes si vous voulez une table vide)
INSERT INTO etudiant
  (idEtu, nomEtu, prenomEtu, dateNaiss, emailEtu, numTelEtu, regEtu, departEtu, arrondisEtu, mentionEtu, cycleEtu, ParcoursEtu)
VALUES
  ('ETU001', 'Mahamat', 'Abdoulaye', '2002-03-14', 'abdoulaye.mahamat@example.com', '66000001', 'Logone Occidental', 'Dodjé', 'Moundou', 'Informatique', 'Licence', 'Développement Informatique'),
  ('ETU002', 'Ngarlem', 'Esther', '2001-11-02', 'esther.ngarlem@example.com', '66000002', 'Logone Oriental', 'Kôh-Est', 'Doba', 'Informatique', 'Master', 'Ingénierie des Réseaux et Systèmes');
