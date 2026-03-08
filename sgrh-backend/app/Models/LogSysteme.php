<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogSysteme extends Model
{
    protected $table = 'logs_systeme';

    protected $fillable = [
        'user_id', 'action', 'module',
        'description', 'ip_address', 'user_agent',
        'donnees_avant', 'donnees_apres',
    ];

    protected $casts = [
        'donnees_avant' => 'array',
        'donnees_apres' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper statique pour logger rapidement
    public static function log(
        string $action,
        string $module,
        string $description = '',
        ?array $avant = null,
        ?array $apres = null
    ): self {
        return self::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'module'       => $module,
            'description'  => $description,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
            'donnees_avant'=> $avant,
            'donnees_apres'=> $apres,
        ]);
    }
}
