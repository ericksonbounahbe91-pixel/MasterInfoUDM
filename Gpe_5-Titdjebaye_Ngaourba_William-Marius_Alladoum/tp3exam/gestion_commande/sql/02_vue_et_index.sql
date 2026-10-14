-- =====================================================================
--  Étape 2 : vue et index utilisés par le tableau de bord.
--  À exécuter sur la base d'examen (compte avec droits CREATE / INDEX).
--  Aucune donnée n'est modifiée.
-- =====================================================================
USE gestion_commande;

-- Index utiles pour les filtres par période et les jointures du tableau de bord.
-- (Si un index existe déjà, MySQL signale une erreur : ignorez-la.)
CREATE INDEX idx_commande_date ON Commande (dateCmd);
CREATE INDEX idx_commande_date_client ON Commande (dateCmd, idCli);

-- Vue : une ligne par produit commandé, avec la date, le client et le montant.
CREATE OR REPLACE VIEW v_ligne_detail AS
SELECT c.idCmd, c.dateCmd, c.idCli,
       lc.idProd, lc.qteCmd, p.prixUProd,
       lc.qteCmd * p.prixUProd AS montant
FROM Commande c
JOIN ligneCommande lc ON lc.idCmd  = c.idCmd
JOIN Produit p        ON p.idProd = lc.idProd;
