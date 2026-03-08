<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employe_id', 'evaluateur_id',
        'date_evaluation', 'date_prochaine', 'type', 'statut',
        'note_globale', 'score',
        'objectifs_fixes', 'objectifs_atteints',
        'points_forts', 'axes_amelioration',
        'commentaire_evaluateur', 'commentaire_employe',
        'signe_employe', 'signe_evaluateur',
    ];

    protected $casts = [
        'date_evaluation'  => 'date',
        'date_prochaine'   => 'date',
        'signe_employe'    => 'boolean',
        'signe_evaluateur' => 'boolean',
    ];

    // Labels lisibles
    public static array $notes = [
        'insuffisant' => 'Insuffisant',
        'passable'    => 'Passable',
        'bien'        => 'Bien',
        'tres_bien'   => 'Très bien',
        'excellent'   => 'Excellent',
    ];

    public static array $types = [
        'annuelle'       => 'Évaluation annuelle',
        'semestrielle'   => 'Évaluation semestrielle',
        'periode_essai'  => 'Fin de période d\'essai',
        'autre'          => 'Autre',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function evaluateur()
    {
        return $this->belongsTo(User::class, 'evaluateur_id');
    }
}
