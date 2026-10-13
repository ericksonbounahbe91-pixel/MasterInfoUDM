<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $table = 'commandes';
    protected $primaryKey = 'idCmd';
    protected $fillable = ['dateCmd', 'idCli'];
    protected $casts = ['dateCmd' => 'date'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'idCli', 'idCli');
    }

    // Les lignes de commande (table ligne_commandes) = table pivot avec qteCmd
    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'ligne_commandes', 'idCmd', 'idProd')
            ->withPivot('qteCmd');
    }

    public function getTotalAttribute(): float
    {
        return $this->produits->sum(fn ($p) => $p->pivot->qteCmd * $p->prixUProd);
    }
}
