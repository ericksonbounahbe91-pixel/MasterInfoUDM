<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';
    protected $primaryKey = 'idCli';
    protected $fillable = ['nomCli', 'prenomCli', 'email', 'photo', 'idRegion'];

    public function region()
    {
        return $this->belongsTo(Region::class, 'idRegion', 'idRegion');
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'idCli', 'idCli');
    }
}
