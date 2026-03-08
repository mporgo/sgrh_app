<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ElementVariable extends Model
{
    use HasFactory;

    protected $fillable = [
        'paie_id', 'libelle', 'type', 'montant', 'commentaire',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
    ];

    public function paie()
    {
        return $this->belongsTo(Paie::class);
    }
}
