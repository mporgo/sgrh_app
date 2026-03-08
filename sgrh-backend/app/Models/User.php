<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'date_naissance',
        'genre',
        'adresse',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_naissance'    => 'date',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Relations (on les complétera au fur et à mesure) ──

    public function employe()
    {
        return $this->hasOne(Employe::class);
    }

    public function departementsGeres()
    {
        return $this->hasMany(Departement::class, 'responsable_id');
    }

    public function collaborateurs()
    {
        return $this->hasMany(Employe::class, 'manager_id');
    }

    public function notifications()
    {
        return $this->hasMany(NotificationSgrh::class);
    }
}
