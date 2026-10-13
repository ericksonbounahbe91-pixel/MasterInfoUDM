<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departement extends Model
{
    protected $table = 'departement';
    protected $primaryKey = 'idDep';
    public $timestamps = false;

    // Colonnes autorisées pour create() et update()
    protected $fillable = ['nomDep'];

    // Un département a plusieurs régions
    public function regions(): HasMany
    {
        return $this->hasMany(Region::class, 'idDep', 'idDep');
    }
}