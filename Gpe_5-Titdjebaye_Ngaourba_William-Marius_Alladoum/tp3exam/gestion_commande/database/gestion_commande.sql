-- Gestion d'une ligne de commande : structure + données de démonstration enrichies
-- ATTENTION : ce script SUPPRIME puis recrée la base « gestion_commande ».
DROP DATABASE IF EXISTS gestion_commande;
CREATE DATABASE gestion_commande
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_commande;

CREATE TABLE Departement (
  idDep INT AUTO_INCREMENT PRIMARY KEY,
  nomDep VARCHAR(100) NOT NULL
);

CREATE TABLE Region (
  idRegion INT AUTO_INCREMENT PRIMARY KEY,
  nomRegion VARCHAR(100) NOT NULL,
  idDep INT NOT NULL,
  CONSTRAINT fk_region_departement FOREIGN KEY (idDep)
    REFERENCES Departement(idDep)
);

CREATE TABLE Client (
  idCli INT AUTO_INCREMENT PRIMARY KEY,
  nomCli VARCHAR(100) NOT NULL,
  prenomCli VARCHAR(100) NOT NULL,
  email VARCHAR(150),
  photo VARCHAR(255),
  idRegion INT,
  CONSTRAINT fk_client_region FOREIGN KEY (idRegion)
    REFERENCES Region(idRegion) ON DELETE SET NULL
);

CREATE TABLE Produit (
  idProd INT AUTO_INCREMENT PRIMARY KEY,
  designProd VARCHAR(150) NOT NULL,
  qteStock INT NOT NULL DEFAULT 0,
  prixUProd DECIMAL(12,2) NOT NULL DEFAULT 0,
  photoProd VARCHAR(255)
);

CREATE TABLE Commande (
  idCmd INT AUTO_INCREMENT PRIMARY KEY,
  dateCmd DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  idCli INT NOT NULL,
  CONSTRAINT fk_commande_client FOREIGN KEY (idCli)
    REFERENCES Client(idCli)
);

CREATE TABLE ligneCommande (
  idCmd INT NOT NULL,
  idProd INT NOT NULL,
  qteCmd INT NOT NULL,
  PRIMARY KEY (idCmd, idProd),
  CONSTRAINT fk_ligne_commande FOREIGN KEY (idCmd)
    REFERENCES Commande(idCmd) ON DELETE CASCADE,
  CONSTRAINT fk_ligne_produit FOREIGN KEY (idProd)
    REFERENCES Produit(idProd),
  CHECK (qteCmd > 0)
);

-- Départements / régions ------------------------------------------------
INSERT INTO Departement(nomDep) VALUES
 ('Logone Occidental'),   -- 1
 ('N''Djamena'),          -- 2
 ('Logone Oriental'),     -- 3
 ('Mayo-Kebbi Est'),      -- 4
 ('Tandjilé'),            -- 5
 ('Moyen-Chari');         -- 6

INSERT INTO Region(nomRegion,idDep) VALUES
 ('Moundou Centre',1),    -- 1
 ('N''Djamena Centre',2), -- 2
 ('Moundou Nord',1),      -- 3
 ('Krim-Krim',1),         -- 4
 ('Doba',3),              -- 5
 ('Bébédjia',3),          -- 6
 ('Bongor',4),            -- 7
 ('Farcha',2),            -- 8
 ('Walia',2),             -- 9
 ('Laï',5),               -- 10
 ('Kélo',5),              -- 11
 ('Sarh',6);              -- 12

-- Clients (16) ------------------------------------------------------------
INSERT INTO Client(nomCli,prenomCli,email,idRegion) VALUES
 ('Mahamat','Ali','ali@example.com',1),                 -- 1
 ('Amina','Saleh','amina@example.com',2),               -- 2
 ('Ngarmbaye','Prisca','prisca.ngarmbaye@example.com',3), -- 3
 ('Djimadoum','Samuel','samuel.djimadoum@example.com',1), -- 4
 ('Tchoumba','Esther','esther.tchoumba@example.com',5),   -- 5
 ('Ndilbé','Joseph','joseph.ndilbe@example.com',4),       -- 6
 ('Abakar','Hassan','hassan.abakar@example.com',8),       -- 7
 ('Brahim','Fatimé','fatime.brahim@example.com',9),       -- 8
 ('Kemba','Rodrigue','rodrigue.kemba@example.com',6),     -- 9
 ('Madjirangar','Cécile','cecile.madjirangar@example.com',1), -- 10
 ('Idriss','Ahmat','ahmat.idriss@example.com',7),         -- 11
 ('Mbaïtoloum','Grâce','grace.mbaitoloum@example.com',10),-- 12
 ('Allahissem','Théodore','theodore.allahissem@example.com',11), -- 13
 ('Oumar','Zara','zara.oumar@example.com',2),             -- 14
 ('Ngaradoum','Pascal','pascal.ngaradoum@example.com',12),-- 15
 ('Bétoloum','Clarisse','clarisse.betoloum@example.com',3);-- 16

-- Produits (20) -----------------------------------------------------------
INSERT INTO Produit(designProd,qteStock,prixUProd) VALUES
 ('Ordinateur portable',15,350000),      -- 1
 ('Souris USB',80,5000),                 -- 2
 ('Clé USB 32 Go',45,7500),              -- 3
 ('Clavier',30,12000),                   -- 4
 ('Écran 24 pouces',12,95000),           -- 5
 ('Imprimante laser',8,120000),          -- 6
 ('Casque audio',25,15000),              -- 7
 ('Disque dur externe 1 To',18,45000),   -- 8
 ('Webcam HD',4,18000),                  -- 9
 ('Routeur Wi-Fi',14,30000),             -- 10
 ('Câble HDMI',60,3500),                 -- 11
 ('Chargeur universel',22,12500),        -- 12
 ('Tablette 10 pouces',7,110000),        -- 13
 ('Onduleur 650 VA',0,55000),            -- 14
 ('Souris sans fil',40,8500),            -- 15
 ('Clavier sans fil',28,16000),          -- 16
 ('Carte mémoire 64 Go',35,9500),        -- 17
 ('Multiprise parafoudre',3,7500),       -- 18
 ('Sac à dos ordinateur',16,22000),      -- 19
 ('Enceinte Bluetooth',9,28000);         -- 20

-- Commandes (16) réparties sur les 6 derniers mois ---------------------------
INSERT INTO Commande(dateCmd,idCli) VALUES
 (NOW(),1),                              -- 1
 (NOW(),2),                              -- 2
 (DATE_SUB(NOW(), INTERVAL 2 DAY),3),    -- 3
 (DATE_SUB(NOW(), INTERVAL 5 DAY),4),    -- 4
 (DATE_SUB(NOW(), INTERVAL 9 DAY),5),    -- 5
 (DATE_SUB(NOW(), INTERVAL 16 DAY),6),   -- 6
 (DATE_SUB(NOW(), INTERVAL 24 DAY),7),   -- 7
 (DATE_SUB(NOW(), INTERVAL 38 DAY),8),   -- 8
 (DATE_SUB(NOW(), INTERVAL 52 DAY),9),   -- 9
 (DATE_SUB(NOW(), INTERVAL 67 DAY),10),  -- 10
 (DATE_SUB(NOW(), INTERVAL 80 DAY),1),   -- 11
 (DATE_SUB(NOW(), INTERVAL 95 DAY),2),   -- 12
 (DATE_SUB(NOW(), INTERVAL 110 DAY),11), -- 13
 (DATE_SUB(NOW(), INTERVAL 130 DAY),12), -- 14
 (DATE_SUB(NOW(), INTERVAL 150 DAY),3),  -- 15
 (DATE_SUB(NOW(), INTERVAL 170 DAY),13); -- 16

-- Lignes de commande (quantité toujours <= stock) ---------------------------
INSERT INTO ligneCommande(idCmd,idProd,qteCmd) VALUES
 (1,1,1),(1,2,2),
 (2,3,3),
 (3,5,2),(3,11,4),(3,15,2),
 (4,6,1),(4,12,2),
 (5,13,1),(5,7,2),(5,17,3),
 (6,1,2),(6,19,2),
 (7,8,2),(7,16,1),(7,2,5),
 (8,10,1),(8,11,6),
 (9,20,2),(9,7,1),
 (10,5,1),(10,4,2),(10,3,10),
 (11,1,1),(11,9,2),(11,17,4),
 (12,13,2),(12,12,3),
 (13,6,1),(13,18,3),(13,15,4),
 (14,8,1),(14,10,2),(14,2,10),
 (15,19,3),(15,16,2),(15,20,1),
 (16,1,2),(16,5,2),(16,11,10);
