-- TP Examen 1 – Gestion d'une ligne de Commande
-- Exécuter ce script dans phpMyAdmin ou en CLI avant d'utiliser l'application.

CREATE DATABASE IF NOT EXISTS gestion_commandes
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_commandes;

-- Département
CREATE TABLE IF NOT EXISTS Departement (
  idDep      INT AUTO_INCREMENT PRIMARY KEY,
  nomDep     VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- Region
CREATE TABLE IF NOT EXISTS Region (
  idRegion   INT AUTO_INCREMENT PRIMARY KEY,
  nomRegion  VARCHAR(100) NOT NULL,
  idDep      INT NOT NULL,
  FOREIGN KEY (idDep) REFERENCES Departement(idDep) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Client
CREATE TABLE IF NOT EXISTS Client (
  idCli      INT AUTO_INCREMENT PRIMARY KEY,
  nomCli     VARCHAR(100) NOT NULL,
  prenomCli  VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL UNIQUE,
  photo      VARCHAR(255) DEFAULT NULL,
  idRegion   INT NOT NULL,
  FOREIGN KEY (idRegion) REFERENCES Region(idRegion) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Produit
CREATE TABLE IF NOT EXISTS Produit (
  idProd     INT AUTO_INCREMENT PRIMARY KEY,
  designProd VARCHAR(150) NOT NULL,
  qteStock   INT NOT NULL DEFAULT 0,
  prixUProd  DECIMAL(10,2) NOT NULL DEFAULT 0,
  photoProd  VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

-- Commande
CREATE TABLE IF NOT EXISTS Commande (
  idCmd      INT AUTO_INCREMENT PRIMARY KEY,
  dateCmd    DATE NOT NULL,
  idCli      INT NOT NULL,
  FOREIGN KEY (idCli) REFERENCES Client(idCli) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Ligne de commande (table associative)
CREATE TABLE IF NOT EXISTS ligneCommande (
  idCmd      INT NOT NULL,
  idProd     INT NOT NULL,
  qteCmd     INT NOT NULL DEFAULT 1,
  PRIMARY KEY (idCmd, idProd),
  FOREIGN KEY (idCmd)  REFERENCES Commande(idCmd) ON DELETE CASCADE,
  FOREIGN KEY (idProd) REFERENCES Produit(idProd)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============ Données de test ============

INSERT INTO Departement (nomDep) VALUES
('Wouri'), ('Mfoundi'), ('Diamaré'), ('Mifi'), ('Océan');

INSERT INTO Region (nomRegion, idDep) VALUES
('Littoral', 1), ('Centre', 2), ('Extrême-Nord', 3), ('Ouest', 4), ('Sud', 5);

INSERT INTO Client (nomCli, prenomCli, email, idRegion) VALUES
('Ngomde Cadet', 'Kamdaou', 'kamdaou@example.com', 1),
('Bediang', 'Leandrine Orline', 'leandrine@example.com', 2),
('Tamba', 'Paul', 'paul.tamba@example.com', 3),
('Fouda', 'Marie', 'marie.fouda@example.com', 4),
('Eyinga', 'Jean', 'jean.eyinga@example.com', 5);

INSERT INTO Produit (designProd, qteStock, prixUProd) VALUES
('Ordinateur Portable HP', 25, 450000),
('Souris sans fil Logitech', 150, 12000),
('Clavier mécanique', 80, 35000),
('Écran 27 pouces Samsung', 40, 185000),
('Casque Bluetooth JBL', 100, 28000),
('Clé USB 64 Go', 200, 8000),
('Disque dur externe 1 To', 60, 45000),
('Imprimante Canon', 30, 95000);

INSERT INTO Commande (dateCmd, idCli) VALUES
('2026-09-15', 1), ('2026-09-20', 2), ('2026-10-01', 3),
('2026-10-05', 1), ('2026-10-10', 4);

INSERT INTO ligneCommande (idCmd, idProd, qteCmd) VALUES
(1, 1, 2), (1, 2, 5), (1, 5, 3),
(2, 3, 1), (2, 4, 2),
(3, 6, 10), (3, 7, 2), (3, 2, 4),
(4, 8, 1), (4, 1, 1),
(5, 5, 6), (5, 6, 20);
