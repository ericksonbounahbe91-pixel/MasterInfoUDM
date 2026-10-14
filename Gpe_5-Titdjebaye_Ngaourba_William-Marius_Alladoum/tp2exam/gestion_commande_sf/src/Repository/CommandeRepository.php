<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Commande> */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    /**
     * Liste des commandes avec nombre de produits et montant.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listeAvecTotaux(): array
    {
        $sql = <<<'SQL'
            SELECT c.id, c.date_commande, cl.nom, cl.prenom,
                   COUNT(l.id) AS nb_lignes,
                   COALESCE(SUM(l.quantite * p.prix_unitaire), 0) AS montant
            FROM cmd_commande c
            JOIN cmd_client cl             ON cl.id = c.client_id
            LEFT JOIN cmd_ligne_commande l ON l.commande_id = c.id
            LEFT JOIN cmd_produit p        ON p.id = l.produit_id
            GROUP BY c.id, c.date_commande, cl.id, cl.nom, cl.prenom
            ORDER BY c.date_commande DESC, c.id DESC
        SQL;

        return $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);
    }
}
