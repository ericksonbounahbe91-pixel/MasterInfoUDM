<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $table = 'Commande';
    protected $primaryKey = 'idCmd';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['dateCmd' => 'date'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'idCli', 'idCli');
    }

    // ligneCommande = table pivot (clé primaire composée idCmd + idProd)
    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'ligneCommande', 'idCmd', 'idProd')
                    ->withPivot('qteCmd');
    }

    public function getTotalAttribute()
    {
        return $this->produits->sum(fn ($p) => $p->pivot->qteCmd * $p->prixUProd);
    }
}
