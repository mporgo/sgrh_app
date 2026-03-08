<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employe extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'departement_id', 'poste_id', 'manager_id',
        'matricule', 'date_embauche', 'type_contrat', 'fin_contrat',
        'salaire_base', 'conge_solde', 'statut', 'notes',
    ];

    protected $casts = [
        'date_embauche' => 'date',
        'fin_contrat'   => 'date',
        'salaire_base'  => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function poste()
    {
        return $this->belongsTo(Poste::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // Alerte contrat expirant dans moins de 30 jours
    public function getContratExpirantAttribute(): bool
    {
        if (!$this->fin_contrat) return false;
        return $this->fin_contrat->diffInDays(now()) <= 30 && $this->fin_contrat->isFuture();
    }
}
