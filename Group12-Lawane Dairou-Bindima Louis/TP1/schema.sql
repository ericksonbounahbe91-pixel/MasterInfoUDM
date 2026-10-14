-- TP5 CRUD étudiants : création de la base et de la table (MySQL / MariaDB, XAMPP)
CREATE DATABASE IF NOT EXISTS universite
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE universite;

CREATE TABLE IF NOT EXISTS etudiant (
  idEtu        VARCHAR(20)  NOT NULL,
  nomEtu       VARCHAR(100) NOT NULL,
  prenomEtu    VARCHAR(100) NOT NULL,
  dateNaiss    DATE         NOT NULL,
  emailEtu     VARCHAR(150) NOT NULL,
  numTelEtu    VARCHAR(20)  NOT NULL,
  regEtu       VARCHAR(100) NOT NULL,
  departEtu    VARCHAR(100) NOT NULL,
  arrondisEtu  VARCHAR(100) NOT NULL,
  mentionEtu   VARCHAR(100) NOT NULL,
  cycleEtu     VARCHAR(50)  NOT NULL,
  ParcoursEtu  VARCHAR(100) NOT NULL,
  PRIMARY KEY (idEtu),
  UNIQUE KEY uq_email (emailEtu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO etudiant VALUES
('ETU001','Ndiaye','Awa','2002-05-14','awa.ndiaye@univ.sn','770000001','Dakar','Dakar','Plateau','Informatique','Licence','Génie logiciel'),
('ETU002','Diop','Moussa','2001-11-02','moussa.diop@univ.sn','770000002','Thiès','Thiès','Thiès Nord','Mathématiques','Master','Statistiques');
