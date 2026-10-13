<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Produit extends Model
{
    protected $table = 'produit';
    protected $primaryKey = 'idProd';
    public $timestamps = false;

    protected $fillable = ['designProd', 'qteStock', 'prixUProd', 'photoProd'];

    // Le prix est toujours lu avec 2 décimales
    protected $casts = ['prixUProd' => 'decimal:2'];

    // Un produit apparaît dans plusieurs commandes, via la table ligneCommande
    // belongsToMany(modèle, table de liaison, clé de CE modèle, clé de l'AUTRE modèle)
    public function commandes(): BelongsToMany
    {
        return $this->belongsToMany(Commande::class, 'ligneCommande', 'idProd', 'idCmd')
                    ->withPivot('qteCmd');
    }
}