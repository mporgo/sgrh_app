<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttributionAvantage extends Model
{
    use HasFactory;

    protected $table = 'attribution_avantages';

    protected $fillable = [
        'employe_id', 'avantage_id', 'valeur_override',
        'date_debut', 'date_fin', 'is_active',
    ];

    protected $casts = [
        'date_debut'     => 'date',
        'date_fin'       => 'date',
        'valeur_override'=> 'decimal:2',
        'is_active'      => 'boolean',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function avantage()
    {
        return $this->belongsTo(Avantage::class);
    }
}
