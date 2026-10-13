<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;

class ClientApiController extends Controller
{
    public function index()
    {
        return Client::with('region.departement')->orderBy('nomCli')->get()->map(fn ($c) => [
            'idCli'       => (int) $c->idCli,
            'nomCli'      => $c->nomCli,
            'prenomCli'   => $c->prenomCli,
            'email'       => $c->email,
            'region'      => $c->region->nomRegion,
            'departement' => $c->region->departement->nomDep,
        ])->values();
    }
}
