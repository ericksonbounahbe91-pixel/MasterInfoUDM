<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'Client';
    protected $primaryKey = 'idCli';
    public $timestamps = false;
    protected $guarded = [];

    public function region()
    {
        return $this->belongsTo(Region::class, 'idRegion', 'idRegion');
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'idCli', 'idCli');
    }
}
