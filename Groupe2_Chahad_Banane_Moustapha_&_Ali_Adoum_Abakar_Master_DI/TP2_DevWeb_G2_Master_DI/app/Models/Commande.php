<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Commande extends Model
{
    protected $table = 'commande';
    protected $primaryKey = 'idCmd';
    public $timestamps = false;

    protected $fillable = ['dateCmd', 'idCli'];

    // dateCmd devient un objet date, on peut faire $commande->dateCmd->format('d/m/Y')
    protected $casts = ['dateCmd' => 'date'];

    // Une commande appartient à un client
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'idCli', 'idCli');
    }

    // Une commande contient plusieurs produits, via ligneCommande
    // withPivot('qteCmd') permet de lire $produit->pivot->qteCmd
    public function produits(): BelongsToMany
    {
        return $this->belongsToMany(Produit::class, 'ligneCommande', 'idCmd', 'idProd')
                    ->withPivot('qteCmd');
    }
}