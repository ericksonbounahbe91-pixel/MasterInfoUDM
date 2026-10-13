<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Commande;
use App\Models\Departement;
use App\Models\Produit;
use App\Models\Region;
use Illuminate\Database\Seeder;

class TpSeeder extends Seeder
{
    public function run(): void
    {
        $deps = [];
        foreach (['Mandoul', 'Moyen-Chari', 'Logone Occidental', 'Hadjer-Lamis', 'Chari-Baguirmi'] as $nom) {
            $deps[] = Departement::create(['nomDep' => $nom]);
        }

        $regions = [];
        foreach ([
            ["N'Djamena", 4], ['Moundou', 2], ['Sarh', 1], ['Doba', 2], ['Mongo', 3], ['Bongor', 0],
        ] as [$nom, $i]) {
            $regions[] = Region::create(['nomRegion' => $nom, 'idDep' => $deps[$i]->idDep]);
        }

        $noms = ['Mahamat', 'Abakar', 'Ngarta', 'Djimadoum', 'Hassan', 'Idriss', 'Tahir', 'Brahim', 'Allamine', 'Moussa'];
        $prenoms = ['Fatimé', 'Achta', 'Kadidja', 'Ali', 'Youssouf', 'Zara', 'Ousmane', 'Halimé', 'Daoud', 'Amina'];
        foreach (range(1, 30) as $i) {
            Client::create([
                'nomCli'    => $noms[array_rand($noms)],
                'prenomCli' => $prenoms[array_rand($prenoms)],
                'email'     => "client{$i}@exemple.td",
                'idRegion'  => $regions[array_rand($regions)]->idRegion,
            ]);
        }

        foreach ([
            ['Riz 25 kg', 18000], ['Huile 5 L', 7500], ['Sucre 50 kg', 32000], ['Farine 25 kg', 15000],
            ['Lait en poudre', 3500], ['Savon (carton)', 12000], ['Eau minérale (pack)', 2500],
            ['Pâtes (carton)', 9000], ['Tomate concentrée', 6000], ['Thé (carton)', 11000],
            ['Café 1 kg', 8000], ['Biscuits (carton)', 5500],
        ] as [$nom, $prix]) {
            Produit::create(['designProd' => $nom, 'qteStock' => rand(8, 200), 'prixUProd' => $prix]);
        }

        $clientIds = Client::pluck('idCli');
        $produitIds = Produit::pluck('idProd');

        foreach (range(1, 80) as $i) {
            $commande = Commande::create([
                'idCli'   => $clientIds->random(),
                'dateCmd' => now()->subDays(rand(0, 300))->toDateString(),
            ]);

            $lignes = [];
            foreach ($produitIds->random(rand(1, 4)) as $idProd) {
                $lignes[$idProd] = ['qteCmd' => rand(1, 5)];
            }
            $commande->produits()->attach($lignes);
        }
    }
}
