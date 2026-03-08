<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TypeConge extends Model
{
    use HasFactory;

    protected $table = 'type_conges';

    protected $fillable = [
        'libelle', 'jours_annuels', 'reportable',
        'justificatif_requis', 'couleur', 'is_active',
    ];

    protected $casts = [
        'reportable'           => 'boolean',
        'justificatif_requis'  => 'boolean',
        'is_active'            => 'boolean',
    ];

    public function conges()
    {
        return $this->hasMany(Conge::class);
    }
}
