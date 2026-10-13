<?php

namespace App\Http\Controllers;

use App\Models\Client;

class ClientController extends Controller
{
    // Consigne 2 : liste des clients
    public function index()
    {
        $clients = Client::with('region.departement')->orderBy('nomCli')->paginate(15);
        return view('clients.index', compact('clients'));
    }
}
