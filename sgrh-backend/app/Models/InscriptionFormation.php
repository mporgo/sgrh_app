<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InscriptionFormation extends Model
{
    use HasFactory;

    protected $table = 'inscription_formations';

    protected $fillable = [
        'formation_id', 'employe_id', 'statut',
        'note', 'certificat_obtenu', 'commentaire', 'inscrit_le',
    ];

    protected $casts = [
        'certificat_obtenu' => 'boolean',
        'inscrit_le'        => 'datetime',
    ];

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }
}
