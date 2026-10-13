<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Departement;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::with('region.departement')
                         ->orderBy('nomCli')
                         ->paginate(10);

        return view('clients.index', compact('clients'));
    }

    // Formulaire d'ajout : on transmet les noms déjà saisis, pour les proposer en suggestion
    public function create()
    {
        $departements = Departement::orderBy('nomDep')->pluck('nomDep');
        $regions      = Region::orderBy('nomRegion')->pluck('nomRegion')->unique();

        return view('clients.create', compact('departements', 'regions'));
    }
    
    public function store(Request $request)
    {
        $donnees = $request->validate([
            'nomCli'    => ['required', 'string', 'max:100'],
            'prenomCli' => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'max:150', 'unique:client,email'],
            'nomDep'    => ['required', 'string', 'max:100'],
            'nomRegion' => ['required', 'string', 'max:100'],
        ], [
            'nomCli.required'    => 'Le nom est obligatoire.',
            'prenomCli.required' => 'Le prénom est obligatoire.',
            'email.required'     => "L'email est obligatoire.",
            'email.email'        => "L'email n'est pas valide.",
            'email.unique'       => 'Cet email est déjà utilisé par un autre client.',
            'nomDep.required'    => 'Le département est obligatoire.',
            'nomRegion.required' => 'La région est obligatoire.',
        ]);

        // Tout dans une transaction : si une étape échoue, rien n'est enregistré
        DB::transaction(function () use ($donnees) {

            // firstOrCreate : retrouve le département s'il existe déjà, sinon le crée
            $departement = Departement::firstOrCreate(['nomDep' => trim($donnees['nomDep'])]);

            // Même principe pour la région, rattachée à ce département
            $region = Region::firstOrCreate([
                'nomRegion' => trim($donnees['nomRegion']),
                'idDep'     => $departement->idDep,
            ]);

            Client::create([
                'nomCli'    => $donnees['nomCli'],
                'prenomCli' => $donnees['prenomCli'],
                'email'     => $donnees['email'],
                'idRegion'  => $region->idRegion,
            ]);
        });

        return redirect()->route('clients.index')->with('success', 'Client ajouté avec succès.');
    }

    public function edit(Client $client)
    {
        $departements = Departement::orderBy('nomDep')->pluck('nomDep');
        $regions      = Region::orderBy('nomRegion')->pluck('nomRegion')->unique();

        return view('clients.edit', compact('client', 'departements', 'regions'));
    }
    // update : on peut modifier l'email, donc pas de validation unique
    public function update(Request $request, Client $client)
    {
        $donnees = $request->validate([
            'nomCli'    => ['required', 'string', 'max:100'],
            'prenomCli' => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'max:150'],
            'nomDep'    => ['required', 'string', 'max:100'],
            'nomRegion' => ['required', 'string', 'max:100'],
        ], [
            'nomCli.required'    => 'Le nom est obligatoire.',
            'prenomCli.required' => 'Le prénom est obligatoire.',
            'email.required'     => "L'email est obligatoire.",
            'email.email'        => "L'email n'est pas valide.",
            'nomDep.required'    => 'Le département est obligatoire.',
            'nomRegion.required' => 'La région est obligatoire.',
        ]);

        DB::transaction(function () use ($donnees, $client) {

            $departement = Departement::firstOrCreate(['nomDep' => trim($donnees['nomDep'])]);

            $region = Region::firstOrCreate([
                'nomRegion' => trim($donnees['nomRegion']),
                'idDep'     => $departement->idDep,
            ]);

            $client->update([
                'nomCli'    => $donnees['nomCli'],
                'prenomCli' => $donnees['prenomCli'],
                'email'     => $donnees['email'],
                'idRegion'  => $region->idRegion,
            ]);
        });

        return redirect()->route('clients.index')->with('success', 'Client modifié avec succès.');
    }
}