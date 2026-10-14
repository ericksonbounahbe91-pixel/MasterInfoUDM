<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Client> */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * Liste des clients avec nombre de commandes et montant total.
     * Requête SQL directe : une seule requête agrégée (au lieu d'une par client).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listeAvecTotaux(): array
    {
        $sql = <<<'SQL'
            SELECT cl.id, cl.nom, cl.prenom, cl.email, cl.photo,
                   r.nom AS region, d.nom AS departement,
                   COUNT(DISTINCT c.id) AS nb_commandes,
                   COALESCE(SUM(l.quantite * p.prix_unitaire), 0) AS total
            FROM cmd_client cl
            LEFT JOIN cmd_region r         ON r.id = cl.region_id
            LEFT JOIN cmd_departement d    ON d.id = r.departement_id
            LEFT JOIN cmd_commande c       ON c.client_id = cl.id
            LEFT JOIN cmd_ligne_commande l ON l.commande_id = c.id
            LEFT JOIN cmd_produit p        ON p.id = l.produit_id
            GROUP BY cl.id, cl.nom, cl.prenom, cl.email, cl.photo, r.nom, d.nom
            ORDER BY cl.nom, cl.prenom
        SQL;

        return $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);
    }
}
