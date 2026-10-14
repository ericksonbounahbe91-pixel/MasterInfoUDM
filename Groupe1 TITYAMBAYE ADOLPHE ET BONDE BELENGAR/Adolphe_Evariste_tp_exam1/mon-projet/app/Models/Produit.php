<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    protected $table = 'Produit';
    protected $primaryKey = 'idProd';
    public $timestamps = false;
    protected $guarded = [];

    public function commandes()
    {
        return $this->belongsToMany(Commande::class, 'ligneCommande', 'idProd', 'idCmd')
                    ->withPivot('qteCmd');
    }
}
