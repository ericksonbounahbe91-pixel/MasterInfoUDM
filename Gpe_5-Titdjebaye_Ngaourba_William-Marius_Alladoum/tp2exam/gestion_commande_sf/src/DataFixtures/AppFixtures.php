<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\Departement;
use App\Entity\LigneCommande;
use App\Entity\Produit;
use App\Entity\Region;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/** Données FICTIVES de test (chargées par : php bin/console doctrine:fixtures:load). */
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $departements = [];
        foreach (['Dodjé', 'Kouh-Est', 'Kouh-Ouest', 'Monts de Lam'] as $nom) {
            $departements[] = $d = (new Departement())->setNom($nom);
            $manager->persist($d);
        }

        $regions = [];
        foreach ([['Moundou', 0], ['Béinamar', 0], ['Doba', 1], ['Bébédjia', 1], ['Krim Krim', 2], ['Bodo', 3]] as [$nom, $dep]) {
            $regions[] = $r = (new Region())->setNom($nom)->setDepartement($departements[$dep]);
            $manager->persist($r);
        }

        $clients = [];
        foreach ([
            ['NGARTOLNODJI', 'Marie', 0], ['DJIMADOUM', 'Jean', 0], ['MBAIGOTO', 'Esther', 1], ['KOUMBAIRIA', 'Paul', 2],
            ['NGAKOUTOU', 'Rachel', 3], ['TCHOUA', 'Daniel', 4], ['BEREMADJI', 'Lydie', 5], ['ALLADOUM', 'Samuel', 0],
        ] as [$nom, $prenom, $region]) {
            $clients[] = $c = (new Client())->setNom($nom)->setPrenom($prenom)
                ->setEmail(strtolower($prenom . '.' . $nom) . '@example.com')->setRegion($regions[$region]);
            $manager->persist($c);
        }

        $produits = [];
        foreach ([
            ['Riz local 25 kg', 120, 14500], ['Huile végétale 5 L', 8, 6500], ['Sucre 50 kg', 45, 32000],
            ['Farine de blé 25 kg', 60, 11000], ['Lait en poudre 400 g', 200, 2800], ['Savon de ménage (carton)', 75, 9000],
            ['Pâtes alimentaires (carton)', 5, 7500], ['Eau minérale 1,5 L (pack de 6)', 300, 1800],
            ['Sel fin 1 kg', 150, 450], ['Thé en sachets (boîte)', 90, 1200],
        ] as [$designation, $stock, $prix]) {
            $produits[] = $p = (new Produit())->setDesignation($designation)->setStock($stock)->setPrixUnitaire($prix);
            $manager->persist($p);
        }

        // [date, index du client, [[index du produit, quantité], ...]]
        $commandes = [
            ['2026-01-12', 0, [[0, 10], [4, 24]]], ['2026-01-28', 1, [[2, 2], [8, 40], [3, 5]]],
            ['2026-02-09', 2, [[7, 50], [1, 6], [4, 12]]], ['2026-02-21', 0, [[0, 8], [5, 10]]],
            ['2026-03-05', 3, [[3, 10], [2, 1]]], ['2026-03-18', 4, [[1, 12], [6, 4], [9, 20]]],
            ['2026-04-02', 1, [[7, 80], [4, 30]]], ['2026-04-23', 5, [[0, 6], [8, 25]]],
            ['2026-05-14', 6, [[5, 8], [6, 3], [2, 2]]], ['2026-05-30', 2, [[4, 40], [9, 15]]],
            ['2026-06-11', 7, [[0, 15], [3, 6]]], ['2026-07-07', 0, [[1, 10], [7, 60]]],
            ['2026-08-19', 3, [[2, 3], [6, 6], [8, 30]]], ['2026-09-15', 4, [[0, 12], [4, 18], [5, 5]]],
        ];
        foreach ($commandes as [$date, $client, $lignes]) {
            $cmd = (new Commande())->setDateCommande(new \DateTimeImmutable($date))->setClient($clients[$client]);
            $manager->persist($cmd);
            foreach ($lignes as [$produit, $quantite]) {
                $manager->persist((new LigneCommande())->setCommande($cmd)->setProduit($produits[$produit])->setQuantite($quantite));
            }
        }

        $manager->flush();
    }
}
