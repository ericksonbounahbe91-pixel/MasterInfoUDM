<?php

namespace App\Service;

use App\Dto\LigneSaisie;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Règles métier des lignes de commande (le contrôleur reste « mince »).
 *
 * Les deux opérations sont exécutées dans une TRANSACTION : ligne + stock
 * réussissent ensemble ou sont annulés ensemble.
 */
class CommandeService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * Ajoute plusieurs produits à une commande et diminue le stock.
     *
     * @param LigneSaisie[] $saisies
     * @return int nombre de produits enregistrés
     * @throws \DomainException si la saisie est vide ou si le stock est insuffisant
     */
    public function ajouterLignes(Commande $commande, array $saisies): int
    {
        // 1. Regrouper : si le même produit est saisi deux fois, on additionne.
        $quantites = [];
        foreach ($saisies as $saisie) {
            if ($saisie->produit !== null && $saisie->quantite > 0) {
                $id = $saisie->produit->getId();
                $quantites[$id] = ($quantites[$id] ?? 0) + $saisie->quantite;
            }
        }
        if (!$quantites) {
            throw new \DomainException('Choisissez au moins un produit avec une quantité supérieure à 0.');
        }
        ksort($quantites); // ordre constant : évite les blocages entre deux utilisateurs

        $connexion = $this->em->getConnection();

        // 2. Tout dans une transaction (wrapInTransaction fait aussi le flush à la fin).
        $this->em->wrapInTransaction(function () use ($commande, $quantites, $connexion): void {
            foreach ($quantites as $idProduit => $quantite) {
                // Mise à jour ATOMIQUE du stock : la condition « stock >= quantité » est vérifiée
                // par MySQL lui-même. Deux acheteurs simultanés ne peuvent pas vendre le même stock.
                $modifiees = $connexion->executeStatement(
                    'UPDATE cmd_produit SET stock = stock - ? WHERE id = ? AND stock >= ?',
                    [$quantite, $idProduit, $quantite]
                );
                if ($modifiees === 0) {
                    $p = $connexion->fetchAssociative('SELECT designation, stock FROM cmd_produit WHERE id = ?', [$idProduit]);
                    throw new \DomainException(sprintf(
                        'Stock insuffisant pour « %s » : %d disponible(s), %d demandé(s). Aucune ligne n\'a été enregistrée.',
                        $p['designation'] ?? 'produit inconnu',
                        (int) ($p['stock'] ?? 0),
                        $quantite
                    ));
                }

                $produit = $this->em->getRepository(Produit::class)->find($idProduit);
                $ligne = $this->em->getRepository(LigneCommande::class)->findOneBy(['commande' => $commande, 'produit' => $produit]);
                if ($ligne !== null) {
                    $ligne->setQuantite($ligne->getQuantite() + $quantite);   // le produit était déjà dans la commande
                } else {
                    $ligne = (new LigneCommande())->setCommande($commande)->setProduit($produit)->setQuantite($quantite);
                    $this->em->persist($ligne);
                }
            }
        });

        return count($quantites);
    }

    /** Retire une ligne de la commande et remet la quantité en stock. */
    public function retirerLigne(LigneCommande $ligne): void
    {
        $this->em->wrapInTransaction(function () use ($ligne): void {
            $this->em->getConnection()->executeStatement(
                'UPDATE cmd_produit SET stock = stock + ? WHERE id = ?',
                [$ligne->getQuantite(), $ligne->getProduit()->getId()]
            );
            $this->em->remove($ligne);
        });
    }
}
