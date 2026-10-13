<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $table = 'client';
    protected $primaryKey = 'idCli';
    public $timestamps = false;

    protected $fillable = ['nomCli', 'prenomCli', 'email', 'photo', 'idRegion'];

    // Un client habite dans une région
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'idRegion', 'idRegion');
    }

    // Un client passe plusieurs commandes
    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class, 'idCli', 'idCli');
    }
}