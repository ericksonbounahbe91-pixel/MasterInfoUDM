-- Optimisations (analyse de performance du tableau de bord)
-- NB : InnoDB crée déjà un index sur chaque clé étrangère (Commande.idCli,
-- ligneCommande.idProd, Client.idRegion, Region.idDep) et la clé primaire
-- composée (idCmd, idProd) couvre les recherches par commande.

-- Regroupement / filtrage par date (CA par mois)
CREATE INDEX idx_commande_date ON Commande (dateCmd);

-- Alerte "stock faible" (WHERE qteStock <= 5)
CREATE INDEX idx_produit_stock ON Produit (qteStock);

-- Pour vérifier l'utilisation des index :
-- EXPLAIN SELECT * FROM Produit WHERE qteStock <= 5;
-- EXPLAIN SELECT DATE_FORMAT(dateCmd,'%Y-%m'), COUNT(*) FROM Commande GROUP BY 1;
