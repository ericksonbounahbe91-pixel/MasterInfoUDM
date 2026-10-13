<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    protected $table = 'produits';
    protected $primaryKey = 'idProd';
    protected $fillable = ['designProd', 'qteStock', 'prixUProd', 'photoProd'];

    public function commandes()
    {
        return $this->belongsToMany(Commande::class, 'ligne_commandes', 'idProd', 'idCmd')
            ->withPivot('qteCmd');
    }
}
