-- =====================================================================
--  Migration : passer une base EXISTANTE au catalogue Immobilier / Transport.
--  Les commandes et clients sont conservés. Les 10 premiers produits sont
--  renommés, les autres sont ajoutés. Si vous avez importé la nouvelle
--  version de 01_schema_et_donnees.sql, cette étape est inutile.
-- =====================================================================
USE tp_commande_2026;

ALTER TABLE cmd_produit
  ADD COLUMN categorieProd ENUM('Immobilier','Transport') NOT NULL DEFAULT 'Immobilier' AFTER designProd,
  ADD COLUMN uniteProd VARCHAR(30) NOT NULL DEFAULT 'unité' AFTER categorieProd,
  ADD KEY idx_produit_categorie (categorieProd);

INSERT INTO cmd_produit (idProd, designProd, categorieProd, uniteProd, qteStock, prixUProd) VALUES
 (1,'Location appartement F3','Immobilier','mois',60,150000),
 (2,'Location studio meublé','Immobilier','mois',120,75000),
 (3,'Location bureau / local commercial','Immobilier','mois',40,200000),
 (4,'Terrain viabilisé 500 m²','Immobilier','vente',8,12000000),
 (5,'Villa 4 chambres','Immobilier','vente',2,45000000),
 (6,'Gestion locative (frais mensuels)','Immobilier','mois',200,25000),
 (7,'Visite et expertise immobilière','Immobilier','visite',100,30000),
 (8,'Transport de marchandises – camion 10 t','Transport','trajet',15,350000),
 (9,'Location camion benne','Transport','jour',5,180000),
 (10,'Transport de personnes – minibus 18 places','Transport','trajet',8,120000),
 (11,'Déménagement résidentiel','Transport','forfait',12,250000),
 (12,'Location 4x4 avec chauffeur','Transport','jour',9,90000),
 (13,'Livraison express à moto','Transport','course',80,5000)
ON DUPLICATE KEY UPDATE
  designProd = VALUES(designProd), categorieProd = VALUES(categorieProd),
  uniteProd = VALUES(uniteProd), qteStock = VALUES(qteStock), prixUProd = VALUES(prixUProd);
