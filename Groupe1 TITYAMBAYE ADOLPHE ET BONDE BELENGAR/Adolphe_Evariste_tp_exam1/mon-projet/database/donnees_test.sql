-- Jeu de données de test (à exécuter APRÈS le script de création des tables)
INSERT INTO Departement (idDep, nomDep) VALUES
 (1,'Mayo-Kebbi'), (2,'Logone'), (3,'Chari-Baguirmi');

INSERT INTO Region (idRegion, nomRegion, idDep) VALUES
 (1,'Bongor',1), (2,'Moundou',2), (3,'Doba',2), (4,'N''Djamena',3), (5,'Massenya',3);

INSERT INTO Client (idCli, nomCli, prenomCli, email, photo, idRegion) VALUES
 (1,'Ali','Moussa','ali.moussa@mail.com',NULL,1),
 (2,'Mahamat','Fatimé','fatime.mahamat@mail.com',NULL,2),
 (3,'Ngarta','Jean','jean.ngarta@mail.com',NULL,2),
 (4,'Abakar','Halimé','halime.abakar@mail.com',NULL,4),
 (5,'Deby','Issa','issa.deby@mail.com',NULL,4),
 (6,'Tchari','Marie','marie.tchari@mail.com',NULL,3),
 (7,'Hassan','Oumar','oumar.hassan@mail.com',NULL,5),
 (8,'Kemba','Pauline','pauline.kemba@mail.com',NULL,1);

INSERT INTO Produit (idProd, designProd, qteStock, prixUProd, photoProd) VALUES
 (1,'Ordinateur portable',15,350000.00,NULL),
 (2,'Imprimante laser',8,120000.00,NULL),
 (3,'Clavier USB',60,6500.00,NULL),
 (4,'Souris sans fil',45,4500.00,NULL),
 (5,'Écran 24 pouces',4,95000.00,NULL),
 (6,'Disque dur externe 1 To',22,40000.00,NULL),
 (7,'Clé USB 64 Go',3,7500.00,NULL),
 (8,'Routeur Wi-Fi',12,28000.00,NULL);

INSERT INTO Commande (idCmd, dateCmd, idCli) VALUES
 (1,'2026-01-12',1), (2,'2026-01-25',2), (3,'2026-02-03',3), (4,'2026-02-18',4),
 (5,'2026-03-07',5), (6,'2026-04-14',1), (7,'2026-05-02',6), (8,'2026-06-21',7),
 (9,'2026-07-09',2), (10,'2026-08-15',4), (11,'2026-09-04',8), (12,'2026-10-02',5);

INSERT INTO ligneCommande (idCmd, idProd, qteCmd) VALUES
 (1,1,1),(1,3,2),
 (2,2,1),(2,4,3),
 (3,1,2),(3,6,1),
 (4,3,5),(4,4,5),(4,8,1),
 (5,5,2),(5,7,4),
 (6,1,1),(6,2,1),(6,6,2),
 (7,8,2),(7,3,3),
 (8,1,3),(8,5,1),
 (9,6,3),(9,4,2),(9,7,2),
 (10,2,2),(10,8,1),
 (11,3,4),(11,1,1),
 (12,5,1),(12,6,1),(12,4,6);
