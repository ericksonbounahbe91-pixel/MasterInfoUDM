<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

/**
 * Requêtes d'agrégation du tableau de bord.
 * Toutes les sommes sont calculées par MySQL (SUM / COUNT / GROUP BY) :
 * PHP ne reçoit que quelques lignes de résultat, jamais toute la table.
 */
class StatistiquesService
{
    // Jointure commune : commande → lignes → produit (le prix vient du produit)
    private const LIGNES = 'FROM cmd_commande c
        JOIN cmd_ligne_commande l ON l.commande_id = c.id
        JOIN cmd_produit p        ON p.id = l.produit_id';

    public function __construct(private Connection $db)
    {
    }

    /** @return array{0: ?string, 1: ?string} première et dernière date de commande */
    public function bornes(): array
    {
        $r = $this->db->fetchAssociative('SELECT MIN(date_commande) AS mn, MAX(date_commande) AS mx FROM cmd_commande');
        return [$r['mn'] ?? null, $r['mx'] ?? null];
    }

    /** @return array<string, int|float> */
    public function indicateurs(string $du, string $au): array
    {
        $r = $this->db->fetchAssociative(
            'SELECT COUNT(DISTINCT c.id) AS nb_cmd, COUNT(DISTINCT c.client_id) AS nb_cli,
                    COALESCE(SUM(l.quantite), 0) AS qte,
                    COALESCE(SUM(l.quantite * p.prix_unitaire), 0) AS ca ' . self::LIGNES . '
             WHERE c.date_commande BETWEEN :du AND :au',
            ['du' => $du, 'au' => $au]
        );
        $nbCmd = (int) $r['nb_cmd'];
        $ca = (int) $r['ca'];
        return [
            'nb_cmd' => $nbCmd,
            'nb_cli' => (int) $r['nb_cli'],
            'qte' => (int) $r['qte'],
            'ca' => $ca,
            'panier' => $nbCmd > 0 ? (int) round($ca / $nbCmd) : 0,
        ];
    }

    /** @return array<int, array{mois: string, ca: int, nb: int, pct: int}> */
    public function parMois(string $du, string $au): array
    {
        $lignes = $this->db->fetchAllAssociative(
            "SELECT DATE_FORMAT(c.date_commande, '%Y-%m') AS mois,
                    SUM(l.quantite * p.prix_unitaire) AS ca, COUNT(DISTINCT c.id) AS nb " . self::LIGNES . '
             WHERE c.date_commande BETWEEN :du AND :au
             GROUP BY DATE_FORMAT(c.date_commande, \'%Y-%m\')
             ORDER BY mois',
            ['du' => $du, 'au' => $au]
        );
        return $this->avecPourcentage($lignes);
    }

    /** @return array<int, array{libelle: string, ca: int, pct: int}> */
    public function topProduits(string $du, string $au, int $limite = 5): array
    {
        return $this->avecPourcentage($this->db->fetchAllAssociative(
            'SELECT p.designation AS libelle, SUM(l.quantite * p.prix_unitaire) AS ca ' . self::LIGNES . '
             WHERE c.date_commande BETWEEN :du AND :au
             GROUP BY p.id, p.designation
             ORDER BY ca DESC LIMIT ' . (int) $limite,
            ['du' => $du, 'au' => $au]
        ));
    }

    /** @return array<int, array{libelle: string, ca: int, pct: int}> */
    public function parDepartement(string $du, string $au): array
    {
        return $this->avecPourcentage($this->db->fetchAllAssociative(
            'SELECT d.nom AS libelle, SUM(l.quantite * p.prix_unitaire) AS ca ' . self::LIGNES . '
             JOIN cmd_client cl      ON cl.id = c.client_id
             JOIN cmd_region r       ON r.id = cl.region_id
             JOIN cmd_departement d  ON d.id = r.departement_id
             WHERE c.date_commande BETWEEN :du AND :au
             GROUP BY d.id, d.nom
             ORDER BY ca DESC',
            ['du' => $du, 'au' => $au]
        ));
    }

    /** @return array<int, array{libelle: string, ca: int, pct: int}> */
    public function topClients(string $du, string $au, int $limite = 5): array
    {
        return $this->avecPourcentage($this->db->fetchAllAssociative(
            "SELECT CONCAT(cl.nom, ' ', cl.prenom) AS libelle, SUM(l.quantite * p.prix_unitaire) AS ca " . self::LIGNES . '
             JOIN cmd_client cl ON cl.id = c.client_id
             WHERE c.date_commande BETWEEN :du AND :au
             GROUP BY cl.id, cl.nom, cl.prenom
             ORDER BY ca DESC LIMIT ' . (int) $limite,
            ['du' => $du, 'au' => $au]
        ));
    }

    /**
     * Ajoute à chaque ligne un pourcentage (0-100) par rapport à la plus grande valeur :
     * c'est ce qui sert de largeur aux barres et de hauteur aux colonnes du graphique.
     *
     * @param array<int, array<string, mixed>> $lignes
     * @return array<int, array<string, mixed>>
     */
    private function avecPourcentage(array $lignes): array
    {
        foreach ($lignes as &$l) {
            $l['ca'] = (int) $l['ca'];
            if (isset($l['nb'])) {
                $l['nb'] = (int) $l['nb'];
            }
        }
        unset($l);

        $max = $lignes ? max(array_column($lignes, 'ca')) : 0;
        foreach ($lignes as &$l) {
            $l['pct'] = $max > 0 ? (int) round($l['ca'] / $max * 100) : 0;
        }
        return $lignes;
    }
}
