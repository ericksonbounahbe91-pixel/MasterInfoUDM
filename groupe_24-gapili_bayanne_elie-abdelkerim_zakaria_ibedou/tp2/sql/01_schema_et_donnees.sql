-- =====================================================================
--  TP Examen – Gestion des commandes – Outils numériques
--  Étape 1 : création des tables + données FICTIVES de test.
--  À exécuter uniquement pour tester en local. Si la base d'examen
--  existe déjà avec ses tables, passez directement à 02_vue_et_index.sql.
-- =====================================================================
CREATE DATABASE IF NOT EXISTS tp_commande_2026 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tp_commande_2026;

-- (Optionnel, en local) création de l'utilisateur fourni par le sujet :
-- CREATE USER IF NOT EXISTS 'MasTMound2'@'localhost' IDENTIFIED BY 'MoundMasT2@2026';
-- GRANT ALL PRIVILEGES ON tp_commande_2026.* TO 'MasTMound2'@'localhost';
-- FLUSH PRIVILEGES;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS cmd_lignecommande, cmd_commande, cmd_client, cmd_produit, cmd_region, cmd_departement;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE cmd_departement (
  idDep  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomDep VARCHAR(100) NOT NULL,
  PRIMARY KEY (idDep)
) ENGINE=InnoDB;

CREATE TABLE cmd_region (
  idRegion  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomRegion VARCHAR(100) NOT NULL,
  idDep     INT UNSIGNED NOT NULL,
  PRIMARY KEY (idRegion),
  KEY idx_region_dep (idDep),
  CONSTRAINT fk_region_dep FOREIGN KEY (idDep) REFERENCES cmd_departement (idDep)
) ENGINE=InnoDB;

CREATE TABLE cmd_client (
  idCli     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomCli    VARCHAR(80)  NOT NULL,
  prenomCli VARCHAR(80)  NOT NULL,
  email     VARCHAR(150) DEFAULT NULL,
  photo     VARCHAR(255) DEFAULT NULL,
  idRegion  INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (idCli),
  KEY idx_client_region (idRegion),
  CONSTRAINT fk_client_region FOREIGN KEY (idRegion) REFERENCES cmd_region (idRegion)
) ENGINE=InnoDB;

CREATE TABLE cmd_produit (
  idProd     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  designProd VARCHAR(150) NOT NULL,
  categorieProd ENUM('Logiciels','Matériel','Services') NOT NULL DEFAULT 'Logiciels',
  uniteProd  VARCHAR(30) NOT NULL DEFAULT 'unité',
  qteStock   INT NOT NULL DEFAULT 0,
  prixUProd  DECIMAL(12,2) NOT NULL DEFAULT 0,
  photoProd  VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (idProd),
  KEY idx_produit_categorie (categorieProd)
) ENGINE=InnoDB;

CREATE TABLE cmd_commande (
  idCmd   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  dateCmd DATE NOT NULL,
  idCli   INT UNSIGNED NOT NULL,
  PRIMARY KEY (idCmd),
  KEY idx_commande_client (idCli),
  CONSTRAINT fk_commande_client FOREIGN KEY (idCli) REFERENCES cmd_client (idCli)
) ENGINE=InnoDB;

CREATE TABLE cmd_lignecommande (
  idCmd   INT UNSIGNED NOT NULL,
  idProd  INT UNSIGNED NOT NULL,
  qteCmd  INT UNSIGNED NOT NULL,
  PRIMARY KEY (idCmd, idProd),
  KEY idx_ligne_produit (idProd),
  CONSTRAINT fk_ligne_cmd  FOREIGN KEY (idCmd)  REFERENCES cmd_commande (idCmd),
  CONSTRAINT fk_ligne_prod FOREIGN KEY (idProd) REFERENCES cmd_produit (idProd)
) ENGINE=InnoDB;

-- ---------------------- Données fictives de test ----------------------
INSERT INTO cmd_departement (idDep, nomDep) VALUES
 (1,'Dodjé'), (2,'Kouh-Est'), (3,'Kouh-Ouest'), (4,'Monts de Lam');

INSERT INTO cmd_region (idRegion, nomRegion, idDep) VALUES
 (1,'Moundou',1), (2,'Béinamar',1), (3,'Doba',2), (4,'Bébédjia',2), (5,'Krim Krim',3), (6,'Bodo',4);

INSERT INTO cmd_client (idCli, nomCli, prenomCli, email, photo, idRegion) VALUES
 (1,'TCHOUKOUA','Alexis','alexis.tchoukoua@startup-moundou.td',NULL,1),
 (2,'MAHAMAT','Fatimé','fatime.mahamat@digitalsahel.td',NULL,1),
 (3,'NODJIGOTO','Cédric','cedric.nodjigoto@cybercafe-beinamar.td',NULL,2),
 (4,'ABAKAR','Hawa','hawa.abakar@ecole-doba.td',NULL,3),
 (5,'DJEKOUNDADE','Brice','brice.djekoundade@pharmacie-bebedjia.td',NULL,4),
 (6,'LAOUKOURA','Priscille','priscille.laoukoura@agri-krimkrim.td',NULL,5),
 (7,'MOUSSA','Idriss','idriss.moussa@boutique-bodo.td',NULL,6),
 (8,'NDOUBA','Annette','annette.ndouba@cabinet-conseil.td',NULL,1),
 (9,'KEMBE','Rodrigue','rodrigue.kembe@transit-logistik.td',NULL,3),
 (10,'YAMINGUE','Sandrine','sandrine.yamingue@clinique-moundou.td',NULL,1);

INSERT INTO cmd_produit (idProd, designProd, categorieProd, uniteProd, qteStock, prixUProd, photoProd) VALUES
 (1,'Licence antivirus Pro (1 an)','Logiciels','licence',500,25000,NULL),
 (2,'Suite bureautique (licence annuelle)','Logiciels','licence',300,45000,NULL),
 (3,'Logiciel de gestion commerciale','Logiciels','licence',80,180000,NULL),
 (4,'Abonnement cloud 100 Go','Logiciels','mois',1000,5000,NULL),
 (5,'Application mobile sur mesure','Logiciels','forfait',6,1500000,NULL),
 (6,'Ordinateur portable 15 pouces','Matériel','unité',25,650000,NULL),
 (7,'Smartphone Android 128 Go','Matériel','unité',60,180000,NULL),
 (8,'Routeur Wi-Fi 6','Matériel','unité',40,55000,NULL),
 (9,'Disque SSD externe 1 To','Matériel','unité',8,48000,NULL),
 (10,'Caméra IP de surveillance','Matériel','unité',7,38000,NULL),
 (11,'Création de site web vitrine','Services','forfait',10,450000,NULL),
 (12,'Maintenance informatique mensuelle','Services','mois',30,60000,NULL),
 (13,'Formation bureautique (session)','Services','session',15,75000,NULL),
 (14,'Hébergement web + nom de domaine','Services','an',100,35000,NULL);

INSERT INTO cmd_commande (idCmd, dateCmd, idCli) VALUES
 (1,'2026-01-12',1),(2,'2026-01-28',2),(3,'2026-02-09',3),(4,'2026-02-21',1),
 (5,'2026-03-05',4),(6,'2026-03-18',5),(7,'2026-04-02',2),(8,'2026-04-23',6),
 (9,'2026-05-14',7),(10,'2026-05-30',3),(11,'2026-06-11',8),(12,'2026-07-07',1),
 (13,'2026-08-19',4),(14,'2026-09-15',5),
 (15,'2026-09-28',9),(16,'2026-10-06',10);

INSERT INTO cmd_lignecommande (idCmd, idProd, qteCmd) VALUES
 (1,1,10),(1,2,5),
 (2,6,2),(2,8,3),
 (3,11,1),(3,14,1),
 (4,4,12),(4,13,2),
 (5,3,1),(5,13,1),
 (6,7,4),(6,9,6),
 (7,12,6),(7,10,4),
 (8,2,15),(8,1,15),
 (9,5,1),(9,14,2),
 (10,6,3),(10,8,4),
 (11,3,2),(11,12,3),(11,4,6),
 (12,7,6),(12,13,2),
 (13,11,1),(13,10,5),
 (14,6,5),(14,12,6),(14,1,20),
 (15,9,4),(15,4,10),
 (16,3,1),(16,14,3),(16,2,8);
