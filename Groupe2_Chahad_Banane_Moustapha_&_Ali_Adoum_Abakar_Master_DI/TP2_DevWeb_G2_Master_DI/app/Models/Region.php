<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $table = 'region';
    protected $primaryKey = 'idRegion';
    public $timestamps = false;

    protected $fillable = ['nomRegion', 'idDep'];

    // Une région appartient à un département
    // belongsTo(modèle, colonne étrangère ici, colonne clé là-bas)
    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'idDep', 'idDep');
    }

    // Une région a plusieurs clients
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'idRegion', 'idRegion');
    }
}