-- =====================================================================
--  Migration : base EXISTANTE (version d'origine) -> catalogue numérique.
--  Conserve les commandes ; remplace les produits 1 à 10, ajoute 11 à 14,
--  met à jour les clients 1 à 8 et ajoute les clients 9 et 10.
--  Si vous avez déjà importé une version précédente, ré-importez plutôt
--  01_schema_et_donnees.sql (données fictives) puis 02_vue_et_index.sql.
-- =====================================================================
USE tp_commande_2026;

ALTER TABLE cmd_produit
  ADD COLUMN categorieProd ENUM('Logiciels','Matériel','Services') NOT NULL DEFAULT 'Logiciels' AFTER designProd,
  ADD COLUMN uniteProd VARCHAR(30) NOT NULL DEFAULT 'unité' AFTER categorieProd,
  ADD KEY idx_produit_categorie (categorieProd);

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
 (14,'Hébergement web + nom de domaine','Services','an',100,35000,NULL)
ON DUPLICATE KEY UPDATE
  designProd = VALUES(designProd), categorieProd = VALUES(categorieProd),
  uniteProd = VALUES(uniteProd), qteStock = VALUES(qteStock), prixUProd = VALUES(prixUProd);

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
 (10,'YAMINGUE','Sandrine','sandrine.yamingue@clinique-moundou.td',NULL,1)
ON DUPLICATE KEY UPDATE
  nomCli = VALUES(nomCli), prenomCli = VALUES(prenomCli),
  email = VALUES(email), idRegion = VALUES(idRegion);
