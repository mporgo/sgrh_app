<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Avantage extends Model
{
    use HasFactory;

    protected $fillable = ['libelle', 'description', 'valeur', 'is_active'];

    protected $casts = [
        'valeur'    => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function attributions()
    {
        return $this->hasMany(AttributionAvantage::class);
    }
}
