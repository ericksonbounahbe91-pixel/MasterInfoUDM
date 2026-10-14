<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Produit> */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    /** @return Produit[] */
    public function recherche(string $q = ''): array
    {
        $qb = $this->createQueryBuilder('p')->orderBy('p.designation', 'ASC');
        if ($q !== '') {
            $qb->andWhere('p.designation LIKE :q')->setParameter('q', '%' . $q . '%');
        }
        return $qb->getQuery()->getResult();
    }

    /** @return Produit[] */
    public function stockBas(int $seuil, int $limite = 8): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.stock <= :seuil')->setParameter('seuil', $seuil)
            ->orderBy('p.stock', 'ASC')->addOrderBy('p.designation', 'ASC')
            ->setMaxResults($limite)
            ->getQuery()->getResult();
    }
}
