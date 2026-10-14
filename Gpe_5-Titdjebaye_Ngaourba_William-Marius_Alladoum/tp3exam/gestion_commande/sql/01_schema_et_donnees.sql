-- =====================================================================
--  TP Examen – Gestion d'une ligne de commande
--  Étape 1 : création des tables + données FICTIVES de test.
--  À exécuter uniquement pour tester en local. Si la base d'examen
--  existe déjà avec ses tables, passez directement à 02_vue_et_index.sql.
-- =====================================================================
CREATE DATABASE IF NOT EXISTS gestion_commande CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_commande;

-- (Optionnel, en local) création de l'utilisateur fourni par le sujet :
-- CREATE USER IF NOT EXISTS 'MasTMound2'@'localhost' IDENTIFIED BY 'MoundMasT2@2026';
-- GRANT ALL PRIVILEGES ON gestion_commande.* TO 'MasTMound2'@'localhost';
-- FLUSH PRIVILEGES;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS ligneCommande, Commande, Client, Produit, Region, Departement;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE Departement (
  idDep  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomDep VARCHAR(100) NOT NULL,
  PRIMARY KEY (idDep)
) ENGINE=InnoDB;

CREATE TABLE Region (
  idRegion  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomRegion VARCHAR(100) NOT NULL,
  idDep     INT UNSIGNED NOT NULL,
  PRIMARY KEY (idRegion),
  KEY idx_region_dep (idDep),
  CONSTRAINT fk_region_dep FOREIGN KEY (idDep) REFERENCES Departement (idDep)
) ENGINE=InnoDB;

CREATE TABLE Client (
  idCli     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomCli    VARCHAR(80)  NOT NULL,
  prenomCli VARCHAR(80)  NOT NULL,
  email     VARCHAR(150) DEFAULT NULL,
  photo     VARCHAR(255) DEFAULT NULL,
  idRegion  INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (idCli),
  KEY idx_client_region (idRegion),
  CONSTRAINT fk_client_region FOREIGN KEY (idRegion) REFERENCES Region (idRegion)
) ENGINE=InnoDB;

CREATE TABLE Produit (
  idProd     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  designProd VARCHAR(150) NOT NULL,
  qteStock   INT NOT NULL DEFAULT 0,
  prixUProd  DECIMAL(12,2) NOT NULL DEFAULT 0,
  photoProd  VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (idProd)
) ENGINE=InnoDB;

CREATE TABLE Commande (
  idCmd   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  dateCmd DATE NOT NULL,
  idCli   INT UNSIGNED NOT NULL,
  PRIMARY KEY (idCmd),
  KEY idx_commande_client (idCli),
  CONSTRAINT fk_commande_client FOREIGN KEY (idCli) REFERENCES Client (idCli)
) ENGINE=InnoDB;

CREATE TABLE ligneCommande (
  idCmd   INT UNSIGNED NOT NULL,
  idProd  INT UNSIGNED NOT NULL,
  qteCmd  INT UNSIGNED NOT NULL,
  PRIMARY KEY (idCmd, idProd),
  KEY idx_ligne_produit (idProd),
  CONSTRAINT fk_ligne_cmd  FOREIGN KEY (idCmd)  REFERENCES Commande (idCmd),
  CONSTRAINT fk_ligne_prod FOREIGN KEY (idProd) REFERENCES Produit (idProd)
) ENGINE=InnoDB;

-- ---------------------- Données fictives de test ----------------------
INSERT INTO Departement (idDep, nomDep) VALUES
 (1,'Dodjé'), (2,'Kouh-Est'), (3,'Kouh-Ouest'), (4,'Monts de Lam');

INSERT INTO Region (idRegion, nomRegion, idDep) VALUES
 (1,'Moundou',1), (2,'Béinamar',1), (3,'Doba',2), (4,'Bébédjia',2), (5,'Krim Krim',3), (6,'Bodo',4);

INSERT INTO Client (idCli, nomCli, prenomCli, email, photo, idRegion) VALUES
 (1,'NGARTOLNODJI','Marie','marie.ngartolnodji@example.com',NULL,1),
 (2,'DJIMADOUM','Jean','jean.djimadoum@example.com',NULL,1),
 (3,'MBAIGOTO','Esther','esther.mbaigoto@example.com',NULL,2),
 (4,'KOUMBAIRIA','Paul','paul.koumbairia@example.com',NULL,3),
 (5,'NGAKOUTOU','Rachel','rachel.ngakoutou@example.com',NULL,4),
 (6,'TCHOUA','Daniel','daniel.tchoua@example.com',NULL,5),
 (7,'BEREMADJI','Lydie','lydie.beremadji@example.com',NULL,6),
 (8,'ALLADOUM','Samuel','samuel.alladoum@example.com',NULL,1);

INSERT INTO Produit (idProd, designProd, qteStock, prixUProd, photoProd) VALUES
 (1,'Riz local 25 kg',120,14500,NULL),
 (2,'Huile végétale 5 L',8,6500,NULL),
 (3,'Sucre 50 kg',45,32000,NULL),
 (4,'Farine de blé 25 kg',60,11000,NULL),
 (5,'Lait en poudre 400 g',200,2800,NULL),
 (6,'Savon de ménage (carton)',75,9000,NULL),
 (7,'Pâtes alimentaires (carton)',5,7500,NULL),
 (8,'Eau minérale 1,5 L (pack de 6)',300,1800,NULL),
 (9,'Sel fin 1 kg',150,450,NULL),
 (10,'Thé en sachets (boîte)',90,1200,NULL);

INSERT INTO Commande (idCmd, dateCmd, idCli) VALUES
 (1,'2026-01-12',1),(2,'2026-01-28',2),(3,'2026-02-09',3),(4,'2026-02-21',1),
 (5,'2026-03-05',4),(6,'2026-03-18',5),(7,'2026-04-02',2),(8,'2026-04-23',6),
 (9,'2026-05-14',7),(10,'2026-05-30',3),(11,'2026-06-11',8),(12,'2026-07-07',1),
 (13,'2026-08-19',4),(14,'2026-09-15',5);

INSERT INTO ligneCommande (idCmd, idProd, qteCmd) VALUES
 (1,1,10),(1,5,24),
 (2,3,2),(2,9,40),(2,4,5),
 (3,8,50),(3,2,6),(3,5,12),
 (4,1,8),(4,6,10),
 (5,4,10),(5,3,1),
 (6,2,12),(6,7,4),(6,10,20),
 (7,8,80),(7,5,30),
 (8,1,6),(8,9,25),
 (9,6,8),(9,7,3),(9,3,2),
 (10,5,40),(10,10,15),
 (11,1,15),(11,4,6),
 (12,2,10),(12,8,60),
 (13,3,3),(13,7,6),(13,9,30),
 (14,1,12),(14,5,18),(14,6,5);
