-- Création de la base de données
CREATE DATABASE IF NOT EXISTS gestionCommandes
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE gestionCommandes;

-- Suppression des tables (ordre inverse des dépendances)
DROP TABLE IF EXISTS ligneCommande;
DROP TABLE IF EXISTS Commande;
DROP TABLE IF EXISTS Client;
DROP TABLE IF EXISTS Produit;
DROP TABLE IF EXISTS Region;
DROP TABLE IF EXISTS Departement;

-- Département
CREATE TABLE Departement (
  idDep  INT AUTO_INCREMENT PRIMARY KEY,
  nomDep VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- Région
CREATE TABLE Region (
  idRegion  INT AUTO_INCREMENT PRIMARY KEY,
  nomRegion VARCHAR(100) NOT NULL,
  idDep     INT NOT NULL,
  CONSTRAINT fk_region_departement
    FOREIGN KEY (idDep) REFERENCES Departement(idDep)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Client
CREATE TABLE Client (
  idCli     INT AUTO_INCREMENT PRIMARY KEY,
  nomCli    VARCHAR(100) NOT NULL,
  prenomCli VARCHAR(100) NOT NULL,
  email     VARCHAR(150) NOT NULL UNIQUE,
  photo     VARCHAR(255) NULL,
  idRegion  INT NOT NULL,
  CONSTRAINT fk_client_region
    FOREIGN KEY (idRegion) REFERENCES Region(idRegion)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Produit
CREATE TABLE Produit (
  idProd     INT AUTO_INCREMENT PRIMARY KEY,
  designProd VARCHAR(150) NOT NULL,
  qteStock   INT NOT NULL DEFAULT 0,
  prixUProd  DECIMAL(10,2) NOT NULL,
  photoProd  VARCHAR(255) NULL,
  CONSTRAINT chk_produit_stock CHECK (qteStock >= 0),
  CONSTRAINT chk_produit_prix  CHECK (prixUProd >= 0)
) ENGINE=InnoDB;

-- Commande
CREATE TABLE Commande (
  idCmd   INT AUTO_INCREMENT PRIMARY KEY,
  dateCmd DATE NOT NULL,
  idCli   INT NOT NULL,
  CONSTRAINT fk_commande_client
    FOREIGN KEY (idCli) REFERENCES Client(idCli)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Ligne de commande (clé primaire composée)
CREATE TABLE ligneCommande (
  idCmd  INT NOT NULL,
  idProd INT NOT NULL,
  qteCmd INT NOT NULL,
  PRIMARY KEY (idCmd, idProd),
  CONSTRAINT fk_ligne_commande
    FOREIGN KEY (idCmd) REFERENCES Commande(idCmd)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_ligne_produit
    FOREIGN KEY (idProd) REFERENCES Produit(idProd)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_ligne_qte CHECK (qteCmd > 0)
) ENGINE=InnoDB;