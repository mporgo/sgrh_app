<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationSgrh extends Model
{
    use HasFactory;

    protected $table = 'notifications_sgrh';

    protected $fillable = [
        'user_id', 'titre', 'message', 'type', 'lien', 'lu', 'lu_le',
    ];

    protected $casts = [
        'lu'    => 'boolean',
        'lu_le' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper statique pour créer rapidement une notification
    public static function notifier(
        int $userId,
        string $titre,
        string $message,
        string $type = 'systeme',
        ?string $lien = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'titre'   => $titre,
            'message' => $message,
            'type'    => $type,
            'lien'    => $lien,
        ]);
    }
}
