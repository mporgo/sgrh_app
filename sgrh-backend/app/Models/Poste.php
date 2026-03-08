<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Poste extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre', 'description', 'departement_id',
        'salaire_min', 'salaire_max', 'is_active'
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'salaire_min' => 'decimal:2',
        'salaire_max' => 'decimal:2',
    ];

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function employes()
    {
        return $this->hasMany(Employe::class);
    }
}
