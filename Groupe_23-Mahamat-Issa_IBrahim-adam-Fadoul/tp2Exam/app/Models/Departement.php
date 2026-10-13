<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    protected $table = 'departements';
    protected $primaryKey = 'idDep';
    protected $fillable = ['nomDep'];

    public function regions()
    {
        return $this->hasMany(Region::class, 'idDep', 'idDep');
    }
}
