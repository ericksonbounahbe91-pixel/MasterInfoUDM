<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Region;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::with('region.departement')
            ->withCount('commandes')
            ->orderBy('nomCli')
            ->paginate(10);

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        $regions = Region::with('departement')->orderBy('nomRegion')->get();

        return view('clients.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomCli'    => 'required|string|max:100',
            'prenomCli' => 'required|string|max:100',
            'email'     => 'required|email|max:150|unique:Client,email',
            'idRegion'  => 'required|exists:Region,idRegion',
            'photo'     => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('clients', 'public');
        }

        Client::create($data);

        return redirect()->route('clients.index')->with('success', 'Client ajouté avec succès.');
    }
}
