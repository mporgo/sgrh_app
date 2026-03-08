<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conge extends Model
{
    use HasFactory;

    protected $fillable = [
        'employe_id', 'type_conge_id', 'valideur_id',
        'date_debut', 'date_fin', 'nombre_jours',
        'commentaire', 'motif_refus', 'statut', 'valide_le',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
        'valide_le'  => 'datetime',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function typeConge()
    {
        return $this->belongsTo(TypeConge::class, 'type_conge_id');
    }

    public function valideur()
    {
        return $this->belongsTo(User::class, 'valideur_id');
    }
}
