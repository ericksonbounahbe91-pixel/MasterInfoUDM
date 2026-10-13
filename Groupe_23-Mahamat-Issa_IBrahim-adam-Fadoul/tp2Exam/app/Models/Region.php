<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'regions';
    protected $primaryKey = 'idRegion';
    protected $fillable = ['nomRegion', 'idDep'];

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'idDep', 'idDep');
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'idRegion', 'idRegion');
    }
}
