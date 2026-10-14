-- =====================================================================
--  Étape 2 : vue et index utilisés par le tableau de bord.
--  À exécuter sur la base d'examen (compte avec droits CREATE / INDEX).
--  Aucune donnée n'est modifiée.
-- =====================================================================
USE tp_commande_2026;

-- Index utiles pour les filtres par période et les jointures du tableau de bord.
-- (Si un index existe déjà, MySQL signale une erreur : ignorez-la.)
CREATE INDEX idx_commande_date ON cmd_commande (dateCmd);
CREATE INDEX idx_commande_date_client ON cmd_commande (dateCmd, idCli);

-- Vue : une ligne par produit commandé, avec la date, le client et le montant.
CREATE OR REPLACE VIEW cmd_v_ligne_detail AS
SELECT c.idCmd, c.dateCmd, c.idCli,
       lc.idProd, lc.qteCmd, p.prixUProd,
       lc.qteCmd * p.prixUProd AS montant
FROM cmd_commande c
JOIN cmd_lignecommande lc ON lc.idCmd  = c.idCmd
JOIN cmd_produit p        ON p.idProd = lc.idProd;
