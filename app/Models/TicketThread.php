<?php

namespace App\Models;

use App\Notifications\AdminNewTicket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class TicketThread extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'user_id',
        'department_id',
        'service_id',
        'subject',
        'status',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'priority' => 'string',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TicketThread $ticket) {
            if (empty($ticket->number)) {
                $ticket->number = 'TKT-'.strtoupper(Str::random(8));
            }
        });

        static::created(function (TicketThread $ticket) {
            $admins = User::where('is_admin', true)->get();
            Notification::send($admins, new AdminNewTicket($ticket));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(TicketDepartment::class, 'department_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(TicketMessage::class)->latestOfMany();
    }
}
