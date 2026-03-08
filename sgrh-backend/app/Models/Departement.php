<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departement extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'description', 'responsable_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function postes()
    {
        return $this->hasMany(Poste::class);
    }

    public function employes()
    {
        return $this->hasMany(Employe::class);
    }
}
