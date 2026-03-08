<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Formation extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre', 'description', 'formateur', 'type', 'statut',
        'date_debut', 'date_fin', 'duree_heures', 'places_max',
        'cout', 'lieu', 'lien_elearning', 'responsable_id',
    ];

    protected $casts = [
        'date_debut'     => 'date',
        'date_fin'       => 'date',
        'cout'           => 'decimal:2',
        'certificat_obtenu' => 'boolean',
    ];

    public static array $types = [
        'interne'   => 'Formation interne',
        'externe'   => 'Formation externe',
        'elearning' => 'E-Learning',
    ];

    public function inscriptions()
    {
        return $this->hasMany(InscriptionFormation::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    // Vérifie si des places sont disponibles
    public function getPlacesDisponiblesAttribute(): ?int
    {
        if ($this->places_max === null) return null; // illimité
        return max(0, $this->places_max - $this->inscriptions()
            ->whereNotIn('statut', ['refusee', 'abandonnee'])
            ->count());
    }

    public function getCompletAttribute(): bool
    {
        if ($this->places_max === null) return false;
        return $this->places_disponibles === 0;
    }
}
