-- =====================================================================
-- Gestion d'une ligne de commande — schéma MySQL / MariaDB
-- Import : mysql -u root -p < sql/schema.sql   puis   mysql -u root -p < sql/seed.sql
-- =====================================================================
DROP DATABASE IF EXISTS gestion_commande;
CREATE DATABASE gestion_commande CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_commande;

CREATE TABLE Departement (
  idDep   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomDep  VARCHAR(80)  NOT NULL,
  PRIMARY KEY (idDep)
) ENGINE=InnoDB;

CREATE TABLE Region (
  idRegion  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomRegion VARCHAR(80)  NOT NULL,
  idDep     INT UNSIGNED NOT NULL,
  PRIMARY KEY (idRegion),
  KEY idx_region_dep (idDep),
  CONSTRAINT fk_region_dep FOREIGN KEY (idDep) REFERENCES Departement(idDep)
) ENGINE=InnoDB;

CREATE TABLE Client (
  idCli     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomCli    VARCHAR(60)  NOT NULL,
  prenomCli VARCHAR(60)  NOT NULL,
  email     VARCHAR(120) NOT NULL,
  photo     VARCHAR(255) NULL,
  idRegion  INT UNSIGNED NOT NULL,
  PRIMARY KEY (idCli),
  UNIQUE KEY uq_client_email (email),
  KEY idx_client_region (idRegion),
  CONSTRAINT fk_client_region FOREIGN KEY (idRegion) REFERENCES Region(idRegion)
) ENGINE=InnoDB;

CREATE TABLE Produit (
  idProd     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  designProd VARCHAR(120) NOT NULL,
  qteStock   INT UNSIGNED NOT NULL DEFAULT 0,
  prixUProd  DECIMAL(10,2) UNSIGNED NOT NULL,
  photoProd  VARCHAR(255) NULL,
  PRIMARY KEY (idProd),
  KEY idx_produit_stock (qteStock)
) ENGINE=InnoDB;

CREATE TABLE Commande (
  idCmd   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  dateCmd DATE         NOT NULL,
  idCli   INT UNSIGNED NOT NULL,
  PRIMARY KEY (idCmd),
  KEY idx_commande_date (dateCmd),
  KEY idx_commande_client (idCli),
  CONSTRAINT fk_commande_client FOREIGN KEY (idCli) REFERENCES Client(idCli)
) ENGINE=InnoDB;

CREATE TABLE ligneCommande (
  idCmd   INT UNSIGNED NOT NULL,
  idProd  INT UNSIGNED NOT NULL,
  qteCmd  INT UNSIGNED NOT NULL,
  PRIMARY KEY (idCmd, idProd),              -- un produit une seule fois par commande
  KEY idx_ligne_produit (idProd),           -- accélère les stats « par produit »
  CONSTRAINT chk_ligne_qte CHECK (qteCmd > 0),
  CONSTRAINT fk_ligne_cmd  FOREIGN KEY (idCmd)  REFERENCES Commande(idCmd) ON DELETE CASCADE,
  CONSTRAINT fk_ligne_prod FOREIGN KEY (idProd) REFERENCES Produit(idProd)
) ENGINE=InnoDB;

-- Vue d'analyse : une ligne = un produit dans une commande, avec son montant
CREATE OR REPLACE VIEW v_lignes AS
SELECT c.idCmd, c.dateCmd, c.idCli, l.idProd, p.designProd,
       l.qteCmd, p.prixUProd, (l.qteCmd * p.prixUProd) AS montant
FROM Commande c
JOIN ligneCommande l ON l.idCmd = c.idCmd
JOIN Produit p       ON p.idProd = l.idProd;

-- Utilisateur imposé par l'énoncé (droits limités au strict nécessaire)
CREATE USER IF NOT EXISTS 'MasTMound2'@'localhost' IDENTIFIED BY 'MoundMasT2@2026';
GRANT SELECT, INSERT, UPDATE, DELETE ON gestion_commande.* TO 'MasTMound2'@'localhost';
FLUSH PRIVILEGES;
