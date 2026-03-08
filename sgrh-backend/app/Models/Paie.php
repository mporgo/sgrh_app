<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Paie extends Model
{
    use HasFactory;

    protected $fillable = [
        'employe_id', 'mois', 'annee',
        'salaire_base', 'total_primes', 'total_deductions',
        'total_avantages', 'net_a_payer',
        'cotisation_cnss', 'impot_iuts',
        'statut', 'date_paiement', 'reference', 'notes', 'genere_par',
    ];

    protected $casts = [
        'date_paiement'   => 'date',
        'salaire_base'    => 'decimal:2',
        'total_primes'    => 'decimal:2',
        'total_deductions'=> 'decimal:2',
        'total_avantages' => 'decimal:2',
        'net_a_payer'     => 'decimal:2',
        'cotisation_cnss' => 'decimal:2',
        'impot_iuts'      => 'decimal:2',
    ];

    public static array $moisLabels = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function elements()
    {
        return $this->hasMany(ElementVariable::class);
    }

    public function generepar()
    {
        return $this->belongsTo(User::class, 'genere_par');
    }

    public function getMoisLabelAttribute(): string
    {
        return self::$moisLabels[$this->mois] ?? $this->mois;
    }
}
