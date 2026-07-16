<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'email_enabled',
        'dashboard_enabled',
    ];

    protected function casts(): array
    {
        return [
            'email_enabled' => 'boolean',
            'dashboard_enabled' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getForUser(int $userId, string $type): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId, 'type' => $type],
            ['email_enabled' => true, 'dashboard_enabled' => true]
        );
    }
}
