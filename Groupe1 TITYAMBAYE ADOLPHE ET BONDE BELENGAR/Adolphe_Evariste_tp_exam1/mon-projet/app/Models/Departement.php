<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    protected $table = 'Departement';
    protected $primaryKey = 'idDep';
    public $timestamps = false;
    protected $guarded = [];

    public function regions()
    {
        return $this->hasMany(Region::class, 'idDep', 'idDep');
    }
}
